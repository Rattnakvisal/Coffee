@extends('layouts.app')

@section('content')
    @php
        $orders = $orders ?? collect();
        $period = (string) ($period ?? 'day');
        $search = (string) ($search ?? '');
        $selectedPayment = (string) ($selectedPayment ?? 'all');
        $selectedStatus = (string) ($selectedStatus ?? 'all');
        $paymentOptions = collect($paymentOptions ?? []);
        $statusOptions = collect($statusOptions ?? []);
        $averageOrder = (float) ($averageOrder ?? 0);
        $itemsPerOrder = (int) ($ordersCount ?? 0) > 0 ? (float) $itemsSold / (int) $ordersCount : 0;
        $averageOrderMeter = $averageOrder > 0 ? min(100, (int) round($averageOrder * 10)) : 0;
        $hasHistoryFilters = $search !== '' || $selectedPayment !== 'all' || $selectedStatus !== 'all';
        $latestOrderLabel = '-';
        if (!empty($latestOrderAt)) {
            $latestOrderLabel =
                $latestOrderAt instanceof \Carbon\Carbon
                    ? $latestOrderAt->format('M d, Y h:i A')
                    : \Carbon\Carbon::parse((string) $latestOrderAt)->format('M d, Y h:i A');
        }
    @endphp

    <div class="anim-enter-up w-full min-h-screen overflow-hidden bg-white/85 lg:overflow-visible">
        <div class="grid min-h-screen grid-cols-1 lg:grid-cols-12">
            <div data-cashier-overlay class="fixed inset-0 z-40 hidden bg-[#1f1713]/50 backdrop-blur-[1px] lg:hidden"></div>
            @include('cashier.sidebar.sidebar', ['activeCashierMenu' => 'history'])

            <main
                class="anim-enter-up anim-delay-100 bg-[#f8f8f8] p-4 pt-20 sm:p-6 sm:pt-20 lg:col-span-6 lg:p-6 lg:pt-6 xl:col-span-7">
                <div class="mb-4 flex items-center justify-between gap-2 lg:hidden">
                    <button type="button" data-cashier-open-menu
                        class="inline-flex items-center gap-2 rounded-xl border border-[#e9d8cc] bg-white px-3 py-2 text-sm font-semibold text-[#6d4e3f] shadow-sm">
                        Menu
                    </button>
                    <button type="button" data-cashier-open-cart
                        class="inline-flex items-center gap-2 rounded-xl bg-[#f4a06b] px-3 py-2 text-sm font-semibold text-white shadow-sm">
                        Cart
                    </button>
                </div>

                @if (session('status'))
                    <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                        {{ $errors->first() }}
                    </div>
                @endif

                <section
                    class="relative overflow-hidden rounded-[34px] border border-[#ead8cb] bg-[linear-gradient(140deg,#fff9f4_0%,#ffffff_52%,#fff5ed_100%)] p-5 shadow-[0_24px_60px_rgba(47,36,31,0.08)] sm:p-7">
                    <div
                        class="pointer-events-none absolute -left-16 -top-16 h-40 w-40 rounded-full bg-[#ffe1ca]/80 blur-3xl">
                    </div>
                    <div class="pointer-events-none absolute right-0 top-0 h-44 w-44 rounded-full bg-[#fbeed8]/80 blur-3xl">
                    </div>

                    <div class="relative flex flex-wrap items-start justify-between gap-4">
                        <div class="max-w-2xl">
                            <span
                                class="inline-flex items-center gap-2 rounded-full border border-[#f2d6c2] bg-white/80 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.16em] text-[#b16231] shadow-sm">
                                <span class="h-2 w-2 rounded-full bg-[#f4a06b]"></span>
                                Cashier Reports
                            </span>
                            <h2 class="mt-4 text-3xl font-black tracking-tight text-[#2f241f] sm:text-[2.5rem]">Order
                                Reports</h2>
                            <p class="mt-3 max-w-xl text-sm leading-6 text-[#7a5c4e]">
                                Review {{ strtolower((string) ($periodLabel ?? 'history')) }} performance with clearer
                                revenue, order, and item trends in one focused dashboard.
                            </p>

                            <div class="mt-5 flex flex-wrap items-center gap-3"></div>
                        </div>
                    </div>

                    <div class="relative mt-7 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                        <article
                            class="group overflow-hidden rounded-[28px] border border-[#ecdccf] bg-white/92 p-5 shadow-[0_14px_30px_rgba(47,36,31,0.08)] transition duration-200 hover:-translate-y-1 hover:shadow-[0_18px_34px_rgba(47,36,31,0.12)]">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-slate-500">Orders</p>
                                    <p class="mt-3 text-3xl font-black text-[#2f241f]">
                                        {{ number_format((int) $ordersCount) }}
                                    </p>
                                </div>
                                <span
                                    class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-100 text-slate-600 transition group-hover:bg-slate-200">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M3.75 7.5h16.5m-13.5 4.5h10.5m-10.5 4.5h6m6.75-12.75h-15a1.5 1.5 0 0 0-1.5 1.5v13.5a1.5 1.5 0 0 0 1.5 1.5h15a1.5 1.5 0 0 0 1.5-1.5V5.25a1.5 1.5 0 0 0-1.5-1.5Z" />
                                    </svg>
                                </span>
                            </div>
                            <p class="mt-4 text-sm font-semibold text-[#6f5a4f]">Completed order records in the selected
                                range</p>
                            <div
                                class="mt-4 flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.12em] text-[#8b6a59]">
                                <span class="h-2 w-2 rounded-full bg-[#f4a06b]"></span>
                                Report volume
                            </div>
                        </article>

                        <article
                            class="group overflow-hidden rounded-[28px] border border-[#ecdccf] bg-white/92 p-5 shadow-[0_14px_30px_rgba(47,36,31,0.08)] transition duration-200 hover:-translate-y-1 hover:shadow-[0_18px_34px_rgba(47,36,31,0.12)]">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-slate-500">Items Sold
                                    </p>
                                    <p class="mt-3 text-3xl font-black text-[#2f241f]">{{ number_format((int) $itemsSold) }}
                                    </p>
                                </div>
                                <span
                                    class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-[#f5efe9] text-[#5f6f8b] transition group-hover:bg-[#ece4dc]">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="m20.25 7.5-8.25 4.5-8.25-4.5m16.5 0L12 3 3.75 7.5m16.5 0v9L12 21m8.25-4.5L12 12m0 9V12m0 0L3.75 7.5" />
                                    </svg>
                                </span>
                            </div>
                            <p class="mt-4 text-sm font-semibold text-[#6f5a4f]">Menu items moved through checkout</p>
                            <div
                                class="mt-4 flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.12em] text-[#8b6a59]">
                                <span class="h-2 w-2 rounded-full bg-[#2f241f]"></span>
                                Sales activity
                            </div>
                        </article>

                        <article
                            class="group overflow-hidden rounded-[28px] border border-emerald-200/80 bg-[linear-gradient(145deg,rgba(236,253,245,0.96),rgba(255,255,255,0.96))] p-5 shadow-[0_14px_30px_rgba(5,150,105,0.10)] transition duration-200 hover:-translate-y-1 hover:shadow-[0_18px_34px_rgba(5,150,105,0.16)]">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-emerald-700">Revenue
                                    </p>
                                    <p class="mt-3 text-3xl font-black text-emerald-700">
                                        ${{ number_format((float) $revenue, 2) }}</p>
                                </div>
                                <span
                                    class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700 transition group-hover:bg-emerald-200">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M2.25 18h19.5m-16.5 0V9.75m4.5 8.25v-12m4.5 12v-6m4.5 6v-3.75" />
                                    </svg>
                                </span>
                            </div>
                            <p class="mt-4 text-sm font-semibold text-emerald-700/80">Gross earnings captured in this
                                report window</p>
                            <div
                                class="mt-4 flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.12em] text-emerald-700/80">
                                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                Revenue performance
                            </div>
                        </article>

                        <article
                            class="group overflow-hidden rounded-[28px] border border-amber-200/80 bg-[linear-gradient(145deg,rgba(255,251,235,0.98),rgba(255,255,255,0.96))] p-5 shadow-[0_14px_30px_rgba(217,119,6,0.10)] transition duration-200 hover:-translate-y-1 hover:shadow-[0_18px_34px_rgba(217,119,6,0.16)]">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-amber-700">Average
                                        Order</p>
                                    <p class="mt-3 text-3xl font-black text-amber-700">
                                        ${{ number_format($averageOrder, 2) }}</p>
                                </div>
                                <span
                                    class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-amber-100 text-amber-700 transition group-hover:bg-amber-200">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 6v12m4.5-9H9.75a2.25 2.25 0 0 0 0 4.5h4.5a2.25 2.25 0 0 1 0 4.5H7.5" />
                                    </svg>
                                </span>
                            </div>
                            <p class="mt-4 text-sm font-semibold text-amber-700/80">Typical basket value across filtered
                                orders</p>
                            <div class="mt-4 h-2 rounded-full bg-amber-100">
                                <div class="h-2 rounded-full bg-amber-500" style="width: {{ $averageOrderMeter }}%"></div>
                            </div>
                        </article>
                    </div>
                </section>

                <section
                    class="mt-6 rounded-[28px] border border-[#efe2d8] bg-white p-4 shadow-[0_18px_45px_rgba(47,36,31,0.07)] sm:p-5">
                    <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <h3 class="inline-flex items-center gap-2 text-lg font-black text-[#2f241f]">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-[#b16231]" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M3.75 7.5h16.5m-13.5 4.5h10.5m-10.5 4.5h6m6.75-12.75h-15a1.5 1.5 0 0 0-1.5 1.5v13.5a1.5 1.5 0 0 0 1.5 1.5h15a1.5 1.5 0 0 0 1.5-1.5V5.25a1.5 1.5 0 0 0-1.5-1.5Z" />
                                </svg>
                                Order History
                            </h3>
                            @if ($orders->count() > 0)
                                <p class="mt-1 text-xs text-slate-500">
                                    Showing {{ $orders->firstItem() }}-{{ $orders->lastItem() }} of {{ $orders->total() }}
                                    orders
                                </p>
                            @else
                                <p class="mt-1 text-xs text-slate-500">No orders match this view</p>
                            @endif
                        </div>

                        <button type="button" data-history-filter-open
                            class="inline-flex min-h-11 items-center gap-2 rounded-2xl border border-[#e7d7cb] bg-[#fffaf6] px-4 py-2.5 text-sm font-bold text-[#5c4438] shadow-[0_8px_18px_rgba(47,36,31,0.05)] transition hover:-translate-y-0.5 hover:border-[#dfc4b2] hover:bg-white hover:shadow-[0_12px_24px_rgba(47,36,31,0.08)] focus:outline-none focus:ring-2 focus:ring-[#f4a06b]/25">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-[#b16231]" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3.75 6h16.5m-13.5 6h10.5m-7.5 6h4.5" />
                            </svg>
                            Filter
                        </button>
                    </div>

                    <div data-history-filter-panel
                        class="mb-5 hidden overflow-hidden rounded-[26px] border border-[#ead8cb] bg-[linear-gradient(135deg,#fffaf6_0%,#ffffff_56%,#fff4ec_100%)] p-4 shadow-[0_14px_32px_rgba(47,36,31,0.06)] sm:p-5">
                        <div class="mb-5 flex items-start justify-between gap-4">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-[0.18em] text-[#b16231]">Reports</p>
                                <h4 class="mt-1 text-xl font-black tracking-tight text-[#2f241f]">Filter Orders</h4>
                            </div>
                            <button type="button" data-history-filter-close
                                class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl border border-[#ead8cb] bg-white text-[#5c4438] shadow-sm transition hover:-translate-y-0.5 hover:bg-[#fff6f0] focus:outline-none focus:ring-2 focus:ring-[#f4a06b]/25"
                                aria-label="Close filter">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        <form method="GET" action="{{ route('cashier.history') }}"
                            class="grid grid-cols-1 items-end gap-3 md:grid-cols-12">
                            <div class="md:col-span-4">
                                <label for="history_search"
                                    class="mb-2 block text-xs font-bold uppercase tracking-[0.16em] text-[#5f7598]">
                                    Search Order
                                </label>
                                <input id="history_search" type="text" name="search" value="{{ $search }}"
                                    placeholder="Order, payment, status"
                                    class="h-[52px] w-full rounded-2xl border border-[#ead8cb] bg-white px-4 text-sm font-medium text-[#2f241f] shadow-sm outline-none transition placeholder:text-slate-400 hover:border-[#dfc4b2] focus:border-[#f4a06b] focus:ring-4 focus:ring-[#f4a06b]/15">
                            </div>
                            <div class="md:col-span-2">
                                <label for="history_period"
                                    class="mb-2 block text-xs font-bold uppercase tracking-[0.16em] text-[#5f7598]">
                                    Period
                                </label>
                                <select id="history_period" name="period"
                                    class="h-[52px] w-full rounded-2xl border border-[#ead8cb] bg-white px-4 text-sm font-medium text-[#2f241f] shadow-sm outline-none transition hover:border-[#dfc4b2] focus:border-[#f4a06b] focus:ring-4 focus:ring-[#f4a06b]/15">
                                    <option value="day" {{ $period === 'day' ? 'selected' : '' }}>Today</option>
                                    <option value="week" {{ $period === 'week' ? 'selected' : '' }}>This Week</option>
                                    <option value="month" {{ $period === 'month' ? 'selected' : '' }}>This Month</option>
                                </select>
                            </div>
                            <div class="md:col-span-2">
                                <label for="history_payment"
                                    class="mb-2 block text-xs font-bold uppercase tracking-[0.16em] text-[#5f7598]">
                                    Payment
                                </label>
                                <select id="history_payment" name="payment"
                                    class="h-[52px] w-full rounded-2xl border border-[#ead8cb] bg-white px-4 text-sm font-medium text-[#2f241f] shadow-sm outline-none transition hover:border-[#dfc4b2] focus:border-[#f4a06b] focus:ring-4 focus:ring-[#f4a06b]/15">
                                    <option value="all" {{ $selectedPayment === 'all' ? 'selected' : '' }}>All</option>
                                    @foreach ($paymentOptions as $paymentOption)
                                        @php $paymentValue = strtolower((string) $paymentOption); @endphp
                                        <option value="{{ $paymentValue }}"
                                            {{ $selectedPayment === $paymentValue ? 'selected' : '' }}>
                                            {{ str($paymentValue)->replace('_', ' ')->headline() }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="md:col-span-2">
                                <label for="history_status"
                                    class="mb-2 block text-xs font-bold uppercase tracking-[0.16em] text-[#5f7598]">
                                    Status
                                </label>
                                <select id="history_status" name="status"
                                    class="h-[52px] w-full rounded-2xl border border-[#ead8cb] bg-white px-4 text-sm font-medium text-[#2f241f] shadow-sm outline-none transition hover:border-[#dfc4b2] focus:border-[#f4a06b] focus:ring-4 focus:ring-[#f4a06b]/15">
                                    <option value="all" {{ $selectedStatus === 'all' ? 'selected' : '' }}>All</option>
                                    @foreach ($statusOptions as $statusOption)
                                        @php $statusOptionValue = strtolower((string) $statusOption); @endphp
                                        <option value="{{ $statusOptionValue }}"
                                            {{ $selectedStatus === $statusOptionValue ? 'selected' : '' }}>
                                            {{ str($statusOptionValue)->replace('_', ' ')->headline() }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="grid grid-cols-2 gap-2 md:col-span-2">
                                <a href="{{ route('cashier.history') }}"
                                    class="inline-flex h-[52px] items-center justify-center rounded-2xl border border-[#e7d7cb] bg-white px-4 text-sm font-bold text-[#7a5c4e] shadow-sm transition hover:-translate-y-0.5 hover:bg-[#fff6f0] focus:outline-none focus:ring-2 focus:ring-[#f4a06b]/25">
                                    Reset
                                </a>
                                <button type="submit"
                                    class="inline-flex h-[52px] items-center justify-center rounded-2xl bg-[#2f241f] px-4 text-sm font-bold text-white shadow-[0_12px_22px_rgba(47,36,31,0.22)] transition hover:-translate-y-0.5 hover:bg-[#3c2f29] hover:shadow-[0_16px_28px_rgba(47,36,31,0.28)] focus:outline-none focus:ring-2 focus:ring-[#2f241f]/25">
                                    Apply
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="overflow-hidden rounded-[24px] border border-[#f0e3da] bg-white">
                        <div class="coffee-table-scroll overflow-x-auto">
                            <table class="min-w-[620px] w-full table-fixed text-left text-sm">
                                <colgroup>
                                    <col class="w-[42%]">
                                    <col class="w-[18%]">
                                    <col class="w-[12%]">
                                    <col class="w-[14%]">
                                    <col class="w-[14%]">
                                </colgroup>
                                <thead class="bg-[#fff6f0] text-[#7a5c4e]">
                                    <tr>
                                        <th class="px-4 py-3 font-semibold">Order</th>
                                        <th class="px-4 py-3 font-semibold">Status</th>
                                        <th class="px-4 py-3 font-semibold">Items</th>
                                        <th class="px-4 py-3 font-semibold">Payment</th>
                                        <th class="px-4 py-3 font-semibold">Total</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#f4e8df]">
                                    @forelse ($orders as $order)
                                        @php
                                            $orderNumber = (string) ($order->order_number ?? '-');
                                            $orderDateValue = $order->placed_at ?? $order->created_at;
                                            $formattedOrderDate =
                                                $orderDateValue instanceof \Carbon\Carbon
                                                    ? $orderDateValue->format('M d, Y h:i A')
                                                    : ($orderDateValue
                                                        ? \Carbon\Carbon::parse((string) $orderDateValue)->format(
                                                            'M d, Y h:i A',
                                                        )
                                                        : '-');
                                            $statusValue = strtolower((string) ($order->status ?? 'completed'));
                                            $statusClasses = match ($statusValue) {
                                                'completed', 'paid' => 'bg-emerald-100 text-emerald-700',
                                                'pending', 'processing' => 'bg-amber-100 text-amber-700',
                                                'failed',
                                                'cancelled',
                                                'canceled',
                                                'refunded'
                                                    => 'bg-rose-100 text-rose-700',
                                                default => 'bg-slate-200 text-slate-700',
                                            };
                                            $itemNames = $order->items->pluck('product_name')->filter()->values();
                                            $itemNamePreview = $itemNames->take(3)->implode(', ');
                                            if ($itemNames->count() > 3) {
                                                $itemNamePreview .= ' +' . ($itemNames->count() - 3) . ' more';
                                            }
                                            if ($itemNamePreview === '') {
                                                $itemNamePreview = '-';
                                            }
                                        @endphp
                                        <tr class="align-middle transition hover:bg-[#fffaf6]">
                                            <td class="px-4 py-4">
                                                <p class="truncate font-bold text-[#2f241f]" title="{{ $orderNumber }}">
                                                    {{ $orderNumber }}</p>
                                                <p class="mt-1 text-xs text-slate-500">{{ $formattedOrderDate }}</p>
                                                <p class="mt-2 truncate text-xs text-slate-600"
                                                    title="{{ $itemNamePreview }}">
                                                    <span class="font-semibold text-[#7a5c4e]">Items:</span>
                                                    {{ $itemNamePreview }}
                                                </p>
                                            </td>
                                            <td class="px-4 py-4">
                                                <span
                                                    class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-semibold uppercase tracking-[0.08em] {{ $statusClasses }}">
                                                    {{ str((string) ($order->status ?? 'completed'))->replace('_', ' ')->headline() }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-4 font-bold text-[#2f241f]">
                                                {{ number_format((int) $order->items_count) }}
                                            </td>
                                            <td class="px-4 py-4 font-bold text-[#2f241f]">
                                                {{ str((string) ($order->payment_method ?? 'unknown'))->replace('_', ' ')->headline() }}
                                            </td>
                                            <td class="px-4 py-4 font-bold text-emerald-700">
                                                ${{ number_format((float) $order->total, 2) }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-4 py-10 text-center text-sm text-[#8b6a59]">
                                                No order history found with the current filters.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>

                <div class="mt-5">
                    {{ $orders->links() }}
                </div>
            </main>

            @include('cashier.sidebar.cart', ['activeCashierMenu' => 'cart'])
        </div>
    </div>
@endsection
