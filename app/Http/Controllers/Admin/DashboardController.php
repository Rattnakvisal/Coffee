<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $searchQuery = trim((string) $request->query('q', ''));
        $searchFeedback = session('searchFeedback');

        $now = now();
        $today = $now->copy()->startOfDay();

        $activeProducts = Product::query()->active();
        $inventoryValue = (float) $activeProducts->sum('price');
        $activeProductsCount = (int) $activeProducts->count();
        $categoriesCount = (int) Category::query()->active()->count();
        $cashiersCount = $this->usersCountByRole('cashier');
        $adminsCount = $this->usersCountByRole('admin');

        $productsToday = Product::query()->whereDate('created_at', $today)->count();
        $productsCurrentWeek = Product::query()
            ->whereBetween('created_at', [$now->copy()->startOfWeek(), $now])
            ->count();
        $productsPreviousWeek = Product::query()
            ->whereBetween('created_at', [
                $now->copy()->subWeek()->startOfWeek(),
                $now->copy()->subWeek()->endOfWeek(),
            ])
            ->count();

        $usersCurrentWeek = User::query()
            ->whereBetween('created_at', [$now->copy()->startOfWeek(), $now])
            ->count();
        $usersPreviousWeek = User::query()
            ->whereBetween('created_at', [
                $now->copy()->subWeek()->startOfWeek(),
                $now->copy()->subWeek()->endOfWeek(),
            ])
            ->count();

        $inventoryCurrentMonth = (float) Product::query()
            ->active()
            ->whereBetween('created_at', [$now->copy()->startOfMonth(), $now])
            ->sum('price');
        $inventoryPreviousMonth = (float) Product::query()
            ->active()
            ->whereBetween('created_at', [
                $now->copy()->subMonth()->startOfMonth(),
                $now->copy()->subMonth()->endOfMonth(),
            ])
            ->sum('price');

        $recentProducts = Product::query()
            ->with('category')
            ->latest()
            ->take(6)
            ->get();

        $topProducts = Product::query()
            ->active()
            ->orderByDesc('price')
            ->take(5)
            ->get(['id', 'name', 'price']);

        $topProductsMaxPrice = (float) ($topProducts->max('price') ?? 0);

        $categoryMix = Category::query()
            ->active()
            ->withCount('products')
            ->orderByDesc('products_count')
            ->take(6)
            ->get()
            ->filter(fn (Category $category): bool => $category->products_count > 0)
            ->values();

        if ($categoryMix->isEmpty()) {
            $categoryMix = collect([
                (object) ['name' => 'No Data', 'products_count' => 1],
            ]);
        }

        $weeklyTimeline = [];
        $weekLabels = [];

        /** @var Carbon $day */
        foreach (CarbonPeriod::create($today->copy()->subDays(6), '1 day', $today) as $day) {
            $start = $day->copy()->startOfDay();
            $end = $day->copy()->endOfDay();

            $weekLabels[] = $day->format('D');
            $weeklyTimeline[] = [
                'products' => Product::query()->whereBetween('created_at', [$start, $end])->count(),
                'inventory' => (float) Product::query()
                    ->active()
                    ->whereBetween('created_at', [$start, $end])
                    ->sum('price'),
            ];
        }

        $monthLabels = [];
        $monthlyProducts = [];

        /** @var Carbon $monthPoint */
        foreach (CarbonPeriod::create($now->copy()->subMonths(5)->startOfMonth(), '1 month', $now->copy()->startOfMonth()) as $monthPoint) {
            $monthLabels[] = $monthPoint->format('M');
            $monthlyProducts[] = Product::query()
                ->whereBetween('created_at', [$monthPoint->copy()->startOfMonth(), $monthPoint->copy()->endOfMonth()])
                ->count();
        }

        $rolesDistribution = Role::query()
            ->active()
            ->whereIn('slug', ['admin', 'cashier'])
            ->withCount('users')
            ->orderBy('name')
            ->get();

        if ($rolesDistribution->isEmpty()) {
            $rolesDistribution = Role::query()
                ->active()
                ->withCount('users')
                ->orderBy('name')
                ->take(6)
                ->get();
        }

        $searchSuggestions = collect([
            ['label' => 'Dashboard', 'value' => 'dashboard', 'type' => 'Shortcut', 'meta' => 'Overview'],
            ['label' => 'Products', 'value' => 'products', 'type' => 'Shortcut', 'meta' => 'Manage products'],
            ['label' => 'Categories', 'value' => 'categories', 'type' => 'Shortcut', 'meta' => 'Manage categories'],
            ['label' => 'Users', 'value' => 'users', 'type' => 'Shortcut', 'meta' => 'Manage staff'],
            ['label' => 'Reports', 'value' => 'reports', 'type' => 'Shortcut', 'meta' => 'Sales analytics'],
            ['label' => 'Settings', 'value' => 'settings', 'type' => 'Shortcut', 'meta' => 'Account settings'],
            ['label' => 'Profile', 'value' => 'profile', 'type' => 'Shortcut', 'meta' => 'Profile settings'],
            ['label' => 'Password', 'value' => 'password', 'type' => 'Shortcut', 'meta' => 'Security settings'],
        ])
            ->merge(
                Product::query()
                    ->latest()
                    ->limit(8)
                    ->get(['name'])
                    ->map(fn (Product $product): array => [
                        'label' => (string) $product->name,
                        'value' => (string) $product->name,
                        'type' => 'Product',
                        'meta' => 'Item',
                    ]),
            )
            ->merge(
                Category::query()
                    ->latest()
                    ->limit(8)
                    ->get(['name'])
                    ->map(fn (Category $category): array => [
                        'label' => (string) $category->name,
                        'value' => (string) $category->name,
                        'type' => 'Category',
                        'meta' => 'Group',
                    ]),
            )
            ->merge(
                User::query()
                    ->latest()
                    ->limit(8)
                    ->get(['name', 'email'])
                    ->map(fn (User $user): array => [
                        'label' => (string) $user->name,
                        'value' => (string) $user->name,
                        'type' => 'User',
                        'meta' => (string) $user->email,
                    ]),
            )
            ->filter(fn (array $item): bool => trim((string) ($item['value'] ?? '')) !== '')
            ->unique(fn (array $item): string => strtolower($item['value'] . '|' . $item['type']))
            ->values();

        return view('admin.index', [
            'stats' => [
                'inventoryValue' => $inventoryValue,
                'productsToday' => $productsToday,
                'activeProductsCount' => $activeProductsCount,
                'categoriesCount' => $categoriesCount,
                'cashiersCount' => $cashiersCount,
                'adminsCount' => $adminsCount,
                'productsGrowth' => $this->growthLabel($productsCurrentWeek, $productsPreviousWeek, 'vs last week'),
                'usersGrowth' => $this->growthLabel($usersCurrentWeek, $usersPreviousWeek, 'vs last week'),
                'inventoryGrowth' => $this->growthLabel($inventoryCurrentMonth, $inventoryPreviousMonth, 'vs last month'),
            ],
            'recentProducts' => $recentProducts,
            'topProducts' => $topProducts,
            'topProductsMaxPrice' => $topProductsMaxPrice,
            'charts' => [
                'weekLabels' => $weekLabels,
                'weeklyProducts' => array_column($weeklyTimeline, 'products'),
                'weeklyInventory' => array_map(
                    static fn (float $value): float => round($value, 2),
                    array_column($weeklyTimeline, 'inventory'),
                ),
                'monthLabels' => $monthLabels,
                'monthlyProducts' => $monthlyProducts,
                'categoryLabels' => $categoryMix->pluck('name')->values(),
                'categoryCounts' => $categoryMix->pluck('products_count')->map(fn ($count): int => (int) $count)->values(),
                'roleLabels' => $rolesDistribution
                    ->map(fn (Role $role): string => str($role->name)->headline())
                    ->values(),
                'roleCounts' => $rolesDistribution
                    ->pluck('users_count')
                    ->map(fn ($count): int => (int) $count)
                    ->values(),
            ],
            'searchQuery' => $searchQuery,
            'searchFeedback' => $searchFeedback,
            'searchSuggestions' => $searchSuggestions,
        ]);
    }

    public function reports(Request $request): View
    {
        $dateColumn = $this->orderDateColumn();
        [$start, $end, $rangeLabel, $startDate, $endDate, $hasCustomRange, $selectedPreset] = $this->resolveReportDateRange($request);

        $ordersQuery = Order::query()->whereBetween($dateColumn, [$start, $end]);

        $ordersCount = (int) (clone $ordersQuery)->count();
        $revenue = (float) (clone $ordersQuery)->sum('total');
        $subtotal = Schema::hasColumn('orders', 'subtotal')
            ? (float) (clone $ordersQuery)->sum('subtotal')
            : 0.0;
        $discountTotal = Schema::hasColumn('orders', 'discount')
            ? (float) (clone $ordersQuery)->sum('discount')
            : 0.0;
        $itemsSold = (int) OrderItem::query()
            ->whereHas('order', function ($query) use ($dateColumn, $start, $end): void {
                $query->whereBetween($dateColumn, [$start, $end]);
            })
            ->sum('qty');
        $averageOrder = $ordersCount > 0 ? $revenue / $ordersCount : 0.0;
        $avgItemsPerOrder = $ordersCount > 0 ? $itemsSold / $ordersCount : 0.0;
        $grossSales = $subtotal > 0 ? $subtotal : max($revenue + $discountTotal, 0);
        $discountRate = $grossSales > 0 ? ($discountTotal / $grossSales) * 100 : 0.0;

        $rangeDays = max($start->diffInDays($end) + 1, 1);
        $previousEnd = $start->copy()->subDay()->endOfDay();
        $previousStart = $previousEnd->copy()->subDays($rangeDays - 1)->startOfDay();

        $previousOrdersQuery = Order::query()->whereBetween($dateColumn, [$previousStart, $previousEnd]);
        $previousOrders = (int) (clone $previousOrdersQuery)->count();
        $previousRevenue = (float) (clone $previousOrdersQuery)->sum('total');
        $previousItems = (int) OrderItem::query()
            ->whereHas('order', function ($query) use ($dateColumn, $previousStart, $previousEnd): void {
                $query->whereBetween($dateColumn, [$previousStart, $previousEnd]);
            })
            ->sum('qty');

        $paymentBreakdown = Schema::hasColumn('orders', 'payment_method')
            ? Order::query()
                ->whereBetween($dateColumn, [$start, $end])
                ->selectRaw("COALESCE(payment_method, 'unknown') as payment_method, COUNT(*) as orders_count, SUM(total) as revenue")
                ->groupBy('payment_method')
                ->orderByDesc('revenue')
                ->get()
            : collect([
                (object) [
                    'payment_method' => 'unknown',
                    'orders_count' => $ordersCount,
                    'revenue' => $revenue,
                ],
            ]);

        $statusBreakdown = Schema::hasColumn('orders', 'status')
            ? Order::query()
                ->whereBetween($dateColumn, [$start, $end])
                ->selectRaw("COALESCE(status, 'completed') as status_name, COUNT(*) as orders_count, SUM(total) as revenue")
                ->groupBy('status_name')
                ->orderByDesc('orders_count')
                ->get()
            : collect([
                (object) [
                    'status_name' => 'completed',
                    'orders_count' => $ordersCount,
                    'revenue' => $revenue,
                ],
            ]);

        $topItems = OrderItem::query()
            ->selectRaw('product_name, SUM(qty) as qty_sold, SUM(line_total) as revenue')
            ->whereHas('order', function ($query) use ($dateColumn, $start, $end): void {
                $query->whereBetween($dateColumn, [$start, $end]);
            })
            ->groupBy('product_name')
            ->orderByDesc('qty_sold')
            ->limit(8)
            ->get();

        $categoryBreakdown = OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->leftJoin('products', 'order_items.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->whereBetween('orders.' . $dateColumn, [$start, $end])
            ->selectRaw("COALESCE(categories.name, 'Uncategorized') as category_name, SUM(order_items.qty) as qty_sold, SUM(order_items.line_total) as revenue")
            ->groupBy('category_name')
            ->orderByDesc('revenue')
            ->limit(8)
            ->get();

        $recentOrders = Order::query()
            ->whereBetween($dateColumn, [$start, $end])
            ->orderByDesc($dateColumn)
            ->limit(10)
            ->get();

        $cashierBreakdown = collect();
        $cashierColumn = $this->orderCashierColumn();
        if ($cashierColumn !== null) {
            $cashierRows = Order::query()
                ->whereBetween($dateColumn, [$start, $end])
                ->selectRaw($cashierColumn . ' as cashier_ref, COUNT(*) as orders_count, SUM(total) as revenue')
                ->groupBy($cashierColumn)
                ->orderByDesc('revenue')
                ->limit(8)
                ->get();

            $cashierNames = User::query()
                ->whereIn('id', $cashierRows->pluck('cashier_ref')->filter()->map(fn ($id): int => (int) $id)->all())
                ->pluck('name', 'id');

            $cashierBreakdown = $cashierRows->map(function ($row) use ($cashierNames) {
                $cashierId = (int) ($row->cashier_ref ?? 0);

                return (object) [
                    'cashier_name' => $cashierId > 0
                        ? (string) ($cashierNames[$cashierId] ?? 'Unknown Cashier')
                        : 'Unknown Cashier',
                    'orders_count' => (int) ($row->orders_count ?? 0),
                    'revenue' => (float) ($row->revenue ?? 0),
                ];
            })->values();
        }
        $activeCashiers = (int) $cashierBreakdown->count();

        $charts = [
            'trend' => $this->buildAdminTrend($dateColumn, $start, $end, $hasCustomRange),
            'payments' => [
                'labels' => $paymentBreakdown
                    ->map(fn ($row): string => strtoupper((string) ($row->payment_method ?? 'UNKNOWN')))
                    ->values()
                    ->all(),
                'revenue' => $paymentBreakdown
                    ->map(fn ($row): float => round((float) ($row->revenue ?? 0), 2))
                    ->values()
                    ->all(),
            ],
            'topItems' => [
                'labels' => $topItems
                    ->map(fn ($row): string => (string) ($row->product_name ?? 'Item'))
                    ->values()
                    ->all(),
                'qty' => $topItems
                    ->map(fn ($row): int => (int) ($row->qty_sold ?? 0))
                    ->values()
                    ->all(),
                'revenue' => $topItems
                    ->map(fn ($row): float => round((float) ($row->revenue ?? 0), 2))
                    ->values()
                    ->all(),
            ],
            'statuses' => [
                'labels' => $statusBreakdown
                    ->map(fn ($row): string => str((string) ($row->status_name ?? 'completed'))->headline()->toString())
                    ->values()
                    ->all(),
                'orders' => $statusBreakdown
                    ->map(fn ($row): int => (int) ($row->orders_count ?? 0))
                    ->values()
                    ->all(),
                'revenue' => $statusBreakdown
                    ->map(fn ($row): float => round((float) ($row->revenue ?? 0), 2))
                    ->values()
                    ->all(),
            ],
            'categories' => [
                'labels' => $categoryBreakdown
                    ->map(fn ($row): string => (string) ($row->category_name ?? 'Uncategorized'))
                    ->values()
                    ->all(),
                'qty' => $categoryBreakdown
                    ->map(fn ($row): int => (int) ($row->qty_sold ?? 0))
                    ->values()
                    ->all(),
                'revenue' => $categoryBreakdown
                    ->map(fn ($row): float => round((float) ($row->revenue ?? 0), 2))
                    ->values()
                    ->all(),
            ],
            'comparison' => [
                'labels' => ['Previous', 'Selected'],
                'orders' => [$previousOrders, $ordersCount],
                'items' => [$previousItems, $itemsSold],
                'revenue' => [round($previousRevenue, 2), round($revenue, 2)],
            ],
        ];

        return view('admin.reports', [
            'rangeLabel' => $rangeLabel,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'selectedPreset' => $selectedPreset,
            'ordersCount' => $ordersCount,
            'revenue' => $revenue,
            'grossSales' => $grossSales,
            'discountTotal' => $discountTotal,
            'discountRate' => $discountRate,
            'itemsSold' => $itemsSold,
            'averageOrder' => $averageOrder,
            'avgItemsPerOrder' => $avgItemsPerOrder,
            'activeCashiers' => $activeCashiers,
            'ordersGrowth' => $this->growthLabel($ordersCount, $previousOrders, 'vs previous range'),
            'revenueGrowth' => $this->growthLabel($revenue, $previousRevenue, 'vs previous range'),
            'itemsGrowth' => $this->growthLabel($itemsSold, $previousItems, 'vs previous range'),
            'paymentBreakdown' => $paymentBreakdown,
            'statusBreakdown' => $statusBreakdown,
            'topItems' => $topItems,
            'categoryBreakdown' => $categoryBreakdown,
            'recentOrders' => $recentOrders,
            'cashierBreakdown' => $cashierBreakdown,
            'charts' => $charts,
        ]);
    }

    public function search(Request $request): RedirectResponse
    {
        $search = trim((string) $request->query('q', ''));

        if ($search === '') {
            return redirect()->route('admin.index');
        }

        $target = $this->resolveRouteFromSearch($search);

        if ($target === null) {
            return redirect()
                ->route('admin.index', ['q' => $search])
                ->with('searchFeedback', 'No match found. Try products, categories, users, or settings.');
        }

        $url = route($target['route'], $target['params'] ?? []);

        if (! empty($target['fragment'])) {
            $url .= '#' . $target['fragment'];
        }

        return redirect()->to($url);
    }

    private function resolveRouteFromSearch(string $search): ?array
    {
        $normalized = Str::of($search)->lower()->squish()->value();

        if ($normalized === '') {
            return null;
        }

        $keywordMap = [
            'admin.index' => ['dashboard', 'home', 'overview'],
            'admin.products.index' => ['product', 'products', 'item', 'items', 'inventory', 'menu'],
            'admin.categories.index' => ['category', 'categories'],
            'admin.users.index' => ['user', 'users', 'member', 'members', 'staff', 'team', 'admin', 'cashier'],
            'admin.reports' => ['report', 'reports', 'sales', 'analytics', 'summary'],
            'admin.settings.index#profile' => ['setting', 'settings', 'profile', 'account'],
            'admin.settings.index#security' => ['password', 'security'],
        ];

        foreach ($keywordMap as $target => $keywords) {
            foreach ($keywords as $keyword) {
                if ($normalized === $keyword || str_contains($normalized, $keyword)) {
                    return $this->toSearchTarget($target, $search);
                }
            }
        }

        $productMatches = Product::query()
            ->where(function ($query) use ($search): void {
                $query
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            })
            ->count();

        $categoryMatches = Category::query()
            ->where(function ($query) use ($search): void {
                $query
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            })
            ->count();

        $userMatches = User::query()
            ->where(function ($query) use ($search): void {
                $query
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->count();

        $matchedCounts = [
            'admin.products.index' => $productMatches,
            'admin.categories.index' => $categoryMatches,
            'admin.users.index' => $userMatches,
        ];

        arsort($matchedCounts);
        $topRoute = array_key_first($matchedCounts);
        $topCount = $topRoute ? $matchedCounts[$topRoute] : 0;

        if ($topRoute !== null && $topCount > 0) {
            return $this->toSearchTarget($topRoute, $search);
        }

        return null;
    }

    private function toSearchTarget(string $target, string $search): array
    {
        [$route, $fragment] = array_pad(explode('#', $target, 2), 2, null);

        $params = [];

        if (in_array($route, ['admin.products.index', 'admin.categories.index', 'admin.users.index'], true)) {
            $params['search'] = $search;
        } elseif ($route === 'admin.index') {
            $params['q'] = $search;
        }

        return [
            'route' => $route,
            'params' => $params,
            'fragment' => $fragment,
        ];
    }

    private function orderDateColumn(): string
    {
        return Schema::hasColumn('orders', 'placed_at') ? 'placed_at' : 'created_at';
    }

    private function orderCashierColumn(): ?string
    {
        if (Schema::hasColumn('orders', 'cashier_id')) {
            return 'cashier_id';
        }

        if (Schema::hasColumn('orders', 'cashier_user_id')) {
            return 'cashier_user_id';
        }

        return null;
    }

    /**
     * @return array{0: Carbon, 1: Carbon, 2: string, 3: string, 4: string, 5: bool, 6: string}
     */
    private function resolveReportDateRange(Request $request): array
    {
        $startInput = trim((string) $request->query('start_date', ''));
        $endInput = trim((string) $request->query('end_date', ''));
        $preset = trim((string) $request->query('preset', ''));

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

                return [$startDate, $endDate, $label, $normalizedStart, $normalizedEnd, true, 'custom'];
            } catch (\Throwable $exception) {
            }
        }

        $now = now();

        [$startDate, $endDate, $label, $resolvedPreset] = match ($preset) {
            'today' => [$now->copy()->startOfDay(), $now->copy()->endOfDay(), 'Today', 'today'],
            'yesterday' => [
                $now->copy()->subDay()->startOfDay(),
                $now->copy()->subDay()->endOfDay(),
                'Yesterday',
                'yesterday',
            ],
            'last30' => [$now->copy()->subDays(29)->startOfDay(), $now->copy()->endOfDay(), 'Last 30 Days', 'last30'],
            'this_month' => [$now->copy()->startOfMonth(), $now->copy()->endOfDay(), 'This Month', 'this_month'],
            default => [$now->copy()->subDays(6)->startOfDay(), $now->copy()->endOfDay(), 'Last 7 Days', 'last7'],
        };

        return [
            $startDate,
            $endDate,
            $label,
            $startDate->toDateString(),
            $endDate->toDateString(),
            false,
            $resolvedPreset,
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
    private function buildAdminTrend(string $dateColumn, Carbon $start, Carbon $end, bool $hasCustomRange): array
    {
        $buckets = [];
        $labels = [];

        /** @var Carbon $day */
        foreach (CarbonPeriod::create($start->copy()->startOfDay(), '1 day', $end->copy()->startOfDay()) as $day) {
            $key = $day->toDateString();
            $buckets[$key] = ['orders' => 0, 'revenue' => 0.0];
            $labels[$key] = $day->format('M j');
        }

        $orders = Order::query()
            ->whereBetween($dateColumn, [$start, $end])
            ->orderBy($dateColumn)
            ->get([$dateColumn, 'total']);

        foreach ($orders as $order) {
            $orderDate = $order->{$dateColumn} instanceof Carbon
                ? $order->{$dateColumn}->copy()
                : Carbon::parse((string) $order->{$dateColumn});
            $key = $orderDate->toDateString();

            if (! isset($buckets[$key])) {
                continue;
            }

            $buckets[$key]['orders']++;
            $buckets[$key]['revenue'] += (float) ($order->total ?? 0);
        }

        $trendLabels = [];
        $ordersSeries = [];
        $revenueSeries = [];

        foreach ($buckets as $key => $bucket) {
            $trendLabels[] = $labels[$key] ?? $key;
            $ordersSeries[] = (int) ($bucket['orders'] ?? 0);
            $revenueSeries[] = round((float) ($bucket['revenue'] ?? 0), 2);
        }

        return [
            'label' => $hasCustomRange ? 'Daily Trend (Custom Range)' : 'Daily Trend (Last 7 Days)',
            'labels' => $trendLabels,
            'orders' => $ordersSeries,
            'revenue' => $revenueSeries,
        ];
    }

    private function usersCountByRole(string $slug): int
    {
        return User::query()
            ->whereHas('role', function ($query) use ($slug): void {
                $query->where('slug', $slug);
            })
            ->count();
    }

    private function growthLabel(float|int $current, float|int $previous, string $suffix): array
    {
        $difference = $current - $previous;
        $isPositive = $difference >= 0;

        if ($previous > 0) {
            $percent = abs($difference) / $previous * 100;
        } elseif ($current > 0) {
            $percent = 100;
        } else {
            $percent = 0;
        }

        return [
            'isPositive' => $isPositive,
            'text' => sprintf('%s%.1f%% %s', $isPositive ? '+' : '-', $percent, $suffix),
        ];
    }
}
