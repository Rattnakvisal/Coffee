<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CashierAttendance;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;

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

        $newAttendanceRows = CashierAttendance::query()
            ->with('cashier:id,name,first_name,last_name,email')
            ->whereNull('admin_notified_at')
            ->orderByDesc('checked_in_at')
            ->limit(5)
            ->get();

        $attendanceAlert = null;
        $newAttendanceIds = $newAttendanceRows
            ->pluck('id')
            ->map(fn(mixed $id): int => (int) $id)
            ->all();

        if ($newAttendanceRows->isNotEmpty()) {
            $latestChecked = $newAttendanceRows->max('checked_in_at');
            $latestCheckedAt = $latestChecked
                ? Carbon::parse((string) $latestChecked)->format('d/m/Y H:i')
                : now()->format('d/m/Y H:i');

            if ($newAttendanceRows->count() === 1) {
                $cashierName = $this->formatUserDisplayName($newAttendanceRows->first()->cashier);
                $attendanceAlert = $cashierName . ' checked attendance at ' . $latestCheckedAt . '.';
            } else {
                $attendanceAlert = $newAttendanceRows->count() . ' cashiers checked attendance. Latest at ' . $latestCheckedAt . '.';
            }

            CashierAttendance::query()
                ->whereIn('id', $newAttendanceIds)
                ->update([
                    'admin_notified_at' => $now,
                    'updated_at' => $now,
                ]);
        }

        $attendanceRows = CashierAttendance::query()
            ->with('cashier:id,name,first_name,last_name,email')
            ->orderByDesc('checked_in_at')
            ->take(10)
            ->get()
            ->map(function (CashierAttendance $attendance) use ($newAttendanceIds): array {
                $cashierName = $this->formatUserDisplayName($attendance->cashier);

                return [
                    'id' => (int) $attendance->id,
                    'cashier_name' => $cashierName,
                    'cashier_email' => (string) ($attendance->cashier?->email ?? '-'),
                    'checked_in_at' => $attendance->checked_in_at?->format('d/m/Y H:i') ?? '-',
                    'attended_on' => $attendance->attended_on?->format('d/m/Y') ?? '-',
                    'is_new' => in_array((int) $attendance->id, $newAttendanceIds, true),
                ];
            });

        $orderAlert = null;
        $orderNotifications = collect();

        if (Schema::hasTable('orders') && Schema::hasColumn('orders', 'admin_notified_at')) {
            $orderDateColumn = Schema::hasColumn('orders', 'placed_at') ? 'placed_at' : 'created_at';

            $newOrderRows = Order::query()
                ->with('cashier:id,name,first_name,last_name,email')
                ->whereNull('admin_notified_at')
                ->orderByDesc($orderDateColumn)
                ->limit(5)
                ->get();

            if ($newOrderRows->isNotEmpty()) {
                $latestOrderAt = $newOrderRows->max($orderDateColumn);
                $latestOrderAtLabel = $latestOrderAt
                    ? Carbon::parse((string) $latestOrderAt)->format('d/m/Y H:i')
                    : now()->format('d/m/Y H:i');

                if ($newOrderRows->count() === 1) {
                    $singleOrder = $newOrderRows->first();
                    $orderAlert = 'New order ' . (string) ($singleOrder?->order_number ?? '-') . ' at ' . $latestOrderAtLabel . '.';
                } else {
                    $orderAlert = $newOrderRows->count() . ' new orders placed. Latest at ' . $latestOrderAtLabel . '.';
                }

                $orderNotifications = $newOrderRows->map(function (Order $order) use ($orderDateColumn): array {
                    $cashierName = $this->formatUserDisplayName($order->cashier);
                    $orderedAtRaw = $order->{$orderDateColumn};
                    $orderedAtLabel = $orderedAtRaw
                        ? Carbon::parse((string) $orderedAtRaw)->format('d/m/Y H:i')
                        : now()->format('d/m/Y H:i');
                    $paymentLabel = str((string) ($order->payment_method ?? 'cash'))->upper()->value();

                    return [
                        'title' => 'New Order',
                        'message' => 'Order ' . (string) ($order->order_number ?? '-') . ' by ' . $cashierName .
                            ' total $' . number_format((float) ($order->total ?? 0), 2) . ' via ' . $paymentLabel . '.',
                        'time' => $orderedAtLabel,
                    ];
                })->values();

                Order::query()
                    ->whereIn('id', $newOrderRows->pluck('id')->all())
                    ->update([
                        'admin_notified_at' => $now,
                        'updated_at' => $now,
                    ]);
            }
        }

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
            ['label' => 'Inventory', 'value' => 'inventory', 'type' => 'Shortcut', 'meta' => 'Income & outgoing details'],
            ['label' => 'Reports', 'value' => 'reports', 'type' => 'Shortcut', 'meta' => 'Sales analytics'],
            ['label' => 'Attendance', 'value' => 'attendance', 'type' => 'Shortcut', 'meta' => 'Cashier attendance details'],
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
            'recentProducts' => $recentProducts,
            'topProducts' => $topProducts,
            'topProductsMaxPrice' => $topProductsMaxPrice,
            'latestShoutouts' => $latestShoutouts,
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
            'attendanceAlert' => $attendanceAlert,
            'attendanceRows' => $attendanceRows,
            'orderAlert' => $orderAlert,
            'orderNotifications' => $orderNotifications,
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
                ->with('searchFeedback', 'No match found. Try inventory, products, categories, users, or settings.');
        }

        $url = route($target['route'], $target['params'] ?? []);

        if (! empty($target['fragment'])) {
            $url .= '#' . $target['fragment'];
        }

        return redirect()->to($url);
    }

    public function notifications(): JsonResponse
    {
        $notifications = collect();

        if (Schema::hasTable('orders') && Schema::hasColumn('orders', 'admin_notified_at')) {
            $orderDateColumn = Schema::hasColumn('orders', 'placed_at') ? 'placed_at' : 'created_at';

            $orderRows = Order::query()
                ->with('cashier:id,name,first_name,last_name,email')
                ->whereNull('admin_notified_at')
                ->orderByDesc($orderDateColumn)
                ->limit(10)
                ->get();

            $orderNotifications = $orderRows->map(function (Order $order) use ($orderDateColumn): array {
                $orderedAtRaw = $order->{$orderDateColumn};
                $orderedAt = $orderedAtRaw ? Carbon::parse((string) $orderedAtRaw) : now();
                $orderedAtLabel = $orderedAt->format('d/m/Y H:i');
                $cashierName = $this->formatUserDisplayName($order->cashier);
                $paymentLabel = str((string) ($order->payment_method ?? 'cash'))->upper()->value();

                return [
                    'title' => 'New Order',
                    'message' => 'Order ' . (string) ($order->order_number ?? '-') . ' by ' . $cashierName .
                        ' total $' . number_format((float) ($order->total ?? 0), 2) . ' via ' . $paymentLabel . '.',
                    'time' => $orderedAtLabel,
                    'timestamp' => $orderedAt->timestamp,
                ];
            });

            $notifications = $notifications->merge($orderNotifications);
        }

        if (Schema::hasTable('cashier_attendances') && Schema::hasColumn('cashier_attendances', 'admin_notified_at')) {
            $attendanceRows = CashierAttendance::query()
                ->with('cashier:id,name,first_name,last_name,email')
                ->whereNull('admin_notified_at')
                ->orderByDesc('checked_in_at')
                ->limit(10)
                ->get();

            $attendanceNotifications = $attendanceRows->map(function (CashierAttendance $attendance): array {
                $checkedAt = $attendance->checked_in_at ?? now();
                $checkedAtLabel = $checkedAt->format('d/m/Y H:i');
                $cashierName = $this->formatUserDisplayName($attendance->cashier);

                return [
                    'title' => 'Attendance Update',
                    'message' => $cashierName . ' checked attendance at ' . $checkedAtLabel . '.',
                    'time' => $checkedAtLabel,
                    'timestamp' => $checkedAt->timestamp,
                ];
            });

            $notifications = $notifications->merge($attendanceNotifications);
        }

        $notifications = $notifications
            ->sortByDesc('timestamp')
            ->take(10)
            ->values()
            ->map(function (array $notification): array {
                return [
                    'title' => (string) ($notification['title'] ?? 'Notification'),
                    'message' => (string) ($notification['message'] ?? ''),
                    'time' => (string) ($notification['time'] ?? now()->format('d/m/Y H:i')),
                ];
            })
            ->values();

        return response()->json([
            'ok' => true,
            'count' => $notifications->count(),
            'notifications' => $notifications,
        ]);
    }

    public function markNotificationsRead(): JsonResponse
    {
        $now = now();

        if (Schema::hasTable('orders') && Schema::hasColumn('orders', 'admin_notified_at')) {
            $orderUpdatePayload = [
                'admin_notified_at' => $now,
            ];

            if (Schema::hasColumn('orders', 'updated_at')) {
                $orderUpdatePayload['updated_at'] = $now;
            }

            Order::query()
                ->whereNull('admin_notified_at')
                ->update($orderUpdatePayload);
        }

        if (Schema::hasTable('cashier_attendances') && Schema::hasColumn('cashier_attendances', 'admin_notified_at')) {
            $attendanceUpdatePayload = [
                'admin_notified_at' => $now,
            ];

            if (Schema::hasColumn('cashier_attendances', 'updated_at')) {
                $attendanceUpdatePayload['updated_at'] = $now;
            }

            CashierAttendance::query()
                ->whereNull('admin_notified_at')
                ->update($attendanceUpdatePayload);
        }

        return response()->json([
            'ok' => true,
        ]);
    }

    private function resolveRouteFromSearch(string $search): ?array
    {
        $normalized = Str::of($search)->lower()->squish()->value();

        if ($normalized === '') {
            return null;
        }

        $keywordMap = [
            'admin.index' => ['dashboard', 'home', 'overview', 'cashflow', 'money in', 'money out', 'finance'],
            'admin.inventory.index' => ['inventory', 'inventory detail', 'income', 'outgoing', 'expense', 'ledger'],
            'admin.products.index' => ['product', 'products', 'item', 'items', 'menu'],
            'admin.categories.index' => ['category', 'categories'],
            'admin.users.index' => ['user', 'users', 'member', 'members', 'staff', 'team', 'admin', 'cashier'],
            'admin.attendance.index' => ['attendance', 'check in', 'check-in', 'cashier attendance'],
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

    private function formatUserDisplayName(?User $user): string
    {
        if (! $user) {
            return 'Cashier';
        }

        $fullName = trim((string) ($user->first_name ?? '') . ' ' . (string) ($user->last_name ?? ''));

        if ($fullName !== '') {
            return $fullName;
        }

        $fallbackName = trim((string) ($user->name ?? ''));

        return $fallbackName !== '' ? $fallbackName : 'Cashier';
    }
}
