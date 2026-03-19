@extends('layouts.app')

@section('content')
    @php
        $currentUser = auth()->user();
        $displayName = trim((string) ($currentUser->first_name ?? '') . ' ' . (string) ($currentUser->last_name ?? ''));
        $displayName = $displayName !== '' ? $displayName : (string) $currentUser->name;

        $exportQuery = request()->query();
        $reportPageUrl = route('admin.reports', $exportQuery);
        $excelExportUrl = route('admin.reports.export.excel', $exportQuery);
        $pdfExportUrl = route('admin.reports.export.pdf', $exportQuery);

        $topItemMaxQty = (int) ($topItems->max('qty_sold') ?? 0);
        $cashierMaxRevenue = (float) ($cashierBreakdown->max('revenue') ?? 0);
        $categoryMaxRevenue = (float) ($categoryBreakdown->max('revenue') ?? 0);
        $recentOrdersItemsTotal = (int) ($recentOrders->sum('items_count') ?? 0);

        $presets = [
            'last7' => 'Last 7 Days',
            'today' => 'Today',
            'yesterday' => 'Yesterday',
            'last30' => 'Last 30 Days',
            'this_month' => 'This Month',
            'custom' => 'Custom Range',
        ];

        $activeFilterCount = collect([
            $selectedPreset !== 'last7',
            filled(request('start_date')),
            filled(request('end_date')),
            $selectedPayment !== 'all',
            $selectedStatus !== 'all',
            filled($selectedCashier),
        ])
            ->filter()
            ->count();
        $isFilterOpen = $activeFilterCount > 0;

        $reportDateHeading = now()->format('l, F jS Y');
    @endphp

    <div class="anim-enter-up min-h-screen w-full overflow-hidden bg-[#f8f8f8] lg:overflow-visible">
        <div class="grid min-h-screen grid-cols-1 lg:grid-cols-12">
            @include('admin.sidebar.sidebar', ['activeAdminMenu' => 'reports'])

            <main
                class="anim-enter-right bg-[#f8f8f8] px-4 pb-8 pt-20 sm:px-6 sm:pt-20 lg:col-span-9 lg:px-8 lg:pt-8 xl:col-span-10">
                <header class="p-5 sm:p-6">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-[0.12em] text-[#8f5f3e]">Sales Report</p>
                            <h1 class="mt-1 text-3xl font-black text-[#2f241f]">Performance Dashboard</h1>
                            <p class="mt-2 text-sm text-slate-500">{{ $reportDateHeading }} - {{ $rangeLabel }}
                                ({{ $startDate }} to {{ $endDate }})</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <button type="button" data-report-filter-toggle
                                aria-expanded="{{ $isFilterOpen ? 'true' : 'false' }}"
                                class="inline-flex items-center gap-2 rounded-xl border border-[#eadfd7] bg-white px-3.5 py-2 text-sm font-semibold text-[#5f4b40] transition hover:bg-[#fff9f4]">
                                {{ $isFilterOpen ? 'Hide Filter' : 'Show Filter' }}
                            </button>
                            <a href="{{ $excelExportUrl }}"
                                class="inline-flex items-center gap-2 rounded-xl border border-[#f1ddce] bg-[#fff4ec] px-3.5 py-2 text-sm font-semibold text-[#8f5f3e] transition hover:bg-[#fff1e8]">
                                CSV
                            </a>
                            <a href="{{ $pdfExportUrl }}" target="_blank" rel="noopener"
                                class="inline-flex items-center gap-2 rounded-xl border border-[#eadfd7] bg-white px-3.5 py-2 text-sm font-semibold text-[#5f4b40] transition hover:bg-[#fff9f4]">
                                PDF
                            </a>
                            <button type="button" data-report-print
                                class="inline-flex items-center gap-2 rounded-xl border border-[#eadfd7] bg-white px-3.5 py-2 text-sm font-semibold text-[#5f4b40] transition hover:bg-[#fff9f4]">
                                Print
                            </button>
                        </div>
                    </div>

                    <form method="GET" action="{{ route('admin.reports') }}" data-report-filter-panel
                        @class([
                            'mt-5 space-y-4 rounded-3xl bg-white p-5 shadow-sm ring-1 ring-black/5',
                            'hidden' => !$isFilterOpen,
                        ])>
                        <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-6">
                            <label class="space-y-1">
                                <span class="text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Preset</span>
                                <select name="preset"
                                    class="w-full rounded-2xl border border-[#ebded5] bg-white px-3 py-2.5 text-sm text-[#2f241f] outline-none transition focus:border-[#f4a06b] focus:ring-2 focus:ring-[#f4a06b]/20">
                                    @foreach ($presets as $presetValue => $presetLabel)
                                        <option value="{{ $presetValue }}"
                                            {{ $selectedPreset === $presetValue ? 'selected' : '' }}>
                                            {{ $presetLabel }}
                                        </option>
                                    @endforeach
                                </select>
                            </label>

                            <label class="space-y-1">
                                <span class="text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Start
                                    Date</span>
                                <input type="date" name="start_date" value="{{ $startDate }}"
                                    class="w-full rounded-2xl border border-[#ebded5] bg-white px-3 py-2.5 text-sm text-[#2f241f] outline-none transition focus:border-[#f4a06b] focus:ring-2 focus:ring-[#f4a06b]/20">
                            </label>

                            <label class="space-y-1">
                                <span class="text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">End
                                    Date</span>
                                <input type="date" name="end_date" value="{{ $endDate }}"
                                    class="w-full rounded-2xl border border-[#ebded5] bg-white px-3 py-2.5 text-sm text-[#2f241f] outline-none transition focus:border-[#f4a06b] focus:ring-2 focus:ring-[#f4a06b]/20">
                            </label>

                            <label class="space-y-1">
                                <span
                                    class="text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Payment</span>
                                <select name="payment"
                                    class="w-full rounded-2xl border border-[#ebded5] bg-white px-3 py-2.5 text-sm text-[#2f241f] outline-none transition focus:border-[#f4a06b] focus:ring-2 focus:ring-[#f4a06b]/20">
                                    <option value="all" {{ $selectedPayment === 'all' ? 'selected' : '' }}>All Payment
                                    </option>
                                    @foreach ($paymentOptions as $paymentOption)
                                        <option value="{{ $paymentOption }}"
                                            {{ $selectedPayment === $paymentOption ? 'selected' : '' }}>
                                            {{ str($paymentOption)->replace('_', ' ')->headline() }}
                                        </option>
                                    @endforeach
                                </select>
                            </label>

                            <label class="space-y-1">
                                <span class="text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Status</span>
                                <select name="status"
                                    class="w-full rounded-2xl border border-[#ebded5] bg-white px-3 py-2.5 text-sm text-[#2f241f] outline-none transition focus:border-[#f4a06b] focus:ring-2 focus:ring-[#f4a06b]/20">
                                    <option value="all" {{ $selectedStatus === 'all' ? 'selected' : '' }}>All Status
                                    </option>
                                    @foreach ($statusOptions as $statusOption)
                                        <option value="{{ $statusOption }}"
                                            {{ $selectedStatus === $statusOption ? 'selected' : '' }}>
                                            {{ str($statusOption)->replace('_', ' ')->headline() }}
                                        </option>
                                    @endforeach
                                </select>
                            </label>

                            <label class="space-y-1">
                                <span
                                    class="text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Cashier</span>
                                <select name="cashier_id"
                                    class="w-full rounded-2xl border border-[#ebded5] bg-white px-3 py-2.5 text-sm text-[#2f241f] outline-none transition focus:border-[#f4a06b] focus:ring-2 focus:ring-[#f4a06b]/20">
                                    <option value="">All Cashiers</option>
                                    @foreach ($cashierOptions as $cashier)
                                        <option value="{{ $cashier->id }}"
                                            {{ (int) $selectedCashier === (int) $cashier->id ? 'selected' : '' }}>
                                            {{ $cashier->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </label>
                        </div>

                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div class="flex flex-wrap items-center gap-2">
                                <button type="submit"
                                    class="inline-flex items-center gap-2 rounded-xl bg-[#2f241f] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#3c2f29]">
                                    Apply Filters
                                </button>
                                <a href="{{ route('admin.reports') }}"
                                    class="inline-flex items-center gap-2 rounded-xl border border-[#ebded5] bg-white px-4 py-2.5 text-sm font-semibold text-[#5f4b40] transition hover:bg-[#fff6f0]">
                                    Reset
                                </a>
                            </div>
                            <div
                                class="inline-flex items-center rounded-full border border-[#f1ddce] bg-[#fff7f1] px-3 py-1 text-xs font-semibold uppercase tracking-widest text-[#b16231]">
                                Active Filters: {{ $activeFilterCount }}
                            </div>
                        </div>
                    </form>
                </header>

                <section class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-12">
                    <div class="grid grid-cols-1 gap-4 sm:auto-rows-fr sm:grid-cols-2 xl:col-span-8">
                        <article
                            class="anim-pop h-full sm:min-h-50 rounded-2xl border border-[#ebded5] bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                            <div class="flex items-start justify-between gap-3">
                                <div
                                    class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-[#fff5ec] text-[#b16231]">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 6v12m3.75-9H10.5a2.25 2.25 0 0 0 0 4.5h3a2.25 2.25 0 0 1 0 4.5H8.25" />
                                    </svg>
                                </div>
                                <span
                                    class="rounded-full {{ $revenueGrowth['isPositive'] ? 'bg-emerald-100 text-emerald-700' : 'bg-[#ffe3e3] text-[#9e1f1f]' }} px-2.5 py-1 text-xs font-bold">
                                    {{ $revenueGrowth['text'] }}
                                </span>
                            </div>
                            <p class="mt-3 text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Total Sales</p>
                            <h3 class="mt-2 text-3xl font-black text-[#2f241f]" data-counter-value="{{ $revenue }}"
                                data-counter-type="currency" data-counter-decimals="2">$0.00</h3>
                            <p class="mt-1 text-xs text-slate-500">Revenue compared to previous range</p>
                        </article>

                        <article
                            class="anim-pop h-full sm:min-h-50 rounded-2xl border border-[#ebded5] bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                            <div class="flex items-start justify-between gap-3">
                                <div
                                    class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-[#edf6ff] text-[#3d75b8]">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M3 3v18h18M7.5 15.75 11.25 12 14.25 15l5.25-6" />
                                    </svg>
                                </div>
                                <span
                                    class="rounded-full {{ $ordersGrowth['isPositive'] ? 'bg-emerald-100 text-emerald-700' : 'bg-[#ffe3e3] text-[#9e1f1f]' }} px-2.5 py-1 text-xs font-bold">
                                    {{ $ordersGrowth['text'] }}
                                </span>
                            </div>
                            <p class="mt-3 text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Total Orders
                            </p>
                            <h3 class="mt-2 text-3xl font-black text-[#2f241f]" data-counter-value="{{ $ordersCount }}"
                                data-counter-type="number">0</h3>
                            <p class="mt-1 text-xs text-slate-500">Orders in selected range</p>
                        </article>

                        <article
                            class="anim-pop h-full sm:min-h-50 rounded-2xl border border-[#ebded5] bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                            <div class="flex items-start justify-between gap-3">
                                <div
                                    class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-[#f4f2ff] text-[#6b5caa]">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M20.25 8.511c.884.284 1.5 1.11 1.5 2.04v7.2a2.25 2.25 0 0 1-2.25 2.25H4.5a2.25 2.25 0 0 1-2.25-2.25v-7.2c0-.93.616-1.756 1.5-2.04" />
                                    </svg>
                                </div>
                                <span
                                    class="rounded-full {{ $itemsGrowth['isPositive'] ? 'bg-emerald-100 text-emerald-700' : 'bg-[#ffe3e3] text-[#9e1f1f]' }} px-2.5 py-1 text-xs font-bold">
                                    {{ $itemsGrowth['text'] }}
                                </span>
                            </div>
                            <p class="mt-3 text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Total Products
                                Sold</p>
                            <h3 class="mt-2 text-3xl font-black text-[#2f241f]" data-counter-value="{{ $itemsSold }}"
                                data-counter-type="number">0</h3>
                            <p class="mt-1 text-xs text-slate-500">Units sold across all orders</p>
                        </article>

                        <article
                            class="anim-pop h-full sm:min-h-50 rounded-2xl border border-[#ebded5] bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                            <div class="flex items-start justify-between gap-3">
                                <div
                                    class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-[#ebfaf1] text-[#2e8f5e]">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v18m9-9H3" />
                                    </svg>
                                </div>
                                <span
                                    class="rounded-full bg-[#eef5ff] px-2.5 py-1 text-xs font-bold text-[#3f79ba]">{{ number_format((float) $avgItemsPerOrder, 2) }}
                                    items / order</span>
                            </div>
                            <p class="mt-3 text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Average Order
                                Value</p>
                            <h3 class="mt-2 text-3xl font-black text-[#2f241f]" data-counter-value="{{ $averageOrder }}"
                                data-counter-type="currency" data-counter-decimals="2">$0.00</h3>
                            <p class="mt-1 text-xs text-slate-500">Gross sales
                                ${{ number_format((float) $grossSales, 2) }} - Discount
                                {{ number_format((float) $discountRate, 2) }}%</p>
                        </article>
                    </div>

                    <aside
                        class="anim-enter-up h-full xl:min-h-114 rounded-3xl bg-[#fffdf9] p-5 shadow-sm ring-1 ring-black/5 xl:col-span-4">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <h3 class="text-3xl font-black text-[#2f241f]">Product Statistic</h3>
                                <p class="mt-1 text-sm text-slate-500">Track your product sales contribution</p>
                            </div>
                            <span
                                class="rounded-full bg-[#fff2e7] px-2.5 py-1 text-xs font-semibold text-[#be6f3c]">Today</span>
                        </div>

                        <div class="dashboard-chart-wrap mt-4" style="height: 280px;">
                            <canvas id="adminReportsCategoryChart"></canvas>
                        </div>

                        <div class="mt-4 space-y-2 text-sm">
                            @forelse ($categoryBreakdown->take(4) as $category)
                                @php
                                    $ratio =
                                        $categoryMaxRevenue > 0
                                            ? ((float) $category->revenue / $categoryMaxRevenue) * 100
                                            : 0;
                                @endphp
                                <div
                                    class="flex items-center justify-between rounded-2xl border border-[#f0e3da] bg-white px-4 py-3">
                                    <span
                                        class="truncate pr-2 font-semibold text-[#2f241f]">{{ $category->category_name }}</span>
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="text-slate-600">${{ number_format((float) $category->revenue, 0) }}</span>
                                        <span
                                            class="rounded-full bg-[#f4a06b] px-2 py-0.5 text-xs font-bold text-[#2f241f]">{{ number_format($ratio, 0) }}%</span>
                                    </div>
                                </div>
                            @empty
                                <p class="rounded-2xl border border-[#f0e3da] bg-white px-4 py-3 text-slate-500">No
                                    category data available.</p>
                            @endforelse
                        </div>
                    </aside>
                </section>

                <section class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-12">
                    <article
                        class="flex min-h-130 flex-col rounded-3xl border border-[#eadfd7] bg-white p-6 shadow-sm xl:col-span-8">
                        <div class="mb-4 flex items-center justify-between gap-2">
                            <div>
                                <h3 class="text-2xl font-black text-[#2f241f]">Customer Habits</h3>
                                <p class="text-sm text-slate-500">Track your customer buying trends</p>
                            </div>
                            <span
                                class="rounded-full bg-[#fff2e7] px-3 py-1 text-xs font-semibold text-[#be6f3c]">{{ $selectedPreset === 'custom' ? 'Custom' : 'This period' }}</span>
                        </div>
                        <div class="dashboard-chart-wrap flex-1" style="min-height: 360px;">
                            <canvas id="adminReportsTrendChart"></canvas>
                        </div>
                    </article>

                    <div class="grid grid-cols-1 gap-6 xl:col-span-4">
                        <article class="rounded-3xl border border-[#eadfd7] bg-white p-5 shadow-sm">
                            <div class="mb-4 flex items-center justify-between gap-2">
                                <h3 class="text-xl font-black text-[#2f241f]">Payment Mix</h3>
                                <span
                                    class="rounded-full bg-[#fff2e7] px-2.5 py-1 text-xs font-semibold text-[#be6f3c]">{{ number_format((int) $ordersCount) }}
                                    orders</span>
                            </div>
                            <div class="dashboard-chart-wrap dashboard-chart-wrap--compact">
                                <canvas id="adminReportsPaymentChart"></canvas>
                            </div>
                        </article>

                    </div>
                </section>

                <section class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-12">
                    <article id="recent-orders-section"
                        class="flex h-140 flex-col rounded-3xl border border-[#eadfd7] bg-white p-6 shadow-sm xl:col-span-8">
                        <div class="mb-4 flex items-center justify-between gap-2">
                            <h3 class="text-2xl font-black text-[#2f241f]">Recent Orders</h3>
                            <a href="{{ $reportPageUrl }}"
                                class="rounded-full bg-[#fff2e7] px-3 py-1 text-xs font-semibold text-[#be6f3c]">Refresh
                                View</a>
                        </div>

                        <div class="grid min-h-0 flex-1 grid-cols-1 gap-4 lg:grid-cols-12">
                            <div class="min-h-0 overflow-auto rounded-2xl border border-slate-100 lg:col-span-12">
                                <table class="w-full min-w-[920px] text-left">
                                    <thead>
                                        <tr class="border-b border-slate-200 text-sm text-gray-500">
                                            <th class="bg-white px-3 py-3 font-semibold">Order</th>
                                            <th class="bg-white px-3 py-3 font-semibold">Date</th>
                                            <th class="bg-white px-3 py-3 font-semibold">Cashier</th>
                                            <th class="bg-white px-3 py-3 font-semibold">Payment</th>
                                            <th class="bg-white px-3 py-3 font-semibold">Status</th>
                                            <th class="bg-white px-3 py-3 text-center font-semibold">Items</th>
                                            <th class="bg-white px-3 py-3 text-right font-semibold">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-sm">
                                        @forelse ($recentOrders as $order)
                                            @php
                                                $orderDateValue = $order->{$reportDateColumn} ?? $order->created_at;
                                                $orderNumber = (string) ($order->order_number ?? '-');
                                                $shortOrderNumber =
                                                    strlen($orderNumber) > 18
                                                        ? substr($orderNumber, 0, 8) . '...' . substr($orderNumber, -6)
                                                        : $orderNumber;
                                                $formattedOrderDate =
                                                    $orderDateValue instanceof \Carbon\Carbon
                                                        ? $orderDateValue->format('M d, Y H:i')
                                                        : ($orderDateValue
                                                            ? \Carbon\Carbon::parse((string) $orderDateValue)->format(
                                                                'M d, Y H:i',
                                                            )
                                                            : '-');
                                                $orderItemNames = $order->items
                                                    ->pluck('product_name')
                                                    ->filter()
                                                    ->values();
                                                $itemPreview = $orderItemNames->take(2)->implode(', ');
                                                if ($orderItemNames->count() > 2) {
                                                    $itemPreview .= ' +' . ($orderItemNames->count() - 2) . ' more';
                                                }
                                                if ($itemPreview === '') {
                                                    $itemPreview = 'No item name';
                                                }
                                            @endphp
                                            <tr class="border-b border-slate-100 last:border-b-0">
                                                <td class="px-3 py-4 font-semibold text-[#2f241f]">
                                                    <p title="{{ $orderNumber }}">{{ $shortOrderNumber }}</p>
                                                    <p
                                                        class="mt-0.5 max-w-[14rem] truncate text-xs font-medium text-slate-500">
                                                        {{ $itemPreview }}
                                                    </p>
                                                </td>
                                                <td class="px-3 text-slate-600">{{ $formattedOrderDate }}</td>
                                                <td class="px-3 text-slate-600">
                                                    {{ $order->cashier?->name ?? 'Unknown Cashier' }}
                                                </td>
                                                <td class="px-3 text-slate-600">
                                                    {{ str((string) ($order->payment_method ?? 'unknown'))->replace('_', ' ')->headline() }}
                                                </td>
                                                <td class="px-3">
                                                    <span
                                                        class="rounded-full bg-[#fff2e7] px-2.5 py-1 text-xs font-semibold text-[#b16231]">
                                                        {{ str((string) ($order->status ?? 'completed'))->replace('_', ' ')->headline() }}
                                                    </span>
                                                </td>
                                                <td class="px-3 text-center font-semibold text-slate-700">
                                                    {{ number_format((int) ($order->items_count ?? 0)) }}
                                                </td>
                                                <td class="px-3 text-right font-semibold text-[#2f241f]">
                                                    ${{ number_format((float) ($order->total ?? 0), 2) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="py-10 text-center text-sm text-slate-500">
                                                    No orders found for the selected filters.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </article>

                    <div class="grid grid-cols-1 gap-6 xl:col-span-4">
                        <article class="rounded-3xl border border-[#eadfd7] bg-white p-5 shadow-sm">
                            <h3 class="mb-4 text-xl font-black text-[#2f241f]">Order Status</h3>
                            <div class="dashboard-chart-wrap dashboard-chart-wrap--compact">
                                <canvas id="adminReportsStatusChart"></canvas>
                            </div>
                            <div class="mt-4 space-y-2 text-sm">
                                @forelse ($statusBreakdown->take(4) as $status)
                                    <div class="flex items-center justify-between rounded-xl bg-[#f8fafc] px-3 py-2">
                                        <span
                                            class="font-semibold text-slate-700">{{ str((string) ($status->status_name ?? 'completed'))->replace('_', ' ')->headline() }}</span>
                                        <span
                                            class="text-slate-600">{{ number_format((int) ($status->orders_count ?? 0)) }}
                                            orders</span>
                                    </div>
                                @empty
                                    <p class="text-slate-500">No status records.</p>
                                @endforelse
                            </div>
                        </article>

                        <article class="rounded-3xl border border-[#eadfd7] bg-white p-5 shadow-sm">
                            <h3 class="mb-4 text-xl font-black text-[#2f241f]">Top Selling Items</h3>
                            <div class="dashboard-chart-wrap dashboard-chart-wrap--compact">
                                <canvas id="adminReportsTopItemsChart"></canvas>
                            </div>
                            <div class="mt-4 space-y-3 text-sm">
                                @forelse ($topItems->take(3) as $item)
                                    @php
                                        $progress =
                                            $topItemMaxQty > 0 ? ((int) $item->qty_sold / $topItemMaxQty) * 100 : 0;
                                    @endphp
                                    <div>
                                        <div class="mb-1 flex items-center justify-between gap-2">
                                            <span
                                                class="truncate pr-2 font-semibold text-slate-700">{{ $item->product_name }}</span>
                                            <span class="text-slate-600">{{ number_format((int) $item->qty_sold) }}</span>
                                        </div>
                                        <div class="h-2 rounded-full bg-slate-100">
                                            <div class="dashboard-progress-bar h-2 rounded-full bg-[#2f241f]"
                                                style="--progress-width: {{ round($progress, 2) }}%;"></div>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-slate-500">No item sales in this range.</p>
                                @endforelse
                            </div>
                        </article>

                        <article class="rounded-3xl border border-[#eadfd7] bg-white p-5 shadow-sm">
                            <h3 class="mb-4 text-xl font-black text-[#2f241f]">Cashier Contribution</h3>
                            <div class="space-y-3 text-sm">
                                @forelse ($cashierBreakdown->take(5) as $cashierRow)
                                    @php
                                        $progress =
                                            $cashierMaxRevenue > 0
                                                ? ((float) $cashierRow->revenue / $cashierMaxRevenue) * 100
                                                : 0;
                                    @endphp
                                    <div>
                                        <div class="mb-1 flex items-center justify-between gap-2">
                                            <span
                                                class="truncate pr-2 font-semibold text-slate-700">{{ $cashierRow->cashier_name }}</span>
                                            <span
                                                class="text-slate-600">${{ number_format((float) $cashierRow->revenue, 0) }}</span>
                                        </div>
                                        <div class="h-2 rounded-full bg-slate-100">
                                            <div class="dashboard-progress-bar h-2 rounded-full bg-[#f4a06b]"
                                                style="--progress-width: {{ round($progress, 2) }}%;"></div>
                                        </div>
                                        <p class="mt-1 text-xs text-slate-500">
                                            {{ number_format((int) $cashierRow->orders_count) }} orders</p>
                                    </div>
                                @empty
                                    <p class="text-slate-500">No cashier contribution data.</p>
                                @endforelse
                            </div>
                        </article>
                    </div>
                </section>
            </main>
        </div>
    </div>

    <script id="admin-report-payload" type="application/json">@json($charts)</script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.8/dist/chart.umd.min.js"></script>
@endsection
