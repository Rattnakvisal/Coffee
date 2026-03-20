<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\InventoryTransaction;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CashierController extends Controller
{
    private const CART_SESSION_KEY = 'cashier_cart';
    private const PAYMENT_METHODS = ['cash', 'card', 'qr'];

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $category = trim((string) $request->query('category', ''));

        $categories = Category::query()->active()->orderBy('name')->get();
        $searchSuggestions = Product::query()
            ->active()
            ->orderBy('name')
            ->limit(250)
            ->pluck('name')
            ->map(fn(mixed $name): string => trim((string) $name))
            ->merge(
                $categories
                    ->pluck('name')
                    ->map(fn(mixed $name): string => trim((string) $name)),
            )
            ->filter(fn(string $value): bool => $value !== '')
            ->unique(fn(string $value): string => mb_strtolower($value))
            ->sort(SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        $products = Product::query()
            ->active()
            ->with('category')
            ->when(
                $category !== '',
                function ($query) use ($category): void {
                    $query->whereHas('category', function ($categoryQuery) use ($category): void {
                        $categoryQuery->where('slug', $category);
                    });
                },
            )
            ->when(
                $search !== '',
                function ($query) use ($search): void {
                    $query->where(function ($productQuery) use ($search): void {
                        $productQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('description', 'like', "%{$search}%")
                            ->orWhereHas('category', function ($categoryQuery) use ($search): void {
                                $categoryQuery->where('name', 'like', "%{$search}%");
                            });
                    });
                },
            )
            ->orderBy('name')
            ->get();

        $cartState = $this->buildCartState($request);

        return view('cashier.index', [
            'products' => $products,
            'categories' => $categories,
            'category' => $category,
            'search' => $search,
            'searchSuggestions' => $searchSuggestions,
            'cartItems' => $cartState['items'],
            'cartSubtotal' => $cartState['subtotal'],
            'cartDiscount' => $cartState['discount'],
            'cartTotal' => $cartState['total'],
        ]);
    }

    public function history(Request $request): View
    {
        $period = $this->normalizePeriod((string) $request->query('period', 'day'));
        $search = trim((string) $request->query('search', ''));
        $selectedPayment = $this->normalizeHistoryFilter((string) $request->query('payment', 'all'));
        $selectedStatus = $this->normalizeHistoryFilter((string) $request->query('status', 'all'));
        $dateColumn = $this->orderDateColumn();
        [$start, $end, $periodLabel] = $this->periodRange($period);

        $baseHistoryQuery = $this->cashierOrdersQuery($request)
            ->whereBetween($dateColumn, [$start, $end]);
        $this->applyHistoryOrderFilters($baseHistoryQuery, $selectedPayment, $selectedStatus);

        $paymentOptions = Schema::hasColumn('orders', 'payment_method')
            ? (clone $baseHistoryQuery)
                ->selectRaw("LOWER(COALESCE(payment_method, 'unknown')) as value")
                ->pluck('value')
                ->filter(fn ($value): bool => trim((string) $value) !== '')
                ->map(fn ($value): string => (string) $value)
                ->unique()
                ->sort()
                ->values()
                ->all()
            : [];

        $statusOptions = Schema::hasColumn('orders', 'status')
            ? (clone $baseHistoryQuery)
                ->selectRaw("LOWER(COALESCE(status, 'completed')) as value")
                ->pluck('value')
                ->filter(fn ($value): bool => trim((string) $value) !== '')
                ->map(fn ($value): string => (string) $value)
                ->unique()
                ->sort()
                ->values()
                ->all()
            : [];

        $ordersQuery = (clone $baseHistoryQuery)
            ->with('items:id,order_id,product_name,qty')
            ->withCount('items')
            ->when(
                $search !== '',
                function (Builder $query) use ($search): void {
                    $query->where(function (Builder $orderQuery) use ($search): void {
                        $orderQuery
                            ->where('order_number', 'like', "%{$search}%")
                            ->orWhere('payment_method', 'like', "%{$search}%")
                            ->orWhere('status', 'like', "%{$search}%");
                    });
                },
            );

        $ordersCount = (int) (clone $ordersQuery)->count();
        $revenue = (float) (clone $ordersQuery)->sum('total');
        $averageOrder = $ordersCount > 0 ? $revenue / $ordersCount : 0.0;
        $itemsSold = (int) OrderItem::query()
            ->whereHas('order', function (Builder $query) use ($request, $dateColumn, $start, $end, $search, $selectedPayment, $selectedStatus): void {
                $this->applyCashierScope($query, $request);
                $query->whereBetween($dateColumn, [$start, $end]);
                $this->applyHistoryOrderFilters($query, $selectedPayment, $selectedStatus);

                if ($search !== '') {
                    $query->where(function (Builder $orderQuery) use ($search): void {
                        $orderQuery
                            ->where('order_number', 'like', "%{$search}%")
                            ->orWhere('payment_method', 'like', "%{$search}%")
                            ->orWhere('status', 'like', "%{$search}%");
                    });
                }
            })
            ->sum('qty');
        $latestOrderAt = (clone $ordersQuery)->max($dateColumn);

        $orders = (clone $ordersQuery)
            ->orderByDesc($dateColumn)
            ->paginate(12)
            ->withQueryString();

        $cartState = $this->buildCartState($request);

        return view('cashier.history', [
            'period' => $period,
            'periodLabel' => $periodLabel,
            'search' => $search,
            'orders' => $orders,
            'ordersCount' => $ordersCount,
            'revenue' => $revenue,
            'averageOrder' => $averageOrder,
            'itemsSold' => $itemsSold,
            'selectedPayment' => $selectedPayment,
            'selectedStatus' => $selectedStatus,
            'paymentOptions' => $paymentOptions,
            'statusOptions' => $statusOptions,
            'latestOrderAt' => $latestOrderAt,
            'cartItems' => $cartState['items'],
            'cartSubtotal' => $cartState['subtotal'],
            'cartDiscount' => $cartState['discount'],
            'cartTotal' => $cartState['total'],
        ]);
    }

    public function addToCart(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'qty' => ['nullable', 'integer', 'min:1', 'max:99'],
            'size' => ['required', 'in:small,medium,large'],
            'sugar' => ['nullable', 'integer', 'min:0', 'max:100'],
        ]);

        $product = Product::query()->active()->findOrFail((int) $validated['product_id']);
        $qty = (int) ($validated['qty'] ?? 1);
        $size = strtolower((string) $validated['size']);
        $sugar = (int) ($validated['sugar'] ?? 50);

        if (! $product->isSizeActive($size)) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Selected size is currently inactive for this product.',
                ], 422);
            }

            return back()->withErrors([
                'size' => 'Selected size is currently inactive for this product.',
            ]);
        }

        $itemKey = $this->makeCartItemKey($product->id, $size, $sugar);

        $cart = $this->getCart($request);
        $currentQty = (int) ($cart[$itemKey]['qty'] ?? 0);

        $cart[$itemKey] = [
            'product_id' => $product->id,
            'size' => $size,
            'sugar' => $sugar,
            'qty' => min($currentQty + $qty, 99),
        ];

        $request->session()->put(self::CART_SESSION_KEY, $cart);

        return $this->respondCartMutation($request);
    }

    public function incrementCartItem(Request $request, string $itemKey): RedirectResponse|JsonResponse
    {
        $cart = $this->getCart($request);

        if (! isset($cart[$itemKey])) {
            return $this->respondCartMutation($request);
        }

        $currentQty = (int) ($cart[$itemKey]['qty'] ?? 0);
        $cart[$itemKey]['qty'] = min($currentQty + 1, 99);

        $request->session()->put(self::CART_SESSION_KEY, $cart);

        return $this->respondCartMutation($request);
    }

    public function decrementCartItem(Request $request, string $itemKey): RedirectResponse|JsonResponse
    {
        $cart = $this->getCart($request);

        if (! isset($cart[$itemKey])) {
            return $this->respondCartMutation($request);
        }

        $currentQty = (int) ($cart[$itemKey]['qty'] ?? 0);

        if ($currentQty <= 1) {
            unset($cart[$itemKey]);
        } else {
            $cart[$itemKey]['qty'] = $currentQty - 1;
        }

        $request->session()->put(self::CART_SESSION_KEY, $cart);

        return $this->respondCartMutation($request);
    }

    public function placeOrder(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'payment_method' => ['required', Rule::in(self::PAYMENT_METHODS)],
            'amount_received' => ['nullable', 'numeric', 'min:0'],
        ]);

        $cartState = $this->buildCartState($request);

        if ($cartState['items']->isEmpty()) {
            return $this->respondOrderFailure($request, 'Cannot place order because cart is empty.');
        }

        $paymentMethod = (string) ($validated['payment_method'] ?? 'cash');
        $total = (float) $cartState['total'];
        $amountReceived = (float) ($validated['amount_received'] ?? 0);

        if ($paymentMethod === 'cash' && $amountReceived < $total) {
            return $this->respondOrderFailure(
                $request,
                'Amount received must be greater than or equal to total.',
            );
        }

        if ($paymentMethod !== 'cash') {
            $amountReceived = $total;
        }

        $changeAmount = max($amountReceived - $total, 0.0);
        $orderNumber = $this->generateOrderNumber();
        $orderMeta = [
            'payment_method' => $paymentMethod,
            'amount_received' => $amountReceived,
            'change_amount' => $changeAmount,
        ];

        DB::transaction(function () use (
            $request,
            $cartState,
            $orderNumber,
            $paymentMethod,
            $amountReceived,
            $changeAmount,
            $orderMeta
        ): void {
            $orderPayload = [
                'order_number' => $orderNumber,
                'subtotal' => $cartState['subtotal'],
                'discount' => $cartState['discount'],
                'total' => $cartState['total'],
            ];

            if (Schema::hasColumn('orders', 'cashier_id')) {
                $orderPayload['cashier_id'] = $request->user()?->id;
            } elseif (Schema::hasColumn('orders', 'cashier_user_id')) {
                $orderPayload['cashier_user_id'] = $request->user()?->id;
            }

            if (Schema::hasColumn('orders', 'payment_method')) {
                $orderPayload['payment_method'] = $paymentMethod;
            }

            if (Schema::hasColumn('orders', 'payment_status')) {
                $orderPayload['payment_status'] = 'paid';
            }

            if (Schema::hasColumn('orders', 'amount_received')) {
                $orderPayload['amount_received'] = $amountReceived;
            }

            if (Schema::hasColumn('orders', 'change_amount')) {
                $orderPayload['change_amount'] = $changeAmount;
            }

            if (Schema::hasColumn('orders', 'status')) {
                $orderPayload['status'] = 'completed';
            }

            if (Schema::hasColumn('orders', 'placed_at')) {
                $orderPayload['placed_at'] = now();
            }

            if (Schema::hasColumn('orders', 'paid_at')) {
                $orderPayload['paid_at'] = now();
            }

            if (Schema::hasColumn('orders', 'currency')) {
                $orderPayload['currency'] = 'USD';
            }

            if (Schema::hasColumn('orders', 'meta')) {
                $orderPayload['meta'] = json_encode($orderMeta, JSON_THROW_ON_ERROR);
            }

            $orderId = (int) DB::table('orders')->insertGetId($orderPayload);

            $hasItemCreatedAt = Schema::hasColumn('order_items', 'created_at');
            $hasItemUpdatedAt = Schema::hasColumn('order_items', 'updated_at');
            $now = now();

            $orderItemRows = $cartState['items']
                ->map(function (array $item) use ($orderId, $hasItemCreatedAt, $hasItemUpdatedAt, $now): array {
                    $row = [
                        'order_id' => $orderId,
                        'product_id' => $item['product_id'],
                        'product_name' => $item['name'],
                        'size' => $item['size'],
                        'sugar' => $item['sugar'],
                        'qty' => $item['qty'],
                        'unit_price' => $item['unit_price'],
                        'line_total' => $item['line_total'],
                    ];

                    if ($hasItemCreatedAt) {
                        $row['created_at'] = $now;
                    }

                    if ($hasItemUpdatedAt) {
                        $row['updated_at'] = $now;
                    }

                    return $row;
                })
                ->all();

            DB::table('order_items')->insert(
                $orderItemRows,
            );

            if (Schema::hasTable('inventory_transactions')) {
                InventoryTransaction::query()->create([
                    'type' => InventoryTransaction::TYPE_MONEY_IN,
                    'amount' => $cartState['total'],
                    'note' => 'Order ' . $orderNumber . ' paid via ' . strtoupper($paymentMethod),
                    'happened_at' => now(),
                    'created_by' => $request->user()?->id,
                ]);
            }
        });

        $request->session()->forget(self::CART_SESSION_KEY);
        $refreshedCartState = $this->buildCartState($request);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'ok' => true,
                'message' => 'Order placed successfully.',
                'order_number' => $orderNumber,
                'order_total' => $total,
                'amount_received' => $amountReceived,
                'change_amount' => $changeAmount,
                'cart_html' => $this->renderCartHtml($refreshedCartState),
            ]);
        }

        return redirect()
            ->route('cashier.index')
            ->with('status', 'Order ' . $orderNumber . ' placed successfully.');
    }

    private function respondCartMutation(Request $request): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson() || $request->ajax()) {
            $cartState = $this->buildCartState($request);

            return response()->json([
                'ok' => true,
                'cart_html' => $this->renderCartHtml($cartState),
            ]);
        }

        return back();
    }

    /**
     * @param array{
     *   items: Collection<int, array{
     *     item_key: string,
     *     product_id: int,
     *     name: string,
     *     image_path: string|null,
     *     size: string,
     *     sugar: int,
     *     qty: int,
     *     base_unit_price: float,
     *     unit_price: float,
     *     line_base_total: float,
     *     line_discount: float,
     *     line_total: float
     *   }>,
     *   subtotal: float,
     *   discount: float,
     *   total: float
     * } $cartState
     */
    private function renderCartHtml(array $cartState): string
    {
        return view('cashier.sidebar.cart', [
            'cartItems' => $cartState['items'],
            'cartSubtotal' => $cartState['subtotal'],
            'cartDiscount' => $cartState['discount'],
            'cartTotal' => $cartState['total'],
        ])->render();
    }

    private function respondOrderFailure(Request $request, string $message): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'ok' => false,
                'message' => $message,
            ], 422);
        }

        return back()->withErrors([
            'payment' => $message,
        ]);
    }

    private function generateOrderNumber(): string
    {
        return 'ORD-' . now()->format('Ymd-His') . '-' . Str::upper(Str::random(4));
    }

    private function normalizePeriod(string $period): string
    {
        return in_array($period, ['day', 'week', 'month'], true) ? $period : 'day';
    }

    private function normalizeHistoryFilter(string $value): string
    {
        $normalized = Str::of($value)
            ->lower()
            ->squish()
            ->value();

        return $normalized === '' ? 'all' : $normalized;
    }

    private function applyHistoryOrderFilters(Builder $query, string $payment, string $status): void
    {
        if ($payment !== 'all' && Schema::hasColumn('orders', 'payment_method')) {
            $query->whereRaw("LOWER(COALESCE(payment_method, 'unknown')) = ?", [$payment]);
        }

        if ($status !== 'all' && Schema::hasColumn('orders', 'status')) {
            $query->whereRaw("LOWER(COALESCE(status, 'completed')) = ?", [$status]);
        }
    }

    /**
     * @return array{0: Carbon, 1: Carbon, 2: string}
     */
    private function periodRange(string $period): array
    {
        $now = now();

        return match ($period) {
            'week' => [$now->copy()->startOfWeek(), $now->copy()->endOfWeek(), 'This Week'],
            'month' => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth(), 'This Month'],
            default => [$now->copy()->startOfDay(), $now->copy()->endOfDay(), 'Today'],
        };
    }

    private function orderDateColumn(): string
    {
        return Schema::hasColumn('orders', 'placed_at') ? 'placed_at' : 'created_at';
    }

    private function cashierOrdersQuery(Request $request): Builder
    {
        $query = Order::query();
        $this->applyCashierScope($query, $request);

        return $query;
    }

    private function applyCashierScope(Builder $query, Request $request): void
    {
        $userId = (int) ($request->user()?->id ?? 0);

        if ($userId <= 0) {
            return;
        }

        if (Schema::hasColumn('orders', 'cashier_id')) {
            $query->where('cashier_id', $userId);
            return;
        }

        if (Schema::hasColumn('orders', 'cashier_user_id')) {
            $query->where('cashier_user_id', $userId);
        }
    }

    /**
     * @return array<string, array{
     *   product_id: int,
     *   size: string,
     *   sugar: int,
     *   qty: int
     * }>
     */
    private function getCart(Request $request): array
    {
        $rawCart = $request->session()->get(self::CART_SESSION_KEY, []);
        $cart = [];

        foreach ((array) $rawCart as $key => $value) {
            if (is_numeric($key) && is_numeric($value)) {
                $legacyProductId = (int) $key;
                $legacyQty = (int) $value;

                if ($legacyProductId <= 0 || $legacyQty <= 0) {
                    continue;
                }

                $legacySize = 'small';
                $legacySugar = 50;
                $legacyKey = $this->makeCartItemKey($legacyProductId, $legacySize, $legacySugar);

                $cart[$legacyKey] = [
                    'product_id' => $legacyProductId,
                    'size' => $legacySize,
                    'sugar' => $legacySugar,
                    'qty' => min($legacyQty, 99),
                ];

                continue;
            }

            if (! is_array($value)) {
                continue;
            }

            $productId = (int) ($value['product_id'] ?? 0);
            $size = strtolower((string) ($value['size'] ?? 'small'));
            $sugar = (int) ($value['sugar'] ?? 50);
            $qty = (int) ($value['qty'] ?? 0);

            if ($productId <= 0 || ! in_array($size, ['small', 'medium', 'large'], true) || $qty <= 0) {
                continue;
            }

            $normalizedSugar = max(0, min(100, $sugar));
            $itemKey = $this->makeCartItemKey($productId, $size, $normalizedSugar);

            $cart[$itemKey] = [
                'product_id' => $productId,
                'size' => $size,
                'sugar' => $normalizedSugar,
                'qty' => min($qty, 99),
            ];
        }

        return $cart;
    }

    private function makeCartItemKey(int $productId, string $size, int $sugar): string
    {
        return $productId . '-' . $size . '-' . $sugar;
    }

    /**
     * @return array{
     *   items: Collection<int, array{
     *     item_key: string,
     *     product_id: int,
     *     name: string,
     *     image_path: string|null,
     *     size: string,
     *     sugar: int,
     *     qty: int,
     *     unit_price: float,
     *     line_total: float
     *   }>,
     *   subtotal: float,
     *   discount: float,
     *   total: float
     * }
     */
    private function buildCartState(Request $request): array
    {
        $cart = $this->getCart($request);
        $productIds = collect($cart)
            ->pluck('product_id')
            ->unique()
            ->values()
            ->all();

        if ($productIds === []) {
            return [
                'items' => collect(),
                'subtotal' => 0.0,
                'discount' => 0.0,
                'total' => 0.0,
            ];
        }

        $products = Product::query()
            ->active()
            ->whereIn('id', $productIds)
            ->get()
            ->keyBy('id');

        $items = collect($cart)
            ->map(function (array $entry, string $itemKey) use ($products): ?array {
                $productId = (int) ($entry['product_id'] ?? 0);
                /** @var Product|null $product */
                $product = $products->get($productId);

                if (! $product) {
                    return null;
                }

                $qty = (int) ($entry['qty'] ?? 1);
                $size = (string) ($entry['size'] ?? 'small');
                $sugar = (int) ($entry['sugar'] ?? 50);

                if (! $product->isSizeActive($size)) {
                    return null;
                }

                $baseUnitPrice = $product->sizeBasePrice($size);
                $unitPrice = $product->sizePrice($size);
                $lineBaseTotal = $baseUnitPrice * $qty;
                $lineTotal = $unitPrice * $qty;
                $lineDiscount = max($lineBaseTotal - $lineTotal, 0.0);

                return [
                    'item_key' => $itemKey,
                    'product_id' => $product->id,
                    'name' => (string) $product->name,
                    'image_path' => $product->image_path,
                    'size' => $size,
                    'sugar' => $sugar,
                    'qty' => $qty,
                    'base_unit_price' => $baseUnitPrice,
                    'unit_price' => $unitPrice,
                    'line_base_total' => $lineBaseTotal,
                    'line_discount' => $lineDiscount,
                    'line_total' => $lineTotal,
                ];
            })
            ->filter()
            ->values();

        $validCart = $items
            ->mapWithKeys(fn(array $item): array => [
                $item['item_key'] => [
                    'product_id' => $item['product_id'],
                    'size' => $item['size'],
                    'sugar' => $item['sugar'],
                    'qty' => $item['qty'],
                ],
            ])
            ->all();

        if ($validCart !== $cart) {
            $request->session()->put(self::CART_SESSION_KEY, $validCart);
        }

        $subtotal = (float) $items->sum('line_base_total');
        $discount = (float) $items->sum('line_discount');
        $total = max($subtotal - $discount, 0.0);

        return [
            'items' => $items,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'total' => $total,
        ];
    }
}
