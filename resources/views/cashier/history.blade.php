@extends('layouts.app')

@section('content')
    @php
        $orders = $orders ?? new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10);
        $period = (string) ($period ?? 'day');
        $search = (string) ($search ?? '');
        $selectedPayment = strtolower((string) ($selectedPayment ?? 'all'));
        $selectedStatus = strtolower((string) ($selectedStatus ?? 'all'));
        $paymentOptions = collect($paymentOptions ?? [])
            ->filter()
            ->unique()
            ->values();
        $statusOptions = collect($statusOptions ?? [])
            ->filter()
            ->unique()
            ->values();
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

                        <button type="button" data-history-filter-open aria-controls="history-filter-panel"
                            aria-expanded="false"
                            class="inline-flex min-h-11 items-center gap-2 rounded-2xl border border-[#e7d7cb] bg-[#fffaf6] px-4 py-2.5 text-sm font-bold text-[#5c4438] shadow-[0_8px_18px_rgba(47,36,31,0.05)] transition hover:-translate-y-0.5 hover:border-[#dfc4b2] hover:bg-white hover:shadow-[0_12px_24px_rgba(47,36,31,0.08)] focus:outline-none focus:ring-2 focus:ring-[#f4a06b]/25">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-[#b16231]" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3.75 6h16.5m-13.5 6h10.5m-7.5 6h4.5" />
                            </svg>
                            Filter
                        </button>
                    </div>

                    <div data-history-filter-backdrop
                        class="fixed inset-0 z-[60] hidden bg-[#1f1713]/45 backdrop-blur-[2px]"></div>

                    <div id="history-filter-panel" data-history-filter-panel
                        class="fixed inset-y-0 right-0 z-[70] hidden w-full max-w-[430px] translate-x-full overflow-y-auto border-l border-[#ead8cb] bg-[linear-gradient(135deg,#fffaf6_0%,#ffffff_58%,#fff4ec_100%)] p-5 shadow-[-24px_0_48px_rgba(47,36,31,0.18)] transition-transform duration-300 ease-out sm:p-6"
                        role="dialog" aria-modal="true" aria-labelledby="history-filter-title">
                        <div class="mb-6 flex items-start justify-between gap-4">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-[0.18em] text-[#b16231]">Reports</p>
                                <h4 id="history-filter-title" class="mt-1 text-xl font-black tracking-tight text-[#2f241f]">
                                    Filter Orders
                                </h4>
                            </div>
                            <button type="button" data-history-filter-close
                                class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl border border-[#ead8cb] bg-white text-[#5c4438] shadow-sm transition hover:-translate-y-0.5 hover:bg-[#fff6f0] focus:outline-none focus:ring-2 focus:ring-[#f4a06b]/25"
                                aria-label="Close filter">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="1.9">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        <form method="GET" action="{{ route('cashier.history') }}"
                            class="space-y-4">
                            <div>
                                <label for="history_search"
                                    class="mb-2 block text-xs font-bold uppercase tracking-[0.16em] text-[#5f7598]">
                                    Search Order
                                </label>
                                <input id="history_search" type="text" name="search" value="{{ $search }}"
                                    placeholder="Order, payment, status"
                                    class="h-[52px] w-full rounded-2xl border border-[#ead8cb] bg-white px-4 text-sm font-medium text-[#2f241f] shadow-sm outline-none transition placeholder:text-slate-400 hover:border-[#dfc4b2] focus:border-[#f4a06b] focus:ring-4 focus:ring-[#f4a06b]/15">
                            </div>
                            <div>
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
                            <div>
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
                            <div>
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
                            <div class="grid grid-cols-2 gap-3 pt-2">
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
                                            $itemNames = collect($order->items ?? [])
                                                ->pluck('product_name')
                                                ->filter()
                                                ->values();
                                            $itemNamePreview = $itemNames->take(3)->implode(', ') ?: '-';
                                            $extraItemCount = max(0, $itemNames->count() - 3);
                                            $itemsCount = (int) ($order->items_count ?? $itemNames->count());

                                            if ($extraItemCount > 0) {
                                                $itemNamePreview .= ' +' . $extraItemCount . ' more';
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
                                                {{ number_format($itemsCount) }}
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

                @if ($orders->hasPages())
                    <div class="mt-5">
                        {{ $orders->withQueryString()->links() }}
                    </div>
                @endif
            </main>

            @include('cashier.sidebar.cart', ['activeCashierMenu' => 'cart'])
        </div>
    </div>
@endsection
