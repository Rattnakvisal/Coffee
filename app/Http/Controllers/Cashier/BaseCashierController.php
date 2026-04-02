<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\CashierAttendance;
use App\Models\InventoryTransaction;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class BaseCashierController extends Controller
{
    protected const CART_SESSION_KEY = 'cashier_cart';

    protected const PAYMENT_METHODS = ['cash', 'card', 'qr'];

    protected const PRODUCT_SIZES = ['small', 'medium', 'large'];

    protected function cashierUsersQuery(): Builder
    {
        return User::query()->whereHas('role', function (Builder $query): void {
            $query->where('slug', 'cashier');
        });
    }

    protected function buildSearchSuggestions(Collection $categories): Collection
    {
        return Product::query()
            ->active()
            ->orderBy('name')
            ->limit(250)
            ->pluck('name')
            ->map(fn (mixed $name): string => trim((string) $name))
            ->merge(
                $categories->pluck('name')->map(fn (mixed $name): string => trim((string) $name))
            )
            ->filter(fn (string $value): bool => $value !== '')
            ->unique(fn (string $value): string => mb_strtolower($value))
            ->sort(SORT_NATURAL | SORT_FLAG_CASE)
            ->values();
    }

    protected function wantsJson(Request $request): bool
    {
        return $request->expectsJson() || $request->ajax();
    }

    protected function resolveUserDisplayName(User $user): string
    {
        $name = trim((string) ($user->name ?? ''));
        if ($name !== '') {
            return $name;
        }

        $fullName = trim((string) (($user->first_name ?? '').' '.($user->last_name ?? '')));
        if ($fullName !== '') {
            return $fullName;
        }

        return (string) ($user->email ?? ('User #'.$user->id));
    }

    protected function successJsonResponse(string $message, array $data = []): JsonResponse
    {
        return response()->json(array_merge([
            'ok' => true,
            'message' => $message,
        ], $data));
    }

    protected function errorJsonResponse(string $message, int $status = 422, array $data = []): JsonResponse
    {
        return response()->json(array_merge([
            'ok' => false,
            'message' => $message,
        ], $data), $status);
    }

    protected function errorResponse(
        Request $request,
        string $message,
        string $errorKey = 'error',
        string $route = 'cashier.index'
    ): RedirectResponse|JsonResponse {
        if ($this->wantsJson($request)) {
            return $this->errorJsonResponse($message);
        }

        return redirect()
            ->route($route)
            ->withErrors([$errorKey => $message]);
    }

    protected function validationFailureResponse(
        Request $request,
        string $message,
        string $errorKey
    ): RedirectResponse|JsonResponse {
        if ($this->wantsJson($request)) {
            return $this->errorJsonResponse($message);
        }

        return back()->withErrors([$errorKey => $message]);
    }

    protected function attendanceStats(): array
    {
        $totalCashiers = (int) $this->cashierUsersQuery()->count();

        $checkedTodayCount = (int) CashierAttendance::query()
            ->whereDate('attended_on', now()->toDateString())
            ->count();

        $pendingTodayCount = max($totalCashiers - $checkedTodayCount, 0);
        $attendanceRate = $totalCashiers > 0
            ? (int) round(($checkedTodayCount / $totalCashiers) * 100)
            : 0;

        return [
            'total_cashiers' => $totalCashiers,
            'checked_today_count' => $checkedTodayCount,
            'pending_today_count' => $pendingTodayCount,
            'attendance_rate' => $attendanceRate,
        ];
    }

    protected function historyPaymentOptions(Builder $query): array
    {
        if (! Schema::hasColumn('orders', 'payment_method')) {
            return [];
        }

        return $query
            ->selectRaw("LOWER(COALESCE(payment_method, 'unknown')) as value")
            ->pluck('value')
            ->filter(fn ($value): bool => trim((string) $value) !== '')
            ->map(fn ($value): string => (string) $value)
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    protected function historyStatusOptions(Builder $query): array
    {
        if (! Schema::hasColumn('orders', 'status')) {
            return [];
        }

        return $query
            ->selectRaw("LOWER(COALESCE(status, 'completed')) as value")
            ->pluck('value')
            ->filter(fn ($value): bool => trim((string) $value) !== '')
            ->map(fn ($value): string => (string) $value)
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    protected function calculateItemsSold(
        Request $request,
        string $dateColumn,
        mixed $start,
        mixed $end,
        string $search,
        string $selectedPayment,
        string $selectedStatus
    ): int {
        return (int) OrderItem::query()
            ->whereHas('order', function (Builder $query) use (
                $request,
                $dateColumn,
                $start,
                $end,
                $search,
                $selectedPayment,
                $selectedStatus
            ): void {
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
    }

    protected function respondCartMutation(Request $request): RedirectResponse|JsonResponse
    {
        if ($this->wantsJson($request)) {
            $cartState = $this->buildCartState($request);

            return $this->successJsonResponse('Cart updated successfully.', [
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
    protected function renderCartHtml(array $cartState): string
    {
        return view('cashier.sidebar.cart', [
            'cartItems' => $cartState['items'],
            'cartSubtotal' => $cartState['subtotal'],
            'cartDiscount' => $cartState['discount'],
            'cartTotal' => $cartState['total'],
        ])->render();
    }

    protected function orderFailureResponse(Request $request, string $message): RedirectResponse|JsonResponse
    {
        if ($this->wantsJson($request)) {
            return $this->errorJsonResponse($message);
        }

        return back()->withErrors([
            'payment' => $message,
        ]);
    }

    protected function ensureAttendanceChecked(Request $request): RedirectResponse|JsonResponse|null
    {
        $cashierId = (int) ($request->user()?->id ?? 0);

        if ($cashierId <= 0) {
            return $this->errorResponse(
                $request,
                'Unable to verify cashier attendance. Please sign in again.',
                'attendance',
                'cashier.attendance'
            );
        }

        $hasAttendance = CashierAttendance::query()
            ->where('cashier_id', $cashierId)
            ->whereDate('attended_on', now()->toDateString())
            ->exists();

        if ($hasAttendance) {
            return null;
        }

        return $this->errorResponse(
            $request,
            'Please check your attendance before working in POS.',
            'attendance',
            'cashier.attendance'
        );
    }

    protected function generateOrderNumber(): string
    {
        return 'ORD-'.now()->format('Ymd-His').'-'.Str::upper(Str::random(4));
    }

    protected function normalizePeriod(string $period): string
    {
        return in_array($period, ['day', 'week', 'month'], true) ? $period : 'day';
    }

    protected function normalizeHistoryFilter(string $value): string
    {
        $normalized = Str::of($value)
            ->lower()
            ->squish()
            ->value();

        return $normalized === '' ? 'all' : $normalized;
    }

    protected function applyHistoryOrderFilters(Builder $query, string $payment, string $status): void
    {
        if ($payment !== 'all' && Schema::hasColumn('orders', 'payment_method')) {
            $query->whereRaw("LOWER(COALESCE(payment_method, 'unknown')) = ?", [$payment]);
        }

        if ($status !== 'all' && Schema::hasColumn('orders', 'status')) {
            $query->whereRaw("LOWER(COALESCE(status, 'completed')) = ?", [$status]);
        }
    }

    protected function periodRange(string $period): array
    {
        $now = now();

        return match ($period) {
            'week' => [$now->copy()->startOfWeek(), $now->copy()->endOfWeek(), 'This Week'],
            'month' => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth(), 'This Month'],
            default => [$now->copy()->startOfDay(), $now->copy()->endOfDay(), 'Today'],
        };
    }

    protected function orderDateColumn(): string
    {
        return Schema::hasColumn('orders', 'placed_at') ? 'placed_at' : 'created_at';
    }

    protected function cashierOrdersQuery(Request $request): Builder
    {
        $query = Order::query();
        $this->applyCashierScope($query, $request);

        return $query;
    }

    protected function applyCashierScope(Builder $query, Request $request): void
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
    protected function getCart(Request $request): array
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

            if (
                $productId <= 0 ||
                ! in_array($size, self::PRODUCT_SIZES, true) ||
                $qty <= 0
            ) {
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

    protected function storeCart(Request $request, array $cart): void
    {
        $request->session()->put(self::CART_SESSION_KEY, $cart);
    }

    protected function makeCartItemKey(int $productId, string $size, int $sugar): string
    {
        return $productId.'-'.$size.'-'.$sugar;
    }

    protected function buildOrderPayload(
        Request $request,
        array $cartState,
        string $orderNumber,
        string $paymentMethod,
        float $amountReceived,
        float $changeAmount
    ): array {
        $orderMeta = [
            'payment_method' => $paymentMethod,
            'amount_received' => $amountReceived,
            'change_amount' => $changeAmount,
        ];

        $payload = [
            'order_number' => $orderNumber,
            'subtotal' => $cartState['subtotal'],
            'discount' => $cartState['discount'],
            'total' => $cartState['total'],
        ];

        if (Schema::hasColumn('orders', 'cashier_id')) {
            $payload['cashier_id'] = $request->user()?->id;
        } elseif (Schema::hasColumn('orders', 'cashier_user_id')) {
            $payload['cashier_user_id'] = $request->user()?->id;
        }

        if (Schema::hasColumn('orders', 'payment_method')) {
            $payload['payment_method'] = $paymentMethod;
        }

        if (Schema::hasColumn('orders', 'payment_status')) {
            $payload['payment_status'] = 'paid';
        }

        if (Schema::hasColumn('orders', 'amount_received')) {
            $payload['amount_received'] = $amountReceived;
        }

        if (Schema::hasColumn('orders', 'change_amount')) {
            $payload['change_amount'] = $changeAmount;
        }

        if (Schema::hasColumn('orders', 'status')) {
            $payload['status'] = 'completed';
        }

        if (Schema::hasColumn('orders', 'placed_at')) {
            $payload['placed_at'] = now();
        }

        if (Schema::hasColumn('orders', 'admin_notified_at')) {
            $payload['admin_notified_at'] = null;
        }

        if (Schema::hasColumn('orders', 'paid_at')) {
            $payload['paid_at'] = now();
        }

        if (Schema::hasColumn('orders', 'currency')) {
            $payload['currency'] = 'USD';
        }

        if (Schema::hasColumn('orders', 'meta')) {
            $payload['meta'] = json_encode($orderMeta, JSON_THROW_ON_ERROR);
        }

        return $payload;
    }

    protected function buildOrderItemRows(Collection $items, int $orderId): array
    {
        $hasCreatedAt = Schema::hasColumn('order_items', 'created_at');
        $hasUpdatedAt = Schema::hasColumn('order_items', 'updated_at');
        $now = now();

        return $items
            ->map(function (array $item) use ($orderId, $hasCreatedAt, $hasUpdatedAt, $now): array {
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

                if ($hasCreatedAt) {
                    $row['created_at'] = $now;
                }

                if ($hasUpdatedAt) {
                    $row['updated_at'] = $now;
                }

                return $row;
            })
            ->all();
    }

    protected function recordInventoryMoneyIn(
        Request $request,
        float $amount,
        string $orderNumber,
        string $paymentMethod
    ): void {
        if (! Schema::hasTable('inventory_transactions')) {
            return;
        }

        InventoryTransaction::query()->create([
            'type' => InventoryTransaction::TYPE_MONEY_IN,
            'amount' => $amount,
            'note' => 'Order '.$orderNumber.' paid via '.strtoupper($paymentMethod),
            'happened_at' => now(),
            'created_by' => $request->user()?->id,
        ]);
    }

    /**
     * @return array{
     *   items: Collection<int, array{
     *     item_key: string,
     *     product_id: int,
     *     name: string,
     *     image_path: string|null,
     *     image_url: string|null,
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
     * }
     */
    protected function buildCartState(Request $request): array
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
                    'image_url' => $product->imageUrl(),
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
            ->mapWithKeys(function (array $item): array {
                return [
                    $item['item_key'] => [
                        'product_id' => $item['product_id'],
                        'size' => $item['size'],
                        'sugar' => $item['sugar'],
                        'qty' => $item['qty'],
                    ],
                ];
            })
            ->all();

        if ($validCart !== $cart) {
            $this->storeCart($request, $validCart);
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
