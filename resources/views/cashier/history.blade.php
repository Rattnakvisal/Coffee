@extends('layouts.app')

@section('content')
    @php
        $orders = $orders ?? collect();
        $period = (string) ($period ?? 'day');
        $search = (string) ($search ?? '');
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
                        <p class="mt-1 text-sm text-gray-500">{{ $periodLabel ?? 'History' }}</p>
                    </div>
                    <a href="{{ route('cashier.reports') }}"
                        class="rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-[#5c4438] transition hover:bg-[#fff8f2]">
                        View reports
                    </a>
                </div>

                <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
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
                </div>

                <div class="mt-6 space-y-3">
                    <form method="GET" action="{{ route('cashier.history') }}" class="flex flex-wrap items-center gap-2">
                        <input type="hidden" name="period" value="{{ $period }}">
                        <input type="text" name="search" value="{{ $search }}" placeholder="Search order number..."
                            class="min-w-[220px] flex-1 rounded-xl border border-[#e9d8cc] bg-white px-4 py-2.5 text-sm outline-none transition focus:border-[#f4a06b] focus:ring-2 focus:ring-[#f4a06b]/25">
                        @if ($search !== '')
                            <a href="{{ route('cashier.history', ['period' => $period]) }}"
                                class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-600 transition hover:bg-slate-100">Clear</a>
                        @endif
                        <button type="submit"
                            class="rounded-lg bg-[#f4a06b] px-4 py-2 text-xs font-semibold text-white">Search</button>
                    </form>

                    <div class="flex flex-wrap gap-2">
                        @foreach (['day' => 'Today', 'week' => 'This Week', 'month' => 'This Month'] as $key => $label)
                            <a href="{{ route('cashier.history', array_filter(['period' => $key, 'search' => $search])) }}"
                                @class([
                                    'rounded-full px-4 py-1.5 text-xs font-semibold uppercase tracking-[0.08em] transition',
                                    'bg-[#f4a06b] text-white' => $period === $key,
                                    'border border-gray-300 bg-white text-[#4f3b31]' => $period !== $key,
                                ])>
                                {{ $label }}
                            </a>
                        @endforeach
                    </div>
                </div>

                <div class="mt-6 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-black/5">
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[720px] text-left text-sm">
                            <thead>
                                <tr class="border-b border-slate-200 text-[#7b5e50]">
                                    <th class="px-4 py-3 font-semibold">Order</th>
                                    <th class="px-4 py-3 font-semibold">Date</th>
                                    <th class="px-4 py-3 font-semibold">Items</th>
                                    <th class="px-4 py-3 font-semibold">Payment</th>
                                    <th class="px-4 py-3 font-semibold">Status</th>
                                    <th class="px-4 py-3 text-right font-semibold">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($orders as $order)
                                    <tr class="border-b border-slate-100">
                                        <td class="px-4 py-3 font-semibold text-[#2f241f]">{{ $order->order_number }}</td>
                                        <td class="px-4 py-3 text-slate-500">
                                            {{ optional($order->placed_at ?? $order->created_at)->format('M d, Y h:i A') }}
                                        </td>
                                        <td class="px-4 py-3 text-slate-600">{{ number_format((int) $order->items_count) }}
                                        </td>
                                        <td class="px-4 py-3 text-slate-600">{{ strtoupper((string) $order->payment_method) }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <span
                                                class="rounded-full bg-emerald-100 px-2.5 py-1 text-[11px] font-semibold uppercase tracking-[0.08em] text-emerald-700">
                                                {{ (string) ($order->status ?? 'completed') }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-right font-semibold text-[#2f241f]">
                                            ${{ number_format((float) $order->total, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-8 text-center text-slate-500">
                                            No order history found for this period.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-5">
                    {{ $orders->links() }}
                </div>
            </main>

            @include('cashier.sidebar.cart', ['activeCashierMenu' => 'cart'])
        </div>
    </div>

@endsection
