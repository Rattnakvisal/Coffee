@extends('layouts.app')

@section('content')
    @php
        $cashierRows = collect($cashierRows ?? []);
        $attendanceHistory = collect($attendanceHistory ?? []);
        $totalCashiers = (int) ($totalCashiers ?? 0);
        $checkedTodayCount = (int) ($checkedTodayCount ?? 0);
        $pendingTodayCount = (int) ($pendingTodayCount ?? 0);
        $todayDate = now()->toDateString();
        $attendanceRate = $totalCashiers > 0 ? (int) round(($checkedTodayCount / $totalCashiers) * 100) : 0;

        $cashierQuery = trim((string) request()->query('q', ''));
        $cashierStatusFilter = trim((string) request()->query('status', 'all'));
        $cashierStatusFilter = in_array($cashierStatusFilter, ['all', 'checked', 'pending'], true)
            ? $cashierStatusFilter
            : 'all';

        $filteredCashierRows = $cashierRows
            ->filter(function (array $row) use ($cashierQuery, $cashierStatusFilter): bool {
                $name = strtolower((string) ($row['name'] ?? ''));
                $email = strtolower((string) ($row['email'] ?? ''));
                $isChecked = ($row['todayAttendance'] ?? null) !== null;

                if ($cashierQuery !== '') {
                    $needle = strtolower($cashierQuery);
                    $isSearchMatch = str_contains($name, $needle) || str_contains($email, $needle);
                    if (!$isSearchMatch) {
                        return false;
                    }
                }

                return match ($cashierStatusFilter) {
                    'checked' => $isChecked,
                    'pending' => !$isChecked,
                    default => true,
                };
            })
            ->values();

        $filteredAttendanceHistory = $attendanceHistory
            ->filter(function ($attendanceRow) use ($cashierQuery): bool {
                if ($cashierQuery === '') {
                    return true;
                }

                $cashierName = trim((string) ($attendanceRow->cashier?->name ?? ''));
                if ($cashierName === '') {
                    $cashierName = trim(
                        (string) (($attendanceRow->cashier?->first_name ?? '') .
                            ' ' .
                            ($attendanceRow->cashier?->last_name ?? '')),
                    );
                }

                $haystack = strtolower($cashierName . ' ' . (string) ($attendanceRow->cashier?->email ?? ''));
                return str_contains($haystack, strtolower($cashierQuery));
            })
            ->values();
        $attendanceFeedback = session('status') ?: ($errors->has('attendance') ? $errors->first('attendance') : '');
        $attendanceFeedbackIsError = !session('status') && $errors->has('attendance');
    @endphp

    <div class="anim-enter-up w-full min-h-screen overflow-hidden bg-white/85 lg:overflow-visible">
        <div class="grid min-h-screen grid-cols-1 lg:grid-cols-12">
            <div data-cashier-overlay class="fixed inset-0 z-40 hidden bg-[#1f1713]/50 backdrop-blur-[1px] lg:hidden"></div>
            @include('cashier.sidebar.sidebar', ['activeCashierMenu' => 'attendance'])

            <main data-attendance-page
                class="anim-enter-up anim-delay-100 p-4 pt-20 sm:p-6 sm:pt-20 lg:col-span-6 lg:p-6 lg:pt-6 xl:col-span-7">
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

                <div data-attendance-feedback
                    @class([
                        'mb-4 rounded-xl border px-4 py-3 text-sm',
                        'hidden' => $attendanceFeedback === '',
                        'border-emerald-200 bg-emerald-50 text-emerald-700' =>
                            $attendanceFeedback !== '' && !$attendanceFeedbackIsError,
                        'border-rose-200 bg-rose-50 text-rose-700' =>
                            $attendanceFeedback !== '' && $attendanceFeedbackIsError,
                    ])>
                    {{ $attendanceFeedback }}
                </div>

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
                                Cashier Workspace
                            </span>
                            <h2 class="mt-4 text-3xl font-black tracking-tight text-[#2f241f] sm:text-[2.5rem]">Attendance
                                Check</h2>
                            <p class="mt-3 max-w-xl text-sm leading-6 text-[#7a5c4e]">
                                Check attendance for the whole cashier roster. As soon as any cashier is checked in,
                                POS is ready to take orders.
                            </p>

                            <div class="mt-5 flex flex-wrap items-center gap-3">
                                <div class="rounded-2xl border border-[#edd8ca] bg-white/85 px-4 py-3 shadow-sm">
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-[#8d715f]">Today
                                    </p>
                                    <p class="mt-1 text-base font-black text-[#2f241f]">{{ now()->format('D, d M Y') }}</p>
                                </div>
                                <div class="rounded-2xl border border-emerald-200 bg-emerald-50/85 px-4 py-3 shadow-sm">
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-emerald-700">
                                        Progress
                                    </p>
                                    <p data-attendance-rate-label class="mt-1 text-base font-black text-emerald-700">{{ $attendanceRate }}% checked in
                                    </p>
                                </div>
                            </div>
                        </div>

                        <a href="{{ route('cashier.index') }}"
                            class="inline-flex items-center gap-2 rounded-2xl border border-[#e7d5c9] bg-white/90 px-5 py-3 text-sm font-semibold text-[#5c4438] shadow-sm transition hover:-translate-y-0.5 hover:bg-[#fff8f2]">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="1.9">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m10.5 19.5-7.5-7.5 7.5-7.5" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12h18" />
                            </svg>
                            Back to POS
                        </a>
                    </div>

                    <div class="relative mt-7 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                        <article
                            class="group overflow-hidden rounded-[28px] border border-[#ecdccf] bg-white/92 p-5 shadow-[0_14px_30px_rgba(47,36,31,0.08)] transition duration-200 hover:-translate-y-1 hover:shadow-[0_18px_34px_rgba(47,36,31,0.12)]">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-slate-500">Today</p>
                                    <p class="mt-3 text-2xl font-black text-[#2f241f]">{{ now()->format('d') }}</p>
                                </div>
                                <span
                                    class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-100 text-slate-600 transition group-hover:bg-slate-200">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M8.25 3v1.5m7.5-1.5v1.5M3.75 8.25h16.5M5.25 5.25h13.5A1.5 1.5 0 0 1 20.25 6.75v12a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5v-12a1.5 1.5 0 0 1 1.5-1.5Z" />
                                    </svg>
                                </span>
                            </div>
                            <p class="mt-4 text-sm font-semibold text-[#6f5a4f]">{{ now()->format('l, M Y') }}</p>
                            <div class="mt-4 h-1.5 rounded-full bg-[#f3e8df]">
                                <div class="h-1.5 w-full rounded-full bg-[#2f241f]"></div>
                            </div>
                        </article>

                        <article
                            class="group overflow-hidden rounded-[28px] border border-[#ecdccf] bg-white/92 p-5 shadow-[0_14px_30px_rgba(47,36,31,0.08)] transition duration-200 hover:-translate-y-1 hover:shadow-[0_18px_34px_rgba(47,36,31,0.12)]">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-slate-500">Total
                                        Cashiers</p>
                                    <p class="mt-3 text-3xl font-black text-[#2f241f]">{{ number_format($totalCashiers) }}
                                    </p>
                                </div>
                                <span
                                    class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-[#f5efe9] text-[#5f6f8b] transition group-hover:bg-[#ece4dc]">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M15.75 6.75a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Zm-9 13.5a5.25 5.25 0 0 1 10.5 0" />
                                    </svg>
                                </span>
                            </div>
                            <p class="mt-4 text-sm font-semibold text-[#6f5a4f]">Staff available for today&apos;s check-in
                            </p>
                            <div
                                class="mt-4 flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.12em] text-[#8b6a59]">
                                <span class="h-2 w-2 rounded-full bg-[#f4a06b]"></span>
                                Active attendance roster
                            </div>
                        </article>

                        <article
                            class="group overflow-hidden rounded-[28px] border border-emerald-200/80 bg-[linear-gradient(145deg,rgba(236,253,245,0.96),rgba(255,255,255,0.96))] p-5 shadow-[0_14px_30px_rgba(5,150,105,0.10)] transition duration-200 hover:-translate-y-1 hover:shadow-[0_18px_34px_rgba(5,150,105,0.16)]">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-emerald-700">Checked
                                        Today</p>
                                    <p data-checked-today-count class="mt-3 text-3xl font-black text-emerald-700">
                                        {{ number_format($checkedTodayCount) }}</p>
                                </div>
                                <span
                                    class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700 transition group-hover:bg-emerald-200">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                    </svg>
                                </span>
                            </div>
                            <p class="mt-4 text-sm font-semibold text-emerald-700/80">Confirmed attendance already
                                recorded</p>
                            <div class="mt-4 h-2 rounded-full bg-emerald-100">
                                <div data-attendance-rate-bar class="h-2 rounded-full bg-emerald-500"
                                    style="width: {{ min($attendanceRate, 100) }}%"></div>
                            </div>
                        </article>

                        <article
                            class="group overflow-hidden rounded-[28px] border border-amber-200/80 bg-[linear-gradient(145deg,rgba(255,251,235,0.98),rgba(255,255,255,0.96))] p-5 shadow-[0_14px_30px_rgba(217,119,6,0.10)] transition duration-200 hover:-translate-y-1 hover:shadow-[0_18px_34px_rgba(217,119,6,0.16)]">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-amber-700">Pending</p>
                                    <p data-pending-today-count class="mt-3 text-3xl font-black text-amber-700">
                                        {{ number_format($pendingTodayCount) }}
                                    </p>
                                </div>
                                <span
                                    class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-amber-100 text-amber-700 transition group-hover:bg-amber-200">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 9v3.75m0 3.75h.008v.008H12v-.008Zm9.303 3.376c.866 1.5-.217 3.374-1.949 3.374H4.646c-1.732 0-2.815-1.874-1.949-3.374L10.051 3.38c.866-1.5 3.032-1.5 3.898 0l7.354 12.746Z" />
                                    </svg>
                                </span>
                            </div>
                            <p class="mt-4 text-sm font-semibold text-amber-700/80">Cashiers still waiting to check in</p>
                            <div
                                class="mt-4 flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.12em] text-amber-700/80">
                                <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                                Attention needed
                            </div>
                        </article>
                    </div>
                </section>

                <section class="mt-6 rounded-3xl border border-[#eadfd7] bg-white p-4 shadow-sm sm:p-5">
                    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                        <h3
                            class="inline-flex items-center gap-2 text-sm font-semibold uppercase tracking-[0.1em] text-[#7b5e50]">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-[#b16231]" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3.75 6h16.5m-13.5 6h10.5m-7.5 6h4.5" />
                            </svg>
                            Filter Form
                        </h3>
                        <p class="text-xs text-slate-500">Showing {{ number_format($filteredCashierRows->count()) }} of
                            {{ number_format($cashierRows->count()) }} cashiers</p>
                    </div>

                    <form method="GET" action="{{ route('cashier.attendance') }}"
                        class="grid grid-cols-1 gap-3 md:grid-cols-12">
                        <div class="md:col-span-6">
                            <label for="q"
                                class="mb-1 block text-xs font-semibold uppercase tracking-[0.1em] text-slate-500">
                                Search Cashier
                            </label>
                            <input id="q" type="text" name="q" value="{{ $cashierQuery }}"
                                placeholder="Name or email"
                                class="w-full rounded-xl border border-[#e7d7cb] bg-[#fffcfa] px-4 py-2.5 text-sm text-[#4f3b31] outline-none transition focus:border-[#f4a06b] focus:ring-2 focus:ring-[#f4a06b]/20">
                        </div>
                        <div class="md:col-span-3">
                            <label for="status"
                                class="mb-1 block text-xs font-semibold uppercase tracking-[0.1em] text-slate-500">
                                Status
                            </label>
                            <select id="status" name="status"
                                class="w-full rounded-xl border border-[#e7d7cb] bg-[#fffcfa] px-4 py-2.5 text-sm text-[#4f3b31] outline-none transition focus:border-[#f4a06b] focus:ring-2 focus:ring-[#f4a06b]/20">
                                <option value="all" {{ $cashierStatusFilter === 'all' ? 'selected' : '' }}>All</option>
                                <option value="checked" {{ $cashierStatusFilter === 'checked' ? 'selected' : '' }}>Checked
                                </option>
                                <option value="pending" {{ $cashierStatusFilter === 'pending' ? 'selected' : '' }}>Pending
                                </option>
                            </select>
                        </div>
                        <div class="flex items-end gap-2 md:col-span-3">
                            <button type="submit"
                                class="inline-flex flex-1 items-center justify-center rounded-xl bg-[#2f241f] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#201813]">
                                Apply
                            </button>
                            <a href="{{ route('cashier.attendance') }}"
                                class="inline-flex items-center justify-center rounded-xl border border-[#e7d7cb] bg-white px-4 py-2.5 text-sm font-semibold text-[#7a5c4e] transition hover:bg-[#fff4eb]">
                                Reset
                            </a>
                        </div>
                    </form>
                </section>

                <section class="mt-6 rounded-3xl border border-[#eadfd7] bg-white p-4 shadow-sm sm:p-5">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <h3
                            class="inline-flex items-center gap-2 text-sm font-semibold uppercase tracking-[0.1em] text-[#7b5e50]">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-[#b16231]" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M18 18.72a9.094 9.094 0 0 0 3.742-.479 3 3 0 0 0-4.682-2.72m.94 3.198v.038c0 .264-.21.478-.476.478H6.476A.477.477 0 0 1 6 18.757v-.038m12 0a9.14 9.14 0 0 1-12 0m12 0v-.038a3 3 0 0 0-.94-2.16m-10.12 2.198a9.14 9.14 0 0 0 12 0m-12 0v-.038a3 3 0 0 1 .94-2.16m10.12 2.198a3 3 0 0 0-.94-2.16m-8.24 2.16a3 3 0 0 1 .94-2.16m0 0a3 3 0 1 1 5.6 0m-5.6 0a9.093 9.093 0 0 0 5.6 0" />
                            </svg>
                            Cashier Cards
                        </h3>
                        <p class="text-xs text-slate-500">{{ number_format($filteredCashierRows->count()) }} users</p>
                    </div>

                    <div class="grid grid-cols-1 gap-3 xl:grid-cols-2">
                        @forelse ($filteredCashierRows as $row)
                            @php
                                $cashier = $row['cashier'] ?? null;
                                $todayAttendance = $row['todayAttendance'] ?? null;
                                $isChecked = $todayAttendance !== null;
                                $cashierName = (string) ($row['name'] ?? '-');
                                $avatarInitial = strtoupper(substr($cashierName, 0, 1));
                            @endphp
                            <article
                                data-attendance-card
                                data-cashier-id="{{ (int) ($cashier?->id ?? 0) }}"
                                class="rounded-2xl border p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md {{ $isChecked ? 'border-emerald-200 bg-emerald-50/35' : 'border-[#ecdccf] bg-[#fffaf6]' }}">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex min-w-0 items-center gap-3">
                                        <span data-attendance-avatar-badge
                                            class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-full {{ $isChecked ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }} text-sm font-black">
                                            {{ $avatarInitial !== '' ? $avatarInitial : 'C' }}
                                        </span>
                                        <div class="min-w-0">
                                            <p class="truncate text-base font-bold text-[#2f241f]">{{ $cashierName }}</p>
                                            <p class="truncate text-sm text-[#6f5a4f]">
                                                {{ (string) ($row['email'] ?? '-') }}</p>
                                        </div>
                                    </div>
                                    <span data-attendance-status-badge
                                        class="shrink-0 rounded-full px-2.5 py-1 text-[11px] font-semibold uppercase tracking-[0.08em] {{ $isChecked ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                        {{ $isChecked ? 'Checked' : 'Pending' }}
                                    </span>
                                </div>

                                <p class="mt-3 text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">
                                    Cashier attendance record
                                </p>

                                <div class="mt-4 grid grid-cols-2 gap-3 text-xs">
                                    <div class="rounded-xl border border-[#efe1d6] bg-white/90 p-2.5">
                                        <p class="font-semibold uppercase tracking-[0.08em] text-slate-500">Check-In Time
                                        </p>
                                        <p data-attendance-time class="mt-1 text-sm font-bold text-[#2f241f]">
                                            {{ $todayAttendance?->checked_in_at?->format('H:i:s') ?? '--:--:--' }}
                                        </p>
                                    </div>
                                    <div class="rounded-xl border border-[#efe1d6] bg-white/90 p-2.5">
                                        <p class="font-semibold uppercase tracking-[0.08em] text-slate-500">Date</p>
                                        <p data-attendance-date class="mt-1 text-sm font-bold text-[#2f241f]">
                                            {{ $todayAttendance?->attended_on?->format('d/m/Y') ?? now()->format('d/m/Y') }}</p>
                                    </div>
                                </div>

                                <form method="POST" action="{{ route('cashier.attendance.check') }}" class="js-attendance-check-form mt-4">
                                    @csrf
                                    <input type="hidden" name="redirect" value="attendance">
                                    <input type="hidden" name="cashier_id" value="{{ (int) ($cashier?->id ?? 0) }}">
                                    <button type="submit" data-attendance-submit @disabled($isChecked || !$cashier)
                                        class="inline-flex w-full items-center justify-center rounded-xl px-4 py-2.5 text-sm font-semibold text-white transition {{ $isChecked ? 'bg-emerald-600' : 'bg-[#f4a06b] hover:brightness-105' }}">
                                        <span data-attendance-submit-label>
                                            {{ $isChecked ? 'Attendance Checked' : 'Check Attendance' }}
                                        </span>
                                    </button>
                                </form>
                            </article>
                        @empty
                            <div
                                class="col-span-full rounded-2xl border border-dashed border-[#e5d4c8] bg-[#fffaf5] px-4 py-10 text-center text-sm text-[#8b6a59]">
                                No cashier found for this filter.
                            </div>
                        @endforelse
                    </div>
                </section>

                <section class="mt-6 rounded-3xl border border-[#eadfd7] bg-white p-4 shadow-sm sm:p-5">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <h3
                            class="inline-flex items-center gap-2 text-sm font-semibold uppercase tracking-[0.1em] text-[#7b5e50]">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-[#b16231]" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 6v6l4 2.25M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            Recent Check-In Logs
                        </h3>
                        <p class="text-xs text-slate-500">Last {{ number_format($filteredAttendanceHistory->count()) }}
                            records
                        </p>
                    </div>

                    <div data-attendance-history-list class="space-y-2">
                        @forelse ($filteredAttendanceHistory as $attendanceRow)
                            @php
                                $isToday =
                                    $attendanceRow->attended_on &&
                                    $attendanceRow->attended_on->toDateString() === $todayDate;
                                $cashierName = trim((string) ($attendanceRow->cashier?->name ?? ''));
                                if ($cashierName === '') {
                                    $cashierName = trim(
                                        (string) (($attendanceRow->cashier?->first_name ?? '') .
                                            ' ' .
                                            ($attendanceRow->cashier?->last_name ?? '')),
                                    );
                                }
                                if ($cashierName === '') {
                                    $cashierName = (string) ($attendanceRow->cashier?->email ?? 'Unknown Cashier');
                                }
                            @endphp
                            <article
                                class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-[#efe2d8] bg-[#fffdfb] px-4 py-3 transition hover:bg-[#fff7f1]">
                                <div class="min-w-0">
                                    <p class="truncate font-semibold text-[#2f241f]">{{ $cashierName }}</p>
                                    <p class="text-xs text-slate-500">
                                        {{ $attendanceRow->attended_on?->format('d/m/Y') ?? '-' }} •
                                        {{ $attendanceRow->checked_in_at?->format('H:i:s') ?? '--:--:--' }}
                                    </p>
                                </div>
                                <span
                                    class="rounded-full px-2.5 py-1 text-[11px] font-semibold uppercase tracking-[0.08em] {{ $isToday ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-700' }}">
                                    {{ $isToday ? 'Today' : 'Recorded' }}
                                </span>
                            </article>
                        @empty
                            <div data-attendance-empty-log
                                class="rounded-2xl border border-dashed border-[#e5d4c8] bg-[#fffaf5] px-4 py-10 text-center text-sm text-[#8b6a59]">
                                No attendance record found yet.
                            </div>
                        @endforelse
                    </div>
                </section>
            </main>

            @include('cashier.sidebar.cart', ['activeCashierMenu' => 'cart'])
        </div>
    </div>

@endsection
