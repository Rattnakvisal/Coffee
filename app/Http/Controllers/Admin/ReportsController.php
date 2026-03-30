<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportsController extends Controller
{
    public function reports(Request $request): View
    {
        $dateColumn = $this->orderDateColumn();
        [$start, $end, $rangeLabel, $startDate, $endDate, $hasCustomRange, $selectedPreset] = $this->resolveReportDateRange($request);
        $filters = $this->resolveReportFilters($request);
        $cashierColumn = $this->orderCashierColumn();

        $ordersQuery = Order::query()->whereBetween($dateColumn, [$start, $end]);
        $this->applyReportFilters($ordersQuery, $filters, $cashierColumn);

        $ordersCount = (int) (clone $ordersQuery)->count();
        $revenue = (float) (clone $ordersQuery)->sum('total');
        $subtotal = Schema::hasColumn('orders', 'subtotal')
            ? (float) (clone $ordersQuery)->sum('subtotal')
            : 0.0;
        $discountTotal = Schema::hasColumn('orders', 'discount')
            ? (float) (clone $ordersQuery)->sum('discount')
            : 0.0;
        $itemsSold = (int) OrderItem::query()
            ->whereHas('order', function (Builder $query) use ($dateColumn, $start, $end, $filters, $cashierColumn): void {
                $query->whereBetween($dateColumn, [$start, $end]);
                $this->applyReportFilters($query, $filters, $cashierColumn);
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
        $this->applyReportFilters($previousOrdersQuery, $filters, $cashierColumn);
        $previousOrders = (int) (clone $previousOrdersQuery)->count();
        $previousRevenue = (float) (clone $previousOrdersQuery)->sum('total');
        $previousItems = (int) OrderItem::query()
            ->whereHas('order', function (Builder $query) use ($dateColumn, $previousStart, $previousEnd, $filters, $cashierColumn): void {
                $query->whereBetween($dateColumn, [$previousStart, $previousEnd]);
                $this->applyReportFilters($query, $filters, $cashierColumn);
            })
            ->sum('qty');

        if (Schema::hasColumn('orders', 'payment_method')) {
            $paymentBreakdownQuery = Order::query()->whereBetween($dateColumn, [$start, $end]);
            $this->applyReportFilters($paymentBreakdownQuery, $filters, $cashierColumn);

            $paymentBreakdown = $paymentBreakdownQuery
                ->selectRaw("COALESCE(payment_method, 'unknown') as payment_method, COUNT(*) as orders_count, SUM(total) as revenue")
                ->groupBy('payment_method')
                ->orderByDesc('revenue')
                ->get();
        } else {
            $paymentBreakdown = collect([
                (object) [
                    'payment_method' => 'unknown',
                    'orders_count' => $ordersCount,
                    'revenue' => $revenue,
                ],
            ]);
        }

        if (Schema::hasColumn('orders', 'status')) {
            $statusBreakdownQuery = Order::query()->whereBetween($dateColumn, [$start, $end]);
            $this->applyReportFilters($statusBreakdownQuery, $filters, $cashierColumn);

            $statusBreakdown = $statusBreakdownQuery
                ->selectRaw("COALESCE(status, 'completed') as status_name, COUNT(*) as orders_count, SUM(total) as revenue")
                ->groupBy('status_name')
                ->orderByDesc('orders_count')
                ->get();
        } else {
            $statusBreakdown = collect([
                (object) [
                    'status_name' => 'completed',
                    'orders_count' => $ordersCount,
                    'revenue' => $revenue,
                ],
            ]);
        }

        $topItems = OrderItem::query()
            ->selectRaw('product_name, SUM(qty) as qty_sold, SUM(line_total) as revenue')
            ->whereHas('order', function (Builder $query) use ($dateColumn, $start, $end, $filters, $cashierColumn): void {
                $query->whereBetween($dateColumn, [$start, $end]);
                $this->applyReportFilters($query, $filters, $cashierColumn);
            })
            ->groupBy('product_name')
            ->orderByDesc('qty_sold')
            ->limit(8)
            ->get();

        $categoryBreakdown = OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->leftJoin('products', 'order_items.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->whereBetween('orders.' . $dateColumn, [$start, $end]);
        $this->applyReportFilters($categoryBreakdown, $filters, $cashierColumn, 'orders');
        $categoryBreakdown = $categoryBreakdown
            ->selectRaw("COALESCE(categories.name, 'Uncategorized') as category_name, SUM(order_items.qty) as qty_sold, SUM(order_items.line_total) as revenue")
            ->groupBy('category_name')
            ->orderByDesc('revenue')
            ->limit(8)
            ->get();

        $recentOrders = Order::query()
            ->with('cashier:id,name')
            ->with('items:id,order_id,product_name,qty')
            ->withCount('items')
            ->whereBetween($dateColumn, [$start, $end]);
        $this->applyReportFilters($recentOrders, $filters, $cashierColumn);
        $recentOrders = $recentOrders
            ->orderByDesc($dateColumn)
            ->limit(10)
            ->get();

        $charts = [
            'trend' => $this->buildAdminTrend($dateColumn, $start, $end, $hasCustomRange, $filters, $cashierColumn),
            'payments' => [
                'labels' => $paymentBreakdown
                    ->map(fn($row): string => strtoupper((string) ($row->payment_method ?? 'UNKNOWN')))
                    ->values()
                    ->all(),
                'revenue' => $paymentBreakdown
                    ->map(fn($row): float => round((float) ($row->revenue ?? 0), 2))
                    ->values()
                    ->all(),
            ],
            'topItems' => [
                'labels' => $topItems
                    ->map(fn($row): string => (string) ($row->product_name ?? 'Item'))
                    ->values()
                    ->all(),
                'qty' => $topItems
                    ->map(fn($row): int => (int) ($row->qty_sold ?? 0))
                    ->values()
                    ->all(),
                'revenue' => $topItems
                    ->map(fn($row): float => round((float) ($row->revenue ?? 0), 2))
                    ->values()
                    ->all(),
            ],
            'statuses' => [
                'labels' => $statusBreakdown
                    ->map(fn($row): string => str((string) ($row->status_name ?? 'completed'))->headline()->toString())
                    ->values()
                    ->all(),
                'orders' => $statusBreakdown
                    ->map(fn($row): int => (int) ($row->orders_count ?? 0))
                    ->values()
                    ->all(),
                'revenue' => $statusBreakdown
                    ->map(fn($row): float => round((float) ($row->revenue ?? 0), 2))
                    ->values()
                    ->all(),
            ],
            'categories' => [
                'labels' => $categoryBreakdown
                    ->map(fn($row): string => (string) ($row->category_name ?? 'Uncategorized'))
                    ->values()
                    ->all(),
                'qty' => $categoryBreakdown
                    ->map(fn($row): int => (int) ($row->qty_sold ?? 0))
                    ->values()
                    ->all(),
                'revenue' => $categoryBreakdown
                    ->map(fn($row): float => round((float) ($row->revenue ?? 0), 2))
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

        return view('admin.reports.reports', [
            'rangeLabel' => $rangeLabel,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'selectedPreset' => $selectedPreset,
            'selectedPayment' => $filters['payment'],
            'selectedStatus' => $filters['status'],
            'selectedCashier' => $filters['cashier_id'],
            'paymentOptions' => $this->reportPaymentOptions(),
            'statusOptions' => $this->reportStatusOptions(),
            'cashierOptions' => $this->reportCashierOptions($cashierColumn),
            'ordersCount' => $ordersCount,
            'revenue' => $revenue,
            'grossSales' => $grossSales,
            'discountTotal' => $discountTotal,
            'discountRate' => $discountRate,
            'itemsSold' => $itemsSold,
            'averageOrder' => $averageOrder,
            'avgItemsPerOrder' => $avgItemsPerOrder,
            'ordersGrowth' => $this->growthLabel($ordersCount, $previousOrders, 'vs previous range'),
            'revenueGrowth' => $this->growthLabel($revenue, $previousRevenue, 'vs previous range'),
            'itemsGrowth' => $this->growthLabel($itemsSold, $previousItems, 'vs previous range'),
            'paymentBreakdown' => $paymentBreakdown,
            'statusBreakdown' => $statusBreakdown,
            'topItems' => $topItems,
            'categoryBreakdown' => $categoryBreakdown,
            'recentOrders' => $recentOrders,
            'reportDateColumn' => $dateColumn,
            'charts' => $charts,
        ]);
    }

    public function exportReportsExcel(Request $request): StreamedResponse
    {
        $dateColumn = $this->orderDateColumn();
        [$start, $end, $rangeLabel, $startDate, $endDate] = $this->resolveReportDateRange($request);
        $filters = $this->resolveReportFilters($request);
        $cashierColumn = $this->orderCashierColumn();

        $ordersQuery = Order::query()
            ->with('cashier:id,name')
            ->withCount('items')
            ->whereBetween($dateColumn, [$start, $end]);
        $this->applyReportFilters($ordersQuery, $filters, $cashierColumn);

        $orders = $ordersQuery
            ->orderBy($dateColumn)
            ->get();

        $filename = sprintf('admin-reports-%s_to_%s.csv', $startDate, $endDate);

        return response()->streamDownload(
            function () use ($orders, $rangeLabel, $startDate, $endDate, $dateColumn): void {
                $output = fopen('php://output', 'wb');
                if ($output === false) {
                    return;
                }

                fwrite($output, "\xEF\xBB\xBF");

                fputcsv($output, ['Admin Reports Export']);
                fputcsv($output, ['Range', $rangeLabel]);
                fputcsv($output, ['Start Date', $startDate]);
                fputcsv($output, ['End Date', $endDate]);
                fputcsv($output, []);
                fputcsv($output, ['Date', 'Order Number', 'Cashier', 'Payment', 'Status', 'Items', 'Subtotal', 'Discount', 'Total']);

                foreach ($orders as $order) {
                    $dateValue = $order->{$dateColumn} ?? null;

                    if ($dateValue instanceof Carbon) {
                        $formattedDate = $dateValue->format('Y-m-d H:i');
                    } elseif ($dateValue !== null && $dateValue !== '') {
                        $formattedDate = Carbon::parse((string) $dateValue)->format('Y-m-d H:i');
                    } else {
                        $formattedDate = '-';
                    }

                    fputcsv($output, [
                        $formattedDate,
                        (string) ($order->order_number ?? '-'),
                        (string) ($order->cashier?->name ?? 'Unknown Cashier'),
                        (string) strtoupper((string) ($order->payment_method ?? 'unknown')),
                        (string) str((string) ($order->status ?? 'completed'))->headline(),
                        (int) ($order->items_count ?? 0),
                        number_format((float) ($order->subtotal ?? 0), 2, '.', ''),
                        number_format((float) ($order->discount ?? 0), 2, '.', ''),
                        number_format((float) ($order->total ?? 0), 2, '.', ''),
                    ]);
                }

                fclose($output);
            },
            $filename,
            [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Cache-Control' => 'no-store, no-cache',
            ],
        );
    }

    public function exportReportsPdf(Request $request): View
    {
        $dateColumn = $this->orderDateColumn();
        [$start, $end, $rangeLabel, $startDate, $endDate] = $this->resolveReportDateRange($request);
        $filters = $this->resolveReportFilters($request);
        $cashierColumn = $this->orderCashierColumn();

        $ordersQuery = Order::query()
            ->with('cashier:id,name')
            ->withCount('items')
            ->whereBetween($dateColumn, [$start, $end]);
        $this->applyReportFilters($ordersQuery, $filters, $cashierColumn);

        $orders = $ordersQuery
            ->orderByDesc($dateColumn)
            ->get();

        $ordersCount = (int) $orders->count();
        $revenue = (float) $orders->sum('total');
        $subtotal = (float) $orders->sum('subtotal');
        $discountTotal = (float) $orders->sum('discount');
        $itemsSold = (int) $orders->sum('items_count');

        return view('admin.reports.exports.pdf', [
            'rangeLabel' => $rangeLabel,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'ordersCount' => $ordersCount,
            'revenue' => $revenue,
            'subtotal' => $subtotal,
            'discountTotal' => $discountTotal,
            'itemsSold' => $itemsSold,
            'orders' => $orders,
            'reportDateColumn' => $dateColumn,
        ]);
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
     * @return array{payment: string, status: string, cashier_id: int|null}
     */
    private function resolveReportFilters(Request $request): array
    {
        $payment = Str::of((string) $request->query('payment', 'all'))
            ->lower()
            ->squish()
            ->value();
        $status = Str::of((string) $request->query('status', 'all'))
            ->lower()
            ->squish()
            ->value();
        $cashierId = (int) $request->query('cashier_id', 0);

        return [
            'payment' => $payment === '' ? 'all' : $payment,
            'status' => $status === '' ? 'all' : $status,
            'cashier_id' => $cashierId > 0 ? $cashierId : null,
        ];
    }

    /**
     * @param array{payment?: string, status?: string, cashier_id?: int|null} $filters
     */
    private function applyReportFilters(
        Builder $query,
        array $filters,
        ?string $cashierColumn = null,
        string $tablePrefix = 'orders',
    ): void {
        $payment = (string) ($filters['payment'] ?? 'all');
        $status = (string) ($filters['status'] ?? 'all');
        $cashierId = (int) ($filters['cashier_id'] ?? 0);

        if ($payment !== 'all' && Schema::hasColumn('orders', 'payment_method')) {
            $paymentColumn = $tablePrefix !== '' ? $tablePrefix . '.payment_method' : 'payment_method';
            $query->whereRaw("LOWER(COALESCE({$paymentColumn}, 'unknown')) = ?", [$payment]);
        }

        if ($status !== 'all' && Schema::hasColumn('orders', 'status')) {
            $statusColumn = $tablePrefix !== '' ? $tablePrefix . '.status' : 'status';
            $query->whereRaw("LOWER(COALESCE({$statusColumn}, 'completed')) = ?", [$status]);
        }

        if ($cashierId > 0 && $cashierColumn !== null && Schema::hasColumn('orders', $cashierColumn)) {
            $qualifiedCashierColumn = $tablePrefix !== ''
                ? $tablePrefix . '.' . $cashierColumn
                : $cashierColumn;

            $query->where($qualifiedCashierColumn, $cashierId);
        }
    }

    /**
     * @return array<int, string>
     */
    private function reportPaymentOptions(): array
    {
        if (! Schema::hasColumn('orders', 'payment_method')) {
            return [];
        }

        return Order::query()
            ->selectRaw("LOWER(COALESCE(payment_method, 'unknown')) as value")
            ->pluck('value')
            ->filter(fn($value): bool => trim((string) $value) !== '')
            ->map(fn($value): string => (string) $value)
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private function reportStatusOptions(): array
    {
        if (! Schema::hasColumn('orders', 'status')) {
            return [];
        }

        return Order::query()
            ->selectRaw("LOWER(COALESCE(status, 'completed')) as value")
            ->pluck('value')
            ->filter(fn($value): bool => trim((string) $value) !== '')
            ->map(fn($value): string => (string) $value)
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    private function reportCashierOptions(?string $cashierColumn): \Illuminate\Support\Collection
    {
        if ($cashierColumn === null || ! Schema::hasColumn('orders', $cashierColumn)) {
            return collect();
        }

        $cashierIds = Order::query()
            ->whereNotNull($cashierColumn)
            ->pluck($cashierColumn)
            ->map(fn($id): int => (int) $id)
            ->filter(fn(int $id): bool => $id > 0)
            ->unique()
            ->values();

        if ($cashierIds->isEmpty()) {
            return collect();
        }

        return User::query()
            ->whereIn('id', $cashierIds->all())
            ->orderBy('name')
            ->get(['id', 'name']);
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
    private function buildAdminTrend(
        string $dateColumn,
        Carbon $start,
        Carbon $end,
        bool $hasCustomRange,
        array $filters = [],
        ?string $cashierColumn = null,
    ): array {
        $buckets = [];
        $labels = [];

        /** @var Carbon $day */
        foreach (CarbonPeriod::create($start->copy()->startOfDay(), '1 day', $end->copy()->startOfDay()) as $day) {
            $key = $day->toDateString();
            $buckets[$key] = ['orders' => 0, 'revenue' => 0.0];
            $labels[$key] = $day->format('M j');
        }

        $orders = Order::query()
            ->whereBetween($dateColumn, [$start, $end]);
        $this->applyReportFilters($orders, $filters, $cashierColumn);
        $orders = $orders
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
