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

                <div class="flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.12em] text-[#b16231]">Cashier</p>
                        <h2 class="mt-1 text-3xl font-black text-[#2f241f]">Order History</h2>
                        <p class="mt-1 text-sm text-gray-500">{{ $periodLabel ?? 'History' }} records with live filters</p>
                    </div>

                    <a href="{{ route('cashier.index') }}"
                        class="rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-[#5c4438] transition hover:bg-[#fff8f2]">
                        Back to POS
                    </a>
                </div>

                <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-black/5">
                        <p class="text-xs font-semibold uppercase tracking-[0.1em] text-gray-500">Orders</p>
                        <p class="mt-2 text-2xl font-black text-[#2f241f]">{{ number_format((int) $ordersCount) }}</p>
                    </div>
                    <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-black/5">
                        <p class="text-xs font-semibold uppercase tracking-[0.1em] text-gray-500">Items Sold</p>
                        <p class="mt-2 text-2xl font-black text-[#2f241f]">{{ number_format((int) $itemsSold) }}</p>
                    </div>
                    <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-black/5">
                        <p class="text-xs font-semibold uppercase tracking-[0.1em] text-gray-500">Revenue</p>
                        <p class="mt-2 text-2xl font-black text-[#d97f46]">${{ number_format((float) $revenue, 2) }}</p>
                    </div>
                    <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-black/5">
                        <p class="text-xs font-semibold uppercase tracking-[0.1em] text-gray-500">Average Order</p>
                        <p class="mt-2 text-2xl font-black text-[#2f241f]">${{ number_format($averageOrder, 2) }}</p>
                    </div>
                </div>

                <section class="mt-6 rounded-2xl border border-[#eadfd7] bg-white p-4 shadow-sm">
                    <div class="flex flex-wrap items-center gap-2">
                        @foreach (['day' => 'Today', 'week' => 'This Week', 'month' => 'This Month'] as $key => $label)
                            <a href="{{ route('cashier.history', array_filter(['period' => $key, 'search' => $search, 'payment' => $selectedPayment !== 'all' ? $selectedPayment : null, 'status' => $selectedStatus !== 'all' ? $selectedStatus : null])) }}"
                                @class([
                                    'rounded-full px-4 py-1.5 text-xs font-semibold uppercase tracking-[0.08em] transition',
                                    'bg-[#f4a06b] text-white' => $period === $key,
                                    'border border-gray-300 bg-white text-[#4f3b31]' => $period !== $key,
                                ])>
                                {{ $label }}
                            </a>
                        @endforeach
                    </div>

                    <form method="GET" action="{{ route('cashier.history') }}" class="mt-4 space-y-3">
                        <input type="hidden" name="period" value="{{ $period }}">

                        <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-5">
                            <label class="space-y-1 md:col-span-2 xl:col-span-2">
                                <span class="text-xs font-semibold uppercase tracking-[0.1em] text-slate-500">Search</span>
                                <input type="text" name="search" value="{{ $search }}"
                                    placeholder="Order number, payment, or status..."
                                    class="w-full rounded-xl border border-[#e9d8cc] bg-white px-4 py-2.5 text-sm outline-none transition focus:border-[#f4a06b] focus:ring-2 focus:ring-[#f4a06b]/25">
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

                            <div class="flex items-end gap-2">
                                <button type="submit"
                                    class="inline-flex flex-1 items-center justify-center rounded-xl bg-[#2f241f] px-4 py-2.5 text-sm font-semibold text-white transition hover:brightness-110">
                                    Apply
                                </button>
                                <a href="{{ route('cashier.history', ['period' => $period]) }}"
                                    class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                                    Reset
                                </a>
                            </div>
                        </div>
                    </form>

                    <div class="mt-3 flex flex-wrap items-center justify-between gap-2 text-xs text-slate-500">
                        <p>
                            @if ($hasHistoryFilters)
                                Filters are active for this list.
                            @else
                                Showing all orders for {{ strtolower((string) ($periodLabel ?? 'selected period')) }}.
                            @endif
                        </p>
                        <p>Latest order in result: {{ $latestOrderLabel }}</p>
                    </div>
                </section>

                <section class="mt-6 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-black/5">
                    <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
                        <h3 class="text-sm font-semibold uppercase tracking-[0.1em] text-[#7b5e50]">Order List</h3>
                        @if ($orders->count() > 0)
                            <p class="text-xs text-slate-500">
                                Showing {{ $orders->firstItem() }}-{{ $orders->lastItem() }} of {{ $orders->total() }}
                            </p>
                        @endif
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[760px] text-left text-sm">
                            <thead>
                                <tr class="border-b border-slate-200 text-[#7b5e50]">
                                    <th class="px-4 py-3 font-semibold">Order</th>
                                    <th class="px-4 py-3 font-semibold">Date</th>
                                    <th class="px-4 py-3 font-semibold">Payment</th>
                                    <th class="px-4 py-3 font-semibold">Status</th>
                                    <th class="px-4 py-3 text-right font-semibold">Items</th>
                                    <th class="px-4 py-3 text-right font-semibold">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($orders as $order)
                                    @php
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
                                            'failed', 'cancelled', 'canceled', 'refunded' => 'bg-rose-100 text-rose-700',
                                            default => 'bg-slate-200 text-slate-700',
                                        };
                                    @endphp
                                    <tr class="border-b border-slate-100 transition hover:bg-[#fffaf7]">
                                        <td class="px-4 py-3 font-semibold text-[#2f241f]">{{ $order->order_number }}</td>
                                        <td class="px-4 py-3 text-slate-500">{{ $formattedOrderDate }}</td>
                                        <td class="px-4 py-3 text-slate-600">
                                            {{ str((string) ($order->payment_method ?? 'unknown'))->replace('_', ' ')->headline() }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <span
                                                class="rounded-full px-2.5 py-1 text-[11px] font-semibold uppercase tracking-[0.08em] {{ $statusClasses }}">
                                                {{ str((string) ($order->status ?? 'completed'))->replace('_', ' ')->headline() }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-right font-semibold text-slate-600">
                                            {{ number_format((int) $order->items_count) }}
                                        </td>
                                        <td class="px-4 py-3 text-right font-semibold text-[#2f241f]">
                                            ${{ number_format((float) $order->total, 2) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-10 text-center text-slate-500">
                                            No order history found with the current filters.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
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
