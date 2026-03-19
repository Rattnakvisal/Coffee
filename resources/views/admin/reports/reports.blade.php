@extends('layouts.app')

@section('content')
    @php
        $currentUser = auth()->user();
        $displayName = trim((string) ($currentUser->first_name ?? '') . ' ' . (string) ($currentUser->last_name ?? ''));
        $displayName = $displayName !== '' ? $displayName : (string) $currentUser->name;
        $initials = collect(explode(' ', $displayName))
            ->filter()
            ->map(fn(string $namePart): string => strtoupper(substr($namePart, 0, 1)))
            ->take(2)
            ->implode('');
        $avatarUrl = $currentUser->avatar_path ? asset('storage/' . $currentUser->avatar_path) : null;
        $exportQuery = request()->query();
        $reportPageUrl = route('admin.reports', $exportQuery);
        $excelExportUrl = route('admin.reports.export.excel', $exportQuery);
        $pdfExportUrl = route('admin.reports.export.pdf', $exportQuery);
        $topItemMaxQty = (int) ($topItems->max('qty_sold') ?? 0);
        $cashierMaxRevenue = (float) ($cashierBreakdown->max('revenue') ?? 0);
        $presets = [
            'last7' => 'Last 7 Days',
            'today' => 'Today',
            'yesterday' => 'Yesterday',
            'last30' => 'Last 30 Days',
            'this_month' => 'This Month',
            'custom' => 'Custom Range',
        ];
    @endphp

    <div class="anim-enter-up w-full min-h-screen overflow-hidden lg:overflow-visible bg-white/85">
        <div class="grid min-h-screen grid-cols-1 lg:grid-cols-12">
            @include('admin.sidebar.sidebar', ['activeAdminMenu' => 'reports'])

            <main
                class="anim-enter-right bg-[#f8f8f8] p-4 pt-20 sm:p-6 sm:pt-20 lg:col-span-9 lg:p-8 lg:pt-8 xl:col-span-10">
                <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p
                            class="inline-flex items-center gap-2 rounded-full bg-[#ffe7d5] px-3 py-1 text-xs font-semibold uppercase tracking-[0.12em] text-[#b16231]">
                            <span class="h-2 w-2 rounded-full bg-[#f4a06b]"></span>
                            Admin Reports
                        </p>
                        <h2 class="mt-3 text-3xl font-black text-[#2f241f]">Sales & Performance Reports</h2>
                        <p class="mt-1 text-sm text-gray-500">
                            {{ $rangeLabel }} ({{ $startDate }} to {{ $endDate }})
                        </p>
                    </div>
                </div>

                <section class="anim-enter-up anim-delay-100 rounded-3xl border border-[#eadfd7] bg-white p-5 shadow-sm">
                    <form method="GET" action="{{ route('admin.reports') }}" class="space-y-4">
                        <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-6">
                            <label class="space-y-1">
                                <span class="text-xs font-semibold uppercase tracking-[0.1em] text-slate-500">Preset</span>
                                <select name="preset"
                                    class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none transition focus:border-[#f4a06b] focus:ring-2 focus:ring-[#f4a06b]/20">
                                    @foreach ($presets as $presetValue => $presetLabel)
                                        <option value="{{ $presetValue }}"
                                            {{ $selectedPreset === $presetValue ? 'selected' : '' }}>
                                            {{ $presetLabel }}
                                        </option>
                                    @endforeach
                                </select>
                            </label>

                            <label class="space-y-1">
                                <span class="text-xs font-semibold uppercase tracking-[0.1em] text-slate-500">Start
                                    Date</span>
                                <input type="date" name="start_date" value="{{ $startDate }}"
                                    class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none transition focus:border-[#f4a06b] focus:ring-2 focus:ring-[#f4a06b]/20">
                            </label>

                            <label class="space-y-1">
                                <span class="text-xs font-semibold uppercase tracking-[0.1em] text-slate-500">End
                                    Date</span>
                                <input type="date" name="end_date" value="{{ $endDate }}"
                                    class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none transition focus:border-[#f4a06b] focus:ring-2 focus:ring-[#f4a06b]/20">
                            </label>

                            <label class="space-y-1">
                                <span class="text-xs font-semibold uppercase tracking-[0.1em] text-slate-500">Payment</span>
                                <select name="payment"
                                    class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none transition focus:border-[#f4a06b] focus:ring-2 focus:ring-[#f4a06b]/20">
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
                                <span class="text-xs font-semibold uppercase tracking-[0.1em] text-slate-500">Status</span>
                                <select name="status"
                                    class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none transition focus:border-[#f4a06b] focus:ring-2 focus:ring-[#f4a06b]/20">
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
                                <span class="text-xs font-semibold uppercase tracking-[0.1em] text-slate-500">Cashier</span>
                                <select name="cashier_id"
                                    class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none transition focus:border-[#f4a06b] focus:ring-2 focus:ring-[#f4a06b]/20">
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
                                    class="inline-flex items-center gap-2 rounded-xl bg-[#2f241f] px-4 py-2.5 text-sm font-semibold text-white transition hover:brightness-110">
                                    Apply Filters
                                </button>

                                <a href="{{ route('admin.reports') }}"
                                    class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                                    Reset
                                </a>
                            </div>

                            <a href="#recent-orders-section"
                                class="inline-flex items-center gap-2 rounded-xl border border-[#eadfd7] bg-[#fff8f3] px-4 py-2.5 text-sm font-semibold text-[#8f5f3e] transition hover:bg-[#fff1e8]">
                                Jump to Orders Table
                            </a>
                        </div>

                        <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-4">
                            <a href="{{ $reportPageUrl }}"
                                class="group rounded-2xl border border-slate-200 bg-slate-50 p-4 transition hover:-translate-y-0.5 hover:bg-slate-100 hover:shadow-sm">
                                <div class="mb-3 flex items-center justify-between">
                                    <span
                                        class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-white text-slate-700 ring-1 ring-slate-200">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M3.75 3.75h16.5v16.5H3.75V3.75Zm4.5 4.5h7.5m-7.5 3.75h7.5m-7.5 3.75h4.5" />
                                        </svg>
                                    </span>
                                    <span class="text-xs font-semibold text-slate-500 transition group-hover:text-slate-700">
                                        Open
                                    </span>
                                </div>
                                <p class="text-sm font-semibold text-slate-800">View Report Page</p>
                                <p class="mt-1 text-xs text-slate-500">Open this page with current filters.</p>
                            </a>

                            <a href="{{ $excelExportUrl }}"
                                class="group rounded-2xl border border-emerald-200 bg-emerald-50 p-4 transition hover:-translate-y-0.5 hover:bg-emerald-100 hover:shadow-sm">
                                <div class="mb-3 flex items-center justify-between">
                                    <span
                                        class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-white text-emerald-700 ring-1 ring-emerald-200">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M12 16.5V3.75m0 12.75 4.5-4.5m-4.5 4.5-4.5-4.5M4.5 18.75h15" />
                                        </svg>
                                    </span>
                                    <span
                                        class="text-xs font-semibold text-emerald-700/80 transition group-hover:text-emerald-800">
                                        Export
                                    </span>
                                </div>
                                <p class="text-sm font-semibold text-emerald-800">Download Excel (CSV)</p>
                                <p class="mt-1 text-xs text-emerald-700/80">Best for editing in Excel or Sheets.</p>
                            </a>

                            <a href="{{ $pdfExportUrl }}" target="_blank" rel="noopener"
                                class="group rounded-2xl border border-rose-200 bg-rose-50 p-4 transition hover:-translate-y-0.5 hover:bg-rose-100 hover:shadow-sm">
                                <div class="mb-3 flex items-center justify-between">
                                    <span
                                        class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-white text-rose-700 ring-1 ring-rose-200">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M9 13.5h6m-6 3h6m2.25-12H9.75A2.25 2.25 0 0 0 7.5 6.75v10.5A2.25 2.25 0 0 0 9.75 19.5h7.5A2.25 2.25 0 0 0 19.5 17.25V6.75A2.25 2.25 0 0 0 17.25 4.5Z" />
                                        </svg>
                                    </span>
                                    <span class="text-xs font-semibold text-rose-700/80 transition group-hover:text-rose-800">
                                        PDF
                                    </span>
                                </div>
                                <p class="text-sm font-semibold text-rose-800">Open PDF View</p>
                                <p class="mt-1 text-xs text-rose-700/80">Clean print layout with report summary.</p>
                            </a>

                            <button type="button" data-report-print
                                class="group rounded-2xl border border-[#eadfd7] bg-white p-4 text-left transition hover:-translate-y-0.5 hover:bg-[#fffaf6] hover:shadow-sm">
                                <div class="mb-3 flex items-center justify-between">
                                    <span
                                        class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-[#fff4ec] text-[#8f5f3e] ring-1 ring-[#f1e1d5]">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M6 9.75V4.5h12v5.25M6 16.5h12M6 12h12a2.25 2.25 0 0 1 2.25 2.25V18H3.75v-3.75A2.25 2.25 0 0 1 6 12Z" />
                                        </svg>
                                    </span>
                                    <span
                                        class="text-xs font-semibold text-[#8f5f3e]/80 transition group-hover:text-[#8f5f3e]">
                                        Print
                                    </span>
                                </div>
                                <p class="text-sm font-semibold text-[#2f241f]">Print This Page</p>
                                <p class="mt-1 text-xs text-slate-500">Quick print for this full dashboard view.</p>
                            </button>
                        </div>
                    </form>
                </section>

                <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    <section
                        class="anim-pop anim-delay-200 rounded-3xl border border-[#f0e3da] bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                        <div class="flex items-center justify-between gap-2">
                            <p class="text-sm font-semibold text-gray-500">Total Revenue</p>
                            <span
                                class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-[#fff3ea] text-[#bf6c3a]">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 6v12m3.75-9H10.5a2.25 2.25 0 0 0 0 4.5h3a2.25 2.25 0 0 1 0 4.5H8.25" />
                                </svg>
                            </span>
                        </div>
                        <h3 class="mt-3 text-3xl font-black text-[#2f241f]" data-counter-value="{{ $revenue }}"
                            data-counter-type="currency" data-counter-decimals="2">$0.00</h3>
                        <p
                            class="mt-2 text-xs font-semibold {{ $revenueGrowth['isPositive'] ? 'text-emerald-600' : 'text-rose-600' }}">
                            {{ $revenueGrowth['text'] }}
                        </p>
                    </section>

                    <section
                        class="anim-pop anim-delay-300 rounded-3xl border border-[#f0e3da] bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                        <div class="flex items-center justify-between gap-2">
                            <p class="text-sm font-semibold text-gray-500">Orders Count</p>
                            <span
                                class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-[#eef5ff] text-[#3f79ba]">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M3.75 6.75h16.5m-15 0 1.5 11.25A2.25 2.25 0 0 0 9 20.25h6a2.25 2.25 0 0 0 2.25-2.25l1.5-11.25M9.75 6.75v-1.5A2.25 2.25 0 0 1 12 3h0a2.25 2.25 0 0 1 2.25 2.25v1.5" />
                                </svg>
                            </span>
                        </div>
                        <h3 class="mt-3 text-3xl font-black text-[#2f241f]" data-counter-value="{{ $ordersCount }}"
                            data-counter-type="number">0</h3>
                        <p
                            class="mt-2 text-xs font-semibold {{ $ordersGrowth['isPositive'] ? 'text-emerald-600' : 'text-rose-600' }}">
                            {{ $ordersGrowth['text'] }}
                        </p>
                    </section>

                    <section
                        class="anim-pop anim-delay-400 rounded-3xl border border-[#f0e3da] bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                        <div class="flex items-center justify-between gap-2">
                            <p class="text-sm font-semibold text-gray-500">Items Sold</p>
                            <span
                                class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-[#f2f8ea] text-[#5f9925]">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M20.25 8.511c.884.284 1.5 1.11 1.5 2.04v7.2a2.25 2.25 0 0 1-2.25 2.25H4.5a2.25 2.25 0 0 1-2.25-2.25v-7.2c0-.93.616-1.756 1.5-2.04m16.5 0a2.25 2.25 0 0 0-.513-.95l-3.09-3.09a2.25 2.25 0 0 0-1.59-.66H9.943a2.25 2.25 0 0 0-1.59.66l-3.09 3.09a2.25 2.25 0 0 0-.513.95m16.5 0a2.25 2.25 0 0 1-2.122 1.489H4.872A2.25 2.25 0 0 1 2.75 8.511" />
                                </svg>
                            </span>
                        </div>
                        <h3 class="mt-3 text-3xl font-black text-[#2f241f]" data-counter-value="{{ $itemsSold }}"
                            data-counter-type="number">0</h3>
                        <p
                            class="mt-2 text-xs font-semibold {{ $itemsGrowth['isPositive'] ? 'text-emerald-600' : 'text-rose-600' }}">
                            {{ $itemsGrowth['text'] }}
                        </p>
                    </section>

                    <section
                        class="anim-pop anim-delay-300 rounded-3xl border border-[#f0e3da] bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                        <div class="flex items-center justify-between gap-2">
                            <p class="text-sm font-semibold text-gray-500">Gross Sales</p>
                            <span
                                class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-[#f4f2ff] text-[#6b5caa]">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M2.25 18 9 11.25l4.5 4.5L21.75 7.5" />
                                </svg>
                            </span>
                        </div>
                        <h3 class="mt-3 text-3xl font-black text-[#2f241f]" data-counter-value="{{ $grossSales }}"
                            data-counter-type="currency" data-counter-decimals="2">$0.00</h3>
                        <p class="mt-2 text-xs font-semibold text-slate-500">
                            Discount Rate {{ number_format((float) $discountRate, 2) }}%
                        </p>
                    </section>

                    <section
                        class="anim-pop anim-delay-400 rounded-3xl border border-[#f0e3da] bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                        <div class="flex items-center justify-between gap-2">
                            <p class="text-sm font-semibold text-gray-500">Average Order</p>
                            <span
                                class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-[#eaf7f4] text-[#2f8c6e]">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M10.5 6h9.75M10.5 12h9.75M10.5 18h9.75M3.75 6h.008v.008H3.75V6Zm0 6h.008v.008H3.75V12Zm0 6h.008v.008H3.75V18Z" />
                                </svg>
                            </span>
                        </div>
                        <h3 class="mt-3 text-3xl font-black text-[#2f241f]" data-counter-value="{{ $averageOrder }}"
                            data-counter-type="currency" data-counter-decimals="2">$0.00</h3>
                        <p class="mt-2 text-xs font-semibold text-slate-500">
                            {{ number_format((float) $avgItemsPerOrder, 2) }} items per order
                        </p>
                    </section>

                    <section
                        class="anim-pop anim-delay-500 rounded-3xl border border-[#f0e3da] bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                        <div class="flex items-center justify-between gap-2">
                            <p class="text-sm font-semibold text-gray-500">Active Cashiers</p>
                            <span
                                class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-[#fff7e9] text-[#c9861f]">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                </svg>
                            </span>
                        </div>
                        <h3 class="mt-3 text-3xl font-black text-[#2f241f]" data-counter-value="{{ $activeCashiers }}"
                            data-counter-type="number">0</h3>
                        <p class="mt-2 text-xs font-semibold text-slate-500">
                            Total discount ${{ number_format((float) $discountTotal, 2) }}
                        </p>
                    </section>
                </div>

                <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-3">
                    <section
                        class="anim-enter-up anim-delay-200 xl:col-span-2 rounded-3xl bg-white p-6 shadow-sm ring-1 ring-black/5">
                        <div class="mb-4 flex items-center justify-between">
                            <h3 class="text-xl font-bold text-[#2f241f]">Revenue & Orders Trend</h3>
                            <span class="rounded-full bg-[#fff2e7] px-3 py-1 text-xs font-semibold text-[#be6f3c]">
                                {{ $rangeLabel }}
                            </span>
                        </div>
                        <div class="dashboard-chart-wrap">
                            <canvas id="adminReportsTrendChart"></canvas>
                        </div>
                    </section>

                    <section class="anim-enter-up anim-delay-300 rounded-3xl bg-white p-6 shadow-sm ring-1 ring-black/5">
                        <h3 class="mb-4 text-xl font-bold text-[#2f241f]">Range Comparison</h3>
                        <div class="dashboard-chart-wrap dashboard-chart-wrap--compact">
                            <canvas id="adminReportsComparisonChart"></canvas>
                        </div>
                    </section>
                </div>

                <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-2">
                    <section class="anim-enter-up anim-delay-300 rounded-3xl bg-white p-6 shadow-sm ring-1 ring-black/5">
                        <h3 class="mb-4 text-xl font-bold text-[#2f241f]">Payment Breakdown</h3>
                        <div class="dashboard-chart-wrap dashboard-chart-wrap--compact">
                            <canvas id="adminReportsPaymentChart"></canvas>
                        </div>
                    </section>

                    <section class="anim-enter-up anim-delay-400 rounded-3xl bg-white p-6 shadow-sm ring-1 ring-black/5">
                        <h3 class="mb-4 text-xl font-bold text-[#2f241f]">Order Status</h3>
                        <div class="dashboard-chart-wrap dashboard-chart-wrap--compact">
                            <canvas id="adminReportsStatusChart"></canvas>
                        </div>
                    </section>
                </div>

                <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-2">
                    <section class="anim-enter-up anim-delay-400 rounded-3xl bg-white p-6 shadow-sm ring-1 ring-black/5">
                        <h3 class="mb-4 text-xl font-bold text-[#2f241f]">Revenue by Category</h3>
                        <div class="dashboard-chart-wrap">
                            <canvas id="adminReportsCategoryChart"></canvas>
                        </div>
                    </section>

                    <section class="anim-enter-up anim-delay-500 rounded-3xl bg-white p-6 shadow-sm ring-1 ring-black/5">
                        <h3 class="mb-4 text-xl font-bold text-[#2f241f]">Top Selling Items</h3>
                        <div class="dashboard-chart-wrap">
                            <canvas id="adminReportsTopItemsChart"></canvas>
                        </div>
                    </section>
                </div>

                <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-3">
                    <section id="recent-orders-section"
                        class="anim-enter-up anim-delay-300 xl:col-span-2 rounded-3xl bg-white p-6 shadow-sm ring-1 ring-black/5">
                        <div class="mb-4 flex items-center justify-between">
                            <h3 class="text-xl font-bold text-[#2f241f]">Recent Orders</h3>
                            <span class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">Latest 10</span>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[720px] text-left">
                                <thead>
                                    <tr class="border-b border-slate-200 text-sm text-gray-500">
                                        <th class="pb-3 font-medium">Order</th>
                                        <th class="pb-3 font-medium">Date</th>
                                        <th class="pb-3 font-medium">Cashier</th>
                                        <th class="pb-3 font-medium">Payment</th>
                                        <th class="pb-3 font-medium">Status</th>
                                        <th class="pb-3 font-medium text-right">Total</th>
                                    </tr>
                                </thead>
                                <tbody class="text-sm">
                                    @forelse ($recentOrders as $order)
                                        @php
                                            $orderDateValue = $order->{$reportDateColumn} ?? $order->created_at;
                                            $formattedOrderDate =
                                                $orderDateValue instanceof \Carbon\Carbon
                                                    ? $orderDateValue->format('M d, Y H:i')
                                                    : ($orderDateValue
                                                        ? \Carbon\Carbon::parse((string) $orderDateValue)->format(
                                                            'M d, Y H:i',
                                                        )
                                                        : '-');
                                        @endphp
                                        <tr class="border-b border-slate-100 last:border-b-0">
                                            <td class="py-4 font-semibold text-[#2f241f]">{{ $order->order_number }}</td>
                                            <td class="text-slate-500">{{ $formattedOrderDate }}</td>
                                            <td class="text-slate-600">{{ $order->cashier?->name ?? 'Unknown Cashier' }}
                                            </td>
                                            <td class="text-slate-600">
                                                {{ str((string) ($order->payment_method ?? 'unknown'))->replace('_', ' ')->headline() }}
                                            </td>
                                            <td>
                                                <span
                                                    class="rounded-full bg-[#fff2e7] px-2.5 py-1 text-xs font-semibold text-[#b16231]">
                                                    {{ str((string) ($order->status ?? 'completed'))->replace('_', ' ')->headline() }}
                                                </span>
                                            </td>
                                            <td class="text-right font-semibold text-[#2f241f]">
                                                ${{ number_format((float) ($order->total ?? 0), 2) }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="py-10 text-center text-sm text-slate-500">
                                                No orders found for the selected filters.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <section class="anim-enter-up anim-delay-400 rounded-3xl bg-white p-6 shadow-sm ring-1 ring-black/5">
                        <h3 class="text-xl font-bold text-[#2f241f]">Cashier Contribution</h3>
                        <div class="mt-5 space-y-5 text-sm">
                            @forelse ($cashierBreakdown as $cashierRow)
                                @php
                                    $progress =
                                        $cashierMaxRevenue > 0
                                            ? ((float) $cashierRow->revenue / $cashierMaxRevenue) * 100
                                            : 0;
                                @endphp
                                <div>
                                    <div class="mb-2 flex items-center justify-between gap-3">
                                        <span class="truncate pr-2 text-slate-700">{{ $cashierRow->cashier_name }}</span>
                                        <span
                                            class="font-semibold">${{ number_format((float) $cashierRow->revenue, 2) }}</span>
                                    </div>
                                    <div class="h-2 rounded-full bg-slate-100">
                                        <div class="dashboard-progress-bar h-2 rounded-full bg-[#f4a06b]"
                                            style="--progress-width: {{ round($progress, 2) }}%;"></div>
                                    </div>
                                    <p class="mt-1 text-xs text-slate-500">
                                        {{ number_format((int) $cashierRow->orders_count) }}
                                        orders</p>
                                </div>
                            @empty
                                <p class="text-sm text-slate-500">No cashier contribution data.</p>
                            @endforelse
                        </div>
                    </section>
                </div>

                <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-3">
                    <section class="anim-enter-up anim-delay-300 rounded-3xl bg-white p-6 shadow-sm ring-1 ring-black/5">
                        <h3 class="mb-4 text-lg font-bold text-[#2f241f]">Payment Summary</h3>
                        <div class="space-y-3 text-sm">
                            @forelse ($paymentBreakdown as $payment)
                                <div class="flex items-center justify-between rounded-xl bg-[#fff8f3] px-3 py-2">
                                    <span class="font-semibold text-[#7f4a2a]">
                                        {{ str((string) ($payment->payment_method ?? 'unknown'))->replace('_', ' ')->headline() }}
                                    </span>
                                    <span
                                        class="text-slate-600">${{ number_format((float) ($payment->revenue ?? 0), 2) }}</span>
                                </div>
                            @empty
                                <p class="text-slate-500">No payment records.</p>
                            @endforelse
                        </div>
                    </section>

                    <section class="anim-enter-up anim-delay-400 rounded-3xl bg-white p-6 shadow-sm ring-1 ring-black/5">
                        <h3 class="mb-4 text-lg font-bold text-[#2f241f]">Status Summary</h3>
                        <div class="space-y-3 text-sm">
                            @forelse ($statusBreakdown as $status)
                                <div class="flex items-center justify-between rounded-xl bg-[#f8fafc] px-3 py-2">
                                    <span class="font-semibold text-slate-700">
                                        {{ str((string) ($status->status_name ?? 'completed'))->replace('_', ' ')->headline() }}
                                    </span>
                                    <span class="text-slate-600">{{ number_format((int) ($status->orders_count ?? 0)) }}
                                        orders</span>
                                </div>
                            @empty
                                <p class="text-slate-500">No status records.</p>
                            @endforelse
                        </div>
                    </section>

                    <section class="anim-enter-up anim-delay-500 rounded-3xl bg-white p-6 shadow-sm ring-1 ring-black/5">
                        <h3 class="mb-4 text-lg font-bold text-[#2f241f]">Top Item Progress</h3>
                        <div class="space-y-4 text-sm">
                            @forelse ($topItems as $item)
                                @php
                                    $progress = $topItemMaxQty > 0 ? ((int) $item->qty_sold / $topItemMaxQty) * 100 : 0;
                                @endphp
                                <div>
                                    <div class="mb-1 flex items-center justify-between gap-2">
                                        <span class="truncate pr-2 text-slate-700">{{ $item->product_name }}</span>
                                        <span class="font-semibold">{{ number_format((int) $item->qty_sold) }}</span>
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
                    </section>
                </div>
            </main>
        </div>
    </div>

    <script id="admin-report-payload" type="application/json">@json($charts)</script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.8/dist/chart.umd.min.js"></script>
@endsection
