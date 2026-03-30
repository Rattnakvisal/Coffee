@extends('layouts.app')

@section('content')
    @php
        $alertData = session('alert');
        $cambodiaTimezone = 'Asia/Phnom_Penh';
        $oldHappenedAt = trim((string) old('happened_at', ''));

        if ($oldHappenedAt !== '') {
            try {
                $defaultCambodiaDateTime = \Carbon\Carbon::parse($oldHappenedAt, $cambodiaTimezone)->format(
                    'Y-m-d\TH:i',
                );
            } catch (\Throwable $exception) {
                $defaultCambodiaDateTime = now($cambodiaTimezone)->format('Y-m-d\TH:i');
            }
        } else {
            $defaultCambodiaDateTime = now($cambodiaTimezone)->format('Y-m-d\TH:i');
        }

        $entryItems = collect($entries->items());
        $overviewRows = $entryItems->take(8);
        $movementCount = $moneyInCount + $moneyOutCount;
        $movementAmount = $moneyInTotal + $moneyOutTotal;
        $incomeShare = $movementAmount > 0 ? ($moneyInTotal / $movementAmount) * 100 : 0;
        $outgoingShare = $movementAmount > 0 ? ($moneyOutTotal / $movementAmount) * 100 : 0;
        $todayMovement = $moneyInToday + $moneyOutToday;

        $distributionRaw = collect([
            ['label' => 'Income', 'value' => (float) $moneyInTotal, 'color' => '#16a34a'],
            ['label' => 'Outgoing', 'value' => (float) $moneyOutTotal, 'color' => '#f97316'],
            ['label' => 'Today In', 'value' => (float) $moneyInToday, 'color' => '#0f766e'],
            ['label' => 'Today Out', 'value' => (float) $moneyOutToday, 'color' => '#7c3aed'],
        ])
            ->filter(fn(array $slice): bool => $slice['value'] > 0)
            ->values();

        if ($distributionRaw->isEmpty()) {
            $distributionRaw = collect([['label' => 'No Data', 'value' => 1, 'color' => '#e5e7eb']]);
        }

        $distributionTotal = (float) $distributionRaw->sum('value');
        $distributionCursor = 0.0;

        $distributionSlices = $distributionRaw
            ->map(function (array $slice) use ($distributionTotal, &$distributionCursor): array {
                $size = $distributionTotal > 0 ? ($slice['value'] / $distributionTotal) * 100 : 0;
                $start = round($distributionCursor, 2);
                $distributionCursor += $size;
                $end = round($distributionCursor, 2);

                return [
                    'label' => $slice['label'],
                    'value' => (float) $slice['value'],
                    'color' => (string) $slice['color'],
                    'percent' => round($size, 1),
                    'start' => $start,
                    'end' => $end,
                ];
            })
            ->values();

        $distributionGradient = $distributionSlices
            ->map(fn(array $slice): string => $slice['color'] . ' ' . $slice['start'] . '% ' . $slice['end'] . '%')
            ->implode(', ');

        $isOutgoingFormVisible =
            $errors->any() || filled(old('amount')) || filled(old('note')) || filled(old('happened_at'));
        $activeFilterCount = collect([$preset !== 'month', $type !== 'all', $payment !== 'all', filled($search)])
            ->filter()
            ->count();
        $isFilterPanelOpen = $activeFilterCount > 0;
    @endphp

    <div class="anim-enter-up min-h-screen w-full overflow-hidden bg-[#f8f8f8] lg:overflow-visible">
        <div class="grid min-h-screen grid-cols-1 lg:grid-cols-12">
            @include('admin.sidebar.sidebar', [
                'activeAdminMenu' => 'inventory',
                'showFloatingAdminMenuButton' => false,
            ])

            <main class="relative pb-4 sm:px-5 sm:pt-5 lg:col-span-9 lg:px-8 lg:pt-8 xl:col-span-10">
                @include('admin.partials.header')

                <div class="pointer-events-none absolute inset-x-0 top-0 h-52"></div>
                <section class="relative overflow-hidden xl:p-7">
                    <div class="absolute -right-16 -top-16 h-44 w-44 "></div>
                    <div class="absolute -bottom-16 -left-12 h-40 w-40"></div>

                    <header class="mb-6 flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-[0.16em] text-[#b16231]">
                                Inventory Workspace
                            </p>
                            <h1 class="mt-3 text-2xl font-black tracking-tight text-slate-900 sm:text-4xl">
                                Inventory Management
                            </h1>
                            <p class="mt-2 max-w-3xl text-sm text-slate-600">
                                Monitor movement, review outgoing expenses, and keep every transaction organized in one
                                clean dashboard. {{ $rangeLabel }}
                                <span class="text-slate-400">({{ $startDate }} to {{ $endDate }})</span>
                            </p>
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            <button type="button" data-inventory-filter-toggle
                                aria-expanded="{{ $isFilterPanelOpen ? 'true' : 'false' }}"
                                class="inline-flex items-center gap-2 rounded-xl border border-[#eadfd7] bg-white px-3.5 py-2 text-sm font-semibold text-[#5f4b40] transition hover:bg-[#fff9f4]">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="1.9">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M3.75 6.75h16.5m-13.5 5.25h10.5m-7.5 5.25h4.5" />
                                </svg>
                                <span data-inventory-filter-toggle-label>
                                    {{ $isFilterPanelOpen ? 'Hide Filter' : 'Filter' }}
                                </span>
                                @if ($activeFilterCount > 0)
                                    <span
                                        class="inline-flex min-w-5 items-center justify-center rounded-full bg-[#fff4ec] px-1.5 py-0.5 text-[10px] font-bold text-[#b16231]">
                                        {{ $activeFilterCount }}
                                    </span>
                                @endif
                            </button>
                            <button type="button" data-inventory-outgoing-toggle
                                aria-expanded="{{ $isOutgoingFormVisible ? 'true' : 'false' }}"
                                class="inline-flex items-center gap-2 rounded-xl border border-[#eadfd7] bg-white px-3.5 py-2 text-sm font-semibold text-[#5f4b40] transition hover:bg-[#fff9f4]">
                                <span class="text-sm font-bold">+</span>
                                <span data-inventory-outgoing-toggle-label>Add Outgoing</span>
                            </button>
                        </div>
                    </header>

                    <div data-inventory-form-alert
                        class="hidden mt-4 rounded-2xl border border-orange-200 bg-orange-50 px-4 py-3 text-sm font-semibold text-orange-700">
                        Outgoing form opened. Fill details and save to show it in the table.
                    </div>

                    <template id="inventory-outgoing-template">
                        <form id="swal-inventory-outgoing-form" method="POST" action="{{ route('admin.inventory.store') }}"
                            class="space-y-4 text-left">
                            @csrf
                            <input type="hidden" name="type" value="money_out">

                            <div>
                                <label for="swal-inventory-amount"
                                    class="mb-1 block text-sm font-semibold text-[#5f4b40]">Amount (USD)</label>
                                <input id="swal-inventory-amount" name="amount" type="number" step="0.01"
                                    min="0.01" required value="{{ old('amount') }}"
                                    class="w-full rounded-xl border border-[#ecd9cc] bg-white px-4 py-3 text-sm outline-none"
                                    placeholder="0.00">
                            </div>

                            <div>
                                <label for="swal-inventory-happened-at"
                                    class="mb-1 block text-sm font-semibold text-[#5f4b40]">Date & Time</label>
                                <p class="mb-2 text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-500">
                                    Cambodia Local Time
                                </p>
                                <input id="swal-inventory-happened-at" name="happened_at" type="datetime-local"
                                    value="{{ $defaultCambodiaDateTime }}"
                                    class="w-full rounded-xl border border-[#ecd9cc] bg-white px-4 py-3 text-sm outline-none">
                            </div>

                            <div>
                                <label for="swal-inventory-note"
                                    class="mb-1 block text-sm font-semibold text-[#5f4b40]">Note</label>
                                <textarea id="swal-inventory-note" name="note" rows="4" maxlength="500"
                                    class="w-full rounded-xl border border-[#ecd9cc] bg-white px-4 py-3 text-sm outline-none"
                                    placeholder="Supplier payment, expenses, transport...">{{ old('note') }}</textarea>
                            </div>

                            <button type="submit"
                                class="inline-flex w-full items-center justify-center rounded-xl bg-[#2f241f] px-4 py-3 text-sm font-semibold text-white transition hover:bg-[#201813]">
                                Save Outgoing
                            </button>
                        </form>
                    </template>

                    @if ($alertData)
                        <div
                            class="relative mt-5 rounded-2xl border border-emerald-200 bg-emerald-50/90 px-4 py-3 text-sm font-medium text-emerald-700 shadow-sm">
                            {{ $alertData['text'] ?? 'Saved successfully.' }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div
                            class="relative mt-5 rounded-2xl border border-rose-200 bg-rose-50/90 px-4 py-3 text-sm font-medium text-rose-700 shadow-sm">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <div data-inventory-filter-panel @class([
                        'relative mt-6 rounded-[28px] border border-slate-200/80 bg-white/90 p-4 shadow-sm sm:p-5',
                        'hidden' => !$isFilterPanelOpen,
                    ])>
                        <form method="GET" action="{{ route('admin.inventory.index') }}"
                            class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-5">
                            <label class="space-y-1.5">
                                <span class="text-[11px] font-bold uppercase tracking-[0.14em] text-slate-500">Period</span>
                                <select name="preset"
                                    class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
                                    <option value="today" @selected($preset === 'today')>Today</option>
                                    <option value="week" @selected($preset === 'week')>This Week</option>
                                    <option value="month" @selected($preset === 'month')>This Month</option>
                                    <option value="all" @selected($preset === 'all')>All Time</option>
                                </select>
                            </label>

                            <label class="space-y-1.5">
                                <span class="text-[11px] font-bold uppercase tracking-[0.14em] text-slate-500">Type</span>
                                <select name="type"
                                    class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
                                    @foreach ($typeOptions as $typeOption)
                                        <option value="{{ $typeOption['value'] }}" @selected($type === $typeOption['value'])>
                                            {{ $typeOption['label'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </label>

                            <label class="space-y-1.5">
                                <span
                                    class="text-[11px] font-bold uppercase tracking-[0.14em] text-slate-500">Payment</span>
                                <select name="payment"
                                    class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
                                    <option value="all" @selected($payment === 'all')>All Payments</option>
                                    @foreach ($paymentOptions as $paymentOption)
                                        <option value="{{ $paymentOption }}" @selected($payment === $paymentOption)>
                                            {{ str($paymentOption)->replace('_', ' ')->headline() }}
                                        </option>
                                    @endforeach
                                </select>
                            </label>

                            <label class="space-y-1.5 xl:col-span-2">
                                <span class="text-[11px] font-bold uppercase tracking-[0.14em] text-slate-500">Search</span>
                                <div class="flex flex-col gap-2 sm:flex-row">
                                    <input type="text" name="search" value="{{ $search }}"
                                        placeholder="Order number, cashier, note..."
                                        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
                                    <button type="submit"
                                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#2f241f] px-4 py-3 text-sm font-semibold text-white transition hover:bg-[#3c2f29]">
                                        Apply
                                    </button>
                                    <a href="{{ route('admin.inventory.index') }}"
                                        class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                                        Reset
                                    </a>
                                </div>
                            </label>
                        </form>
                    </div>
                </section>

                <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <article
                        class="anim-pop group h-full sm:min-h-50 rounded-[26px] border border-orange-100 bg-gradient-to-br from-orange-50 to-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-xl">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-slate-500">Total Movement
                                </p>
                                <p class="mt-3 text-3xl font-black tracking-tight text-slate-900">
                                    ${{ number_format($movementAmount, 2) }}</p>
                                <p class="mt-1 text-sm text-slate-500">{{ number_format($movementCount) }} transactions
                                </p>
                            </div>
                            <span
                                class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-700">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M3.75 6.75h16.5m-13.5 5.25h10.5m-10.5 5.25h6" />
                                </svg>
                            </span>
                        </div>
                    </article>

                    <article
                        class="anim-pop group h-full sm:min-h-50 rounded-[26px] border border-emerald-100 bg-gradient-to-br from-emerald-50 to-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-xl">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-emerald-700">Income</p>
                                <p class="mt-3 text-3xl font-black tracking-tight text-slate-900">
                                    ${{ number_format($moneyInTotal, 2) }}</p>
                                <p class="mt-1 text-sm font-semibold text-emerald-700">
                                    {{ number_format($incomeShare, 1) }}% of flow</p>
                            </div>
                            <span
                                class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m-5-5 5 5 5-5" />
                                </svg>
                            </span>
                        </div>
                    </article>

                    <article
                        class="anim-pop group h-full sm:min-h-50 rounded-[26px] border border-orange-100 bg-gradient-to-br from-orange-50 to-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-xl">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-orange-700">Outgoings</p>
                                <p class="mt-3 text-3xl font-black tracking-tight text-slate-900">
                                    ${{ number_format($moneyOutTotal, 2) }}</p>
                                <p class="mt-1 text-sm font-semibold text-orange-700">
                                    {{ number_format($outgoingShare, 1) }}% of flow</p>
                            </div>
                            <span
                                class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-orange-100 text-orange-700">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 19V5m-5 5 5-5 5 5" />
                                </svg>
                            </span>
                        </div>
                    </article>

                    <article
                        class="anim-pop group h-full sm:min-h-50 rounded-[26px] border border-violet-100 bg-gradient-to-br from-violet-50 to-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-xl">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-violet-700">Net Balance
                                </p>
                                <p
                                    class="mt-3 text-3xl font-black tracking-tight {{ $balanceTotal >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">
                                    ${{ number_format($balanceTotal, 2) }}
                                </p>
                                <p class="mt-1 text-sm text-slate-500">Today movement:
                                    ${{ number_format($todayMovement, 2) }}</p>
                            </div>
                            <span
                                class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-violet-100 text-violet-700">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M4.5 12h15m-7.5-7.5 7.5 7.5-7.5 7.5" />
                                </svg>
                            </span>
                        </div>
                    </article>
                </section>

                <section class="mt-6 grid grid-cols-1 gap-5 xl:grid-cols-12">
                    <article
                        class="overflow-hidden rounded-[30px] border border-white/60 bg-white/90 shadow-[0_18px_50px_-24px_rgba(15,23,42,0.22)] xl:col-span-9">
                        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4 sm:px-6">
                            <div>
                                <h2 class="text-lg font-black text-slate-900">Inventory Status Overview</h2>
                                <p class="mt-1 text-sm text-slate-500">Latest inventory movements and recorded
                                    transactions.</p>
                            </div>
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">
                                {{ number_format($entries->total()) }} rows
                            </span>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full text-left text-sm">
                                <thead class="bg-slate-50 text-slate-500">
                                    <tr>
                                        <th class="px-4 py-3 font-bold">Reference</th>
                                        <th class="px-4 py-3 font-bold">Type</th>
                                        <th class="px-4 py-3 font-bold">Payment</th>
                                        <th class="px-4 py-3 text-right font-bold">Amount</th>
                                        <th class="px-4 py-3 font-bold">Recorded By</th>
                                        <th class="px-4 py-3 font-bold">Detail</th>
                                        <th class="px-4 py-3 font-bold">Date</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @forelse ($overviewRows as $entry)
                                        @php
                                            $isIncome = $entry['type'] === 'money_in';
                                            $amount = $isIncome
                                                ? (float) ($entry['money_in'] ?? 0)
                                                : (float) ($entry['money_out'] ?? 0);
                                        @endphp
                                        <tr class="transition hover:bg-orange-50/40">
                                            <td class="px-4 py-4 font-bold text-slate-900">{{ $entry['reference'] }}</td>
                                            <td class="px-4 py-4">
                                                <span
                                                    class="inline-flex rounded-full px-3 py-1 text-[11px] font-bold uppercase tracking-[0.12em] {{ $isIncome ? 'bg-emerald-100 text-emerald-700' : 'bg-orange-100 text-orange-700' }}">
                                                    {{ $isIncome ? 'Income' : 'Outgoing' }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-4 text-slate-600">{{ $entry['payment_method'] }}</td>
                                            <td
                                                class="px-4 py-4 text-right font-bold {{ $isIncome ? 'text-emerald-700' : 'text-orange-700' }}">
                                                {{ $isIncome ? '+' : '-' }}${{ number_format($amount, 2) }}
                                            </td>
                                            <td class="px-4 py-4 text-slate-600">{{ $entry['actor_name'] }}</td>
                                            <td class="max-w-[240px] px-4 py-4 text-slate-600"
                                                title="{{ $entry['note'] ?? '-' }}">
                                                <span
                                                    class="inline-flex rounded-xl bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-600">
                                                    {{ str($entry['note'] ?? '-')->limit(56) }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-4 text-slate-600">{{ $entry['happened_at_local'] }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="px-4 py-12 text-center text-slate-500">
                                                No transactions found for this filter.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </article>

                    <article
                        class="rounded-[30px] border border-white/60 bg-white/90 p-5 shadow-[0_18px_50px_-24px_rgba(15,23,42,0.22)] xl:col-span-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-lg font-black text-slate-900">Distribution</h2>
                                <p class="mt-1 text-sm text-slate-500">Balance of incoming and outgoing activity.</p>
                            </div>
                            <span
                                class="rounded-full bg-slate-100 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.14em] text-slate-500">
                                Mix
                            </span>
                        </div>

                        <div class="mt-6 flex justify-center">
                            <div class="relative h-48 w-48 rounded-full shadow-inner"
                                style="background: conic-gradient({{ $distributionGradient }});">
                                <div
                                    class="absolute left-1/2 top-1/2 flex h-28 w-28 -translate-x-1/2 -translate-y-1/2 flex-col items-center justify-center rounded-full border border-slate-200 bg-white shadow-sm">
                                    <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-slate-400">Total</p>
                                    <p class="mt-1 text-xl font-black text-slate-900">
                                        ${{ number_format($movementAmount, 0) }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 space-y-3">
                            @foreach ($distributionSlices as $slice)
                                <div
                                    class="flex items-center justify-between rounded-2xl border border-slate-100 bg-slate-50/70 px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <span class="h-3 w-3 rounded-full"
                                            style="background-color: {{ $slice['color'] }};"></span>
                                        <div>
                                            <p class="text-sm font-bold text-slate-800">{{ $slice['label'] }}</p>
                                            <p class="text-xs text-slate-500">{{ $slice['percent'] }}%</p>
                                        </div>
                                    </div>
                                    <p class="text-sm font-black text-slate-900">${{ number_format($slice['value'], 2) }}
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    </article>
                </section>

                <section class="mt-4 grid grid-cols-1 gap-5 xl:grid-cols-12">
                    <form method="POST" action="{{ route('admin.inventory.store') }}" data-inventory-outgoing-panel
                        @class([
                            'rounded-[30px] border border-white/60 bg-white/90 p-6 shadow-[0_18px_50px_-24px_rgba(15,23,42,0.22)] xl:col-span-5',
                            'hidden' => !$isOutgoingFormVisible,
                        ])>
                        @csrf

                        <div class="mb-5">
                            <h3 class="text-xl font-black text-slate-900">Add Outgoing</h3>
                            <p class="mt-1 text-sm text-slate-500">
                                Record supplier payments, transport costs, bills, and daily operating expenses.
                            </p>
                        </div>

                        <input type="hidden" name="type" value="money_out">

                        <div class="space-y-4">
                            <div>
                                <label for="inventory-amount" class="mb-1.5 block text-sm font-bold text-slate-700">
                                    Amount (USD)
                                </label>
                                <input id="inventory-amount" name="amount" type="number" step="0.01"
                                    min="0.01" required value="{{ old('amount') }}"
                                    class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-orange-400 focus:ring-4 focus:ring-orange-100"
                                    placeholder="0.00">
                            </div>

                            <div>
                                <label for="inventory-happened-at" class="mb-1.5 block text-sm font-bold text-slate-700">
                                    Date & Time
                                </label>
                                <p class="mb-2 text-[11px] font-bold uppercase tracking-[0.14em] text-slate-400">
                                    Cambodia Local Time
                                </p>
                                <input id="inventory-happened-at" name="happened_at" type="datetime-local"
                                    value="{{ $defaultCambodiaDateTime }}"
                                    class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
                            </div>

                            <div>
                                <label for="inventory-note" class="mb-1.5 block text-sm font-bold text-slate-700">
                                    Note
                                </label>
                                <textarea id="inventory-note" name="note" rows="4" maxlength="500"
                                    class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-orange-400 focus:ring-4 focus:ring-orange-100"
                                    placeholder="Supplier payment, expenses, transport...">{{ old('note') }}</textarea>
                            </div>

                            <button type="submit"
                                class="inline-flex w-full items-center justify-center rounded-2xl bg-orange-500 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-orange-500/20 transition hover:-translate-y-0.5 hover:bg-orange-600">
                                Save Outgoing
                            </button>
                        </div>
                    </form>
                </section>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection
