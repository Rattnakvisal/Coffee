<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\InventoryTransaction;
use App\Models\Order;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class DashboardController extends Controller
{
    private const CAMBODIA_TIMEZONE = 'Asia/Phnom_Penh';

    public function index(Request $request): View|RedirectResponse
    {
        $searchQuery = trim((string) $request->query('q', ''));
        $searchFeedback = session('searchFeedback');

        $now = now();
        $today = $now->copy()->startOfDay();
        $currentMonthStart = $now->copy()->startOfMonth();
        $previousMonthStart = $now->copy()->subMonth()->startOfMonth();
        $previousMonthEnd = $now->copy()->subMonth()->endOfMonth();

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

        $ordersDateColumn = $this->orderDateColumn();
        $paidOrdersQuery = $this->paidOrdersQuery();

        $moneyInTotal = (float) (clone $paidOrdersQuery)->sum('subtotal');
        $moneyOutTotal = (float) InventoryTransaction::query()
            ->moneyOut()
            ->sum('amount');
        $moneyBalance = $moneyInTotal - $moneyOutTotal;
        $moneyInToday = (float) (clone $paidOrdersQuery)
            ->whereDate($ordersDateColumn, $today)
            ->sum('subtotal');
        $moneyOutToday = (float) InventoryTransaction::query()
            ->moneyOut()
            ->whereDate('happened_at', $today)
            ->sum('amount');
        $moneyInCurrentMonth = (float) (clone $paidOrdersQuery)
            ->whereBetween($ordersDateColumn, [$currentMonthStart, $now])
            ->sum('subtotal');
        $moneyInPreviousMonth = (float) (clone $paidOrdersQuery)
            ->whereBetween($ordersDateColumn, [$previousMonthStart, $previousMonthEnd])
            ->sum('subtotal');
        $moneyOutCurrentMonth = (float) InventoryTransaction::query()
            ->moneyOut()
            ->whereBetween('happened_at', [$currentMonthStart, $now])
            ->sum('amount');
        $moneyOutPreviousMonth = (float) InventoryTransaction::query()
            ->moneyOut()
            ->whereBetween('happened_at', [$previousMonthStart, $previousMonthEnd])
            ->sum('amount');

        $moneyInFilter = $this->normalizeMoneyInFilter((string) $request->query('money_in_filter', 'month'));
        [$moneyInFilterStart, $moneyInFilterEnd, $moneyInFilterLabel] = $this->moneyInFilterRange($moneyInFilter, $now);

        $moneyInFilteredOrdersQuery = (clone $paidOrdersQuery);

        if ($moneyInFilterStart !== null && $moneyInFilterEnd !== null) {
            $moneyInFilteredOrdersQuery->whereBetween($ordersDateColumn, [$moneyInFilterStart, $moneyInFilterEnd]);
        }

        $moneyInFilteredTotal = (float) (clone $moneyInFilteredOrdersQuery)->sum('subtotal');
        $moneyInFilteredCount = (int) (clone $moneyInFilteredOrdersQuery)->count();

        $moneyInOrders = (clone $moneyInFilteredOrdersQuery)
            ->orderByDesc($ordersDateColumn)
            ->orderByDesc('id')
            ->limit(10)
            ->get(['id', 'order_number', 'subtotal', 'payment_method', $ordersDateColumn, 'cashier_id']);

        $cashierNames = $moneyInOrders
            ->pluck('cashier_id')
            ->filter(fn($value): bool => (int) $value > 0)
            ->unique()
            ->whenEmpty(fn($collection) => collect([]));

        $cashierNameMap = $cashierNames->isEmpty()
            ? collect()
            : User::query()
                ->whereIn('id', $cashierNames->all())
                ->get(['id', 'name', 'first_name', 'last_name'])
                ->mapWithKeys(function (User $user): array {
                    $display = trim((string) ($user->first_name ?? '') . ' ' . (string) ($user->last_name ?? ''));
                    return [$user->id => $display !== '' ? $display : (string) $user->name];
                });

        $inventoryTransactions = $moneyInOrders
            ->map(function (Order $order) use ($ordersDateColumn, $cashierNameMap): object {
                $cashierId = (int) ($order->cashier_id ?? 0);
                $cashierName = $cashierId > 0 ? (string) ($cashierNameMap->get($cashierId) ?? 'Cashier') : 'Cashier';
                $happenedAt = $order->{$ordersDateColumn};
                $happenedAtLocal = $happenedAt !== null
                    ? Carbon::parse((string) $happenedAt)
                        ->timezone(self::CAMBODIA_TIMEZONE)
                        ->format('d/m/Y H:i') . ' ICT'
                    : '-';

                return (object) [
                    'type' => InventoryTransaction::TYPE_MONEY_IN,
                    'amount' => (float) ($order->subtotal ?? 0),
                    'note' => 'Order ' . (string) ($order->order_number ?? '-') . ' paid via ' . strtoupper((string) ($order->payment_method ?? 'cash')),
                    'happened_at' => $happenedAt,
                    'happened_at_local' => $happenedAtLocal,
                    'actor_name' => $cashierName,
                ];
            })
            ->values();

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

        $latestShoutouts = User::query()
            ->with('role:id,name,slug')
            ->whereHas('role', function ($query): void {
                $query->whereIn('slug', ['admin', 'cashier']);
            })
            ->when(
                $request->user() !== null,
                fn($query) => $query->where('id', '!=', (int) $request->user()->id),
            )
            ->latest()
            ->take(4)
            ->get(['id', 'name', 'first_name', 'last_name', 'email', 'avatar_path', 'role_id', 'created_at']);

        $categoryMix = Category::query()
            ->active()
            ->withCount('products')
            ->orderByDesc('products_count')
            ->take(6)
            ->get()
            ->filter(fn(Category $category): bool => $category->products_count > 0)
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
                    ->map(fn(Product $product): array => [
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
                    ->map(fn(Category $category): array => [
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
                    ->map(fn(User $user): array => [
                        'label' => (string) $user->name,
                        'value' => (string) $user->name,
                        'type' => 'User',
                        'meta' => (string) $user->email,
                    ]),
            )
            ->filter(fn(array $item): bool => trim((string) ($item['value'] ?? '')) !== '')
            ->unique(fn(array $item): string => strtolower($item['value'] . '|' . $item['type']))
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
            'cashflow' => [
                'moneyInTotal' => $moneyInTotal,
                'moneyOutTotal' => $moneyOutTotal,
                'moneyBalance' => $moneyBalance,
                'moneyInToday' => $moneyInToday,
                'moneyOutToday' => $moneyOutToday,
                'moneyInFilter' => $moneyInFilter,
                'moneyInFilterLabel' => $moneyInFilterLabel,
                'moneyInFilteredTotal' => $moneyInFilteredTotal,
                'moneyInFilteredCount' => $moneyInFilteredCount,
                'moneyInGrowth' => $this->growthLabel($moneyInCurrentMonth, $moneyInPreviousMonth, 'vs last month'),
                'moneyOutGrowth' => $this->growthLabel($moneyOutCurrentMonth, $moneyOutPreviousMonth, 'vs last month'),
            ],
            'recentProducts' => $recentProducts,
            'topProducts' => $topProducts,
            'topProductsMaxPrice' => $topProductsMaxPrice,
            'latestShoutouts' => $latestShoutouts,
            'inventoryTransactions' => $inventoryTransactions,
            'inventoryTypeOptions' => [
                [
                    'value' => InventoryTransaction::TYPE_MONEY_OUT,
                    'label' => 'Money Out',
                ],
            ],
            'charts' => [
                'weekLabels' => $weekLabels,
                'weeklyProducts' => array_column($weeklyTimeline, 'products'),
                'weeklyInventory' => array_map(
                    static fn(float $value): float => round($value, 2),
                    array_column($weeklyTimeline, 'inventory'),
                ),
                'monthLabels' => $monthLabels,
                'monthlyProducts' => $monthlyProducts,
                'categoryLabels' => $categoryMix->pluck('name')->values(),
                'categoryCounts' => $categoryMix->pluck('products_count')->map(fn($count): int => (int) $count)->values(),
                'roleLabels' => $rolesDistribution
                    ->map(fn(Role $role): string => str($role->name)->headline())
                    ->values(),
                'roleCounts' => $rolesDistribution
                    ->pluck('users_count')
                    ->map(fn($count): int => (int) $count)
                    ->values(),
            ],
            'searchQuery' => $searchQuery,
            'searchFeedback' => $searchFeedback,
            'searchSuggestions' => $searchSuggestions,
        ]);
    }

    public function storeInventoryTransaction(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type' => [
                'required',
                Rule::in([
                    InventoryTransaction::TYPE_MONEY_OUT,
                ]),
            ],
            'amount' => ['required', 'numeric', 'gt:0'],
            'note' => ['nullable', 'string', 'max:500'],
            'happened_at' => ['nullable', 'date'],
        ]);

        $userId = (int) ($request->user()?->id ?? 0);
        $happenedAt = filled($validated['happened_at'] ?? null)
            ? Carbon::parse((string) $validated['happened_at'], self::CAMBODIA_TIMEZONE)
                ->timezone(config('app.timezone'))
            : now();

        InventoryTransaction::query()->create([
            'type' => $validated['type'],
            'amount' => $validated['amount'],
            'note' => $validated['note'] ?? null,
            'happened_at' => $happenedAt,
            'created_by' => $userId > 0 ? $userId : null,
        ]);

        return redirect()
            ->route('admin.index')
            ->with('alert', [
                'icon' => 'success',
                'title' => 'Inventory Updated',
                'text' => 'Money flow entry has been saved.',
            ]);
    }

    private function orderDateColumn(): string
    {
        return Schema::hasColumn('orders', 'placed_at') ? 'placed_at' : 'created_at';
    }

    private function paidOrdersQuery()
    {
        $query = Order::query();

        if (Schema::hasColumn('orders', 'payment_status')) {
            $query->whereRaw("LOWER(COALESCE(payment_status, 'paid')) = ?", ['paid']);
        }

        return $query;
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
            'admin.index' => ['dashboard', 'home', 'overview', 'cashflow', 'money in', 'money out', 'finance'],
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

    private function normalizeMoneyInFilter(string $value): string
    {
        $normalized = Str::of($value)
            ->lower()
            ->squish()
            ->value();

        return in_array($normalized, ['today', 'week', 'month', 'all'], true) ? $normalized : 'month';
    }

    /**
     * @return array{0: Carbon|null, 1: Carbon|null, 2: string}
     */
    private function moneyInFilterRange(string $filter, Carbon $now): array
    {
        return match ($filter) {
            'today' => [$now->copy()->startOfDay(), $now->copy()->endOfDay(), 'Today'],
            'week' => [$now->copy()->startOfWeek(), $now->copy()->endOfWeek(), 'This Week'],
            'all' => [null, null, 'All Time'],
            default => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth(), 'This Month'],
        };
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
