<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
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
        $dateColumn = $this->orderDateColumn();
        [$start, $end, $periodLabel] = $this->periodRange($period);

        $ordersQuery = $this->cashierOrdersQuery($request)
            ->withCount('items')
            ->whereBetween($dateColumn, [$start, $end])
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
        $itemsSold = (int) OrderItem::query()
            ->whereHas('order', function (Builder $query) use ($request, $dateColumn, $start, $end): void {
                $this->applyCashierScope($query, $request);
                $query->whereBetween($dateColumn, [$start, $end]);
            })
            ->sum('qty');

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
            'itemsSold' => $itemsSold,
            'cartItems' => $cartState['items'],
            'cartSubtotal' => $cartState['subtotal'],
            'cartDiscount' => $cartState['discount'],
            'cartTotal' => $cartState['total'],
        ]);
    }

    public function reports(Request $request): View
    {
        $period = $this->normalizePeriod((string) $request->query('period', 'day'));
        $dateColumn = $this->orderDateColumn();

        [$selectedStart, $selectedEnd, $selectedLabel, $startDate, $endDate, $hasCustomRange] = $this->resolveReportsDateRange(
            $request,
            $period,
        );
        [$dayStart, $dayEnd] = $this->periodRange('day');
        [$weekStart, $weekEnd] = $this->periodRange('week');
        [$monthStart, $monthEnd] = $this->periodRange('month');

        $daySummary = $this->summarizeRange($request, $dayStart, $dayEnd, $dateColumn);
        $weekSummary = $this->summarizeRange($request, $weekStart, $weekEnd, $dateColumn);
        $monthSummary = $this->summarizeRange($request, $monthStart, $monthEnd, $dateColumn);
        $selectedSummary = $this->summarizeRange($request, $selectedStart, $selectedEnd, $dateColumn);

        $paymentBreakdown = $this->cashierOrdersQuery($request)
            ->whereBetween($dateColumn, [$selectedStart, $selectedEnd])
            ->selectRaw("COALESCE(payment_method, 'unknown') as payment_method, COUNT(*) as orders_count, SUM(total) as revenue")
            ->groupBy('payment_method')
            ->orderByDesc('revenue')
            ->get();

        $topItems = OrderItem::query()
            ->selectRaw('product_name, SUM(qty) as qty_sold, SUM(line_total) as revenue')
            ->whereHas('order', function (Builder $query) use ($request, $dateColumn, $selectedStart, $selectedEnd): void {
                $this->applyCashierScope($query, $request);
                $query->whereBetween($dateColumn, [$selectedStart, $selectedEnd]);
            })
            ->groupBy('product_name')
            ->orderByDesc('qty_sold')
            ->limit(8)
            ->get();

        $recentOrders = $this->cashierOrdersQuery($request)
            ->whereBetween($dateColumn, [$selectedStart, $selectedEnd])
            ->orderByDesc($dateColumn)
            ->limit(8)
            ->get();

        $reportTrend = $this->buildReportTrend(
            $request,
            $dateColumn,
            $period,
            $selectedStart,
            $selectedEnd,
            $hasCustomRange,
        );

        $reportCharts = [
            'trend' => $reportTrend,
            'comparison' => [
                'labels' => ['Today', 'This Week', 'This Month'],
                'orders' => [
                    (int) ($daySummary['orders'] ?? 0),
                    (int) ($weekSummary['orders'] ?? 0),
                    (int) ($monthSummary['orders'] ?? 0),
                ],
                'revenue' => [
                    (float) ($daySummary['revenue'] ?? 0),
                    (float) ($weekSummary['revenue'] ?? 0),
                    (float) ($monthSummary['revenue'] ?? 0),
                ],
                'items' => [
                    (int) ($daySummary['items'] ?? 0),
                    (int) ($weekSummary['items'] ?? 0),
                    (int) ($monthSummary['items'] ?? 0),
                ],
            ],
            'payments' => [
                'labels' => $paymentBreakdown
                    ->map(fn ($payment): string => strtoupper((string) ($payment->payment_method ?? 'UNKNOWN')))
                    ->values()
                    ->all(),
                'orders' => $paymentBreakdown
                    ->map(fn ($payment): int => (int) ($payment->orders_count ?? 0))
                    ->values()
                    ->all(),
                'revenue' => $paymentBreakdown
                    ->map(fn ($payment): float => round((float) ($payment->revenue ?? 0), 2))
                    ->values()
                    ->all(),
            ],
            'topItems' => [
                'labels' => $topItems
                    ->map(fn ($item): string => (string) ($item->product_name ?? 'Item'))
                    ->values()
                    ->all(),
                'qty' => $topItems
                    ->map(fn ($item): int => (int) ($item->qty_sold ?? 0))
                    ->values()
                    ->all(),
                'revenue' => $topItems
                    ->map(fn ($item): float => round((float) ($item->revenue ?? 0), 2))
                    ->values()
                    ->all(),
            ],
        ];

        $cartState = $this->buildCartState($request);

        return view('cashier.reports', [
            'period' => $period,
            'selectedLabel' => $selectedLabel,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'hasCustomRange' => $hasCustomRange,
            'daySummary' => $daySummary,
            'weekSummary' => $weekSummary,
            'monthSummary' => $monthSummary,
            'selectedSummary' => $selectedSummary,
            'paymentBreakdown' => $paymentBreakdown,
            'topItems' => $topItems,
            'recentOrders' => $recentOrders,
            'reportCharts' => $reportCharts,
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
     *     unit_price: float,
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

    /**
     * @return array{0: Carbon, 1: Carbon, 2: string, 3: string, 4: string, 5: bool}
     */
    private function resolveReportsDateRange(Request $request, string $period): array
    {
        $startInput = trim((string) $request->query('start_date', ''));
        $endInput = trim((string) $request->query('end_date', ''));

        if ($startInput !== '' && $endInput !== '') {
            try {
                $startDate = Carbon::createFromFormat('Y-m-d', $startInput)->startOfDay();
                $endDate = Carbon::createFromFormat('Y-m-d', $endInput)->endOfDay();

                if ($startDate->gt($endDate)) {
                    [$startDate, $endDate] = [$endDate->copy()->startOfDay(), $startDate->copy()->endOfDay()];
                }

                $normalizedStart = $startDate->toDateString();
                $normalizedEnd = $endDate->toDateString();
                $label = $normalizedStart === $normalizedEnd
                    ? 'Custom: ' . $startDate->format('M d, Y')
                    : 'Custom: ' . $startDate->format('M d, Y') . ' - ' . $endDate->format('M d, Y');

                return [$startDate, $endDate, $label, $normalizedStart, $normalizedEnd, true];
            } catch (\Throwable $exception) {
            }
        }

        [$defaultStart, $defaultEnd, $defaultLabel] = $this->periodRange($period);

        return [
            $defaultStart,
            $defaultEnd,
            $defaultLabel,
            $defaultStart->toDateString(),
            $defaultEnd->toDateString(),
            false,
        ];
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
     * @return array{orders: int, items: int, revenue: float, average: float}
     */
    private function summarizeRange(Request $request, Carbon $start, Carbon $end, string $dateColumn): array
    {
        $ordersQuery = $this->cashierOrdersQuery($request)
            ->whereBetween($dateColumn, [$start, $end]);

        $orders = (int) (clone $ordersQuery)->count();
        $revenue = (float) (clone $ordersQuery)->sum('total');
        $items = (int) OrderItem::query()
            ->whereHas('order', function (Builder $query) use ($request, $dateColumn, $start, $end): void {
                $this->applyCashierScope($query, $request);
                $query->whereBetween($dateColumn, [$start, $end]);
            })
            ->sum('qty');

        return [
            'orders' => $orders,
            'items' => $items,
            'revenue' => $revenue,
            'average' => $orders > 0 ? $revenue / $orders : 0.0,
        ];
    }

    /**
     * @return array{
     *   label: string,
     *   labels: array<int, string>,
     *   orders: array<int, int>,
     *   revenue: array<int, float>
     * }
     */
    private function buildReportTrend(
        Request $request,
        string $dateColumn,
        string $period,
        Carbon $start,
        Carbon $end,
        bool $hasCustomRange
    ): array {
        $orders = $this->cashierOrdersQuery($request)
            ->whereBetween($dateColumn, [$start, $end])
            ->orderBy($dateColumn)
            ->get([$dateColumn, 'total']);

        $buckets = [];
        $labelMap = [];
        $label = 'Sales Trend';

        $useHourly = $period === 'day' && ! $hasCustomRange && $start->isSameDay($end);

        if ($useHourly) {
            $label = 'Hourly Sales';

            for ($hour = 0; $hour < 24; $hour++) {
                $key = str_pad((string) $hour, 2, '0', STR_PAD_LEFT);
                $buckets[$key] = ['orders' => 0, 'revenue' => 0.0];
                $labelMap[$key] = Carbon::createFromTime($hour)->format('ga');
            }
        } else {
            $label = $hasCustomRange
                ? 'Daily Sales (Custom Range)'
                : ($period === 'week' ? 'Daily Sales (Week)' : 'Daily Sales (Month)');

            /** @var Carbon $dayPoint */
            foreach (CarbonPeriod::create($start->copy()->startOfDay(), '1 day', $end->copy()->startOfDay()) as $dayPoint) {
                $key = $dayPoint->format('Y-m-d');
                $buckets[$key] = ['orders' => 0, 'revenue' => 0.0];
                $labelMap[$key] = $period === 'week'
                    ? $dayPoint->format('D')
                    : $dayPoint->format('M j');
            }
        }

        foreach ($orders as $order) {
            $orderDateValue = $order->{$dateColumn} ?? null;
            if ($orderDateValue === null) {
                continue;
            }

            $orderDate = $orderDateValue instanceof Carbon
                ? $orderDateValue->copy()
                : Carbon::parse((string) $orderDateValue);

            $bucketKey = $useHourly
                ? $orderDate->format('H')
                : $orderDate->format('Y-m-d');

            if (! isset($buckets[$bucketKey])) {
                continue;
            }

            $buckets[$bucketKey]['orders']++;
            $buckets[$bucketKey]['revenue'] += (float) ($order->total ?? 0);
        }

        $labels = [];
        $ordersSeries = [];
        $revenueSeries = [];

        foreach ($buckets as $key => $bucket) {
            $labels[] = $labelMap[$key] ?? $key;
            $ordersSeries[] = (int) ($bucket['orders'] ?? 0);
            $revenueSeries[] = round((float) ($bucket['revenue'] ?? 0), 2);
        }

        return [
            'label' => $label,
            'labels' => $labels,
            'orders' => $ordersSeries,
            'revenue' => $revenueSeries,
        ];
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
                $unitPrice = (float) $product->price;
                $lineTotal = $unitPrice * $qty;

                return [
                    'item_key' => $itemKey,
                    'product_id' => $product->id,
                    'name' => (string) $product->name,
                    'image_path' => $product->image_path,
                    'size' => $size,
                    'sugar' => $sugar,
                    'qty' => $qty,
                    'unit_price' => $unitPrice,
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

        $subtotal = (float) $items->sum('line_total');
        $discount = 0.0;
        $total = max($subtotal - $discount, 0.0);

        return [
            'items' => $items,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'total' => $total,
        ];
    }
}
