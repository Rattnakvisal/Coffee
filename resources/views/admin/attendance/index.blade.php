@extends('layouts.app')

@section('content')
    @php
        $search = (string) ($search ?? '');
        $date = (string) ($date ?? '');
        $todayRows = collect($todayRows ?? []);
        $totalCashiers = (int) ($totalCashiers ?? 0);
        $checkedTodayCount = (int) ($checkedTodayCount ?? 0);
        $pendingTodayCount = (int) ($pendingTodayCount ?? 0);
        $checkedPercent = $totalCashiers > 0 ? ($checkedTodayCount / $totalCashiers) * 100 : 0;
        $pendingPercent = $totalCashiers > 0 ? ($pendingTodayCount / $totalCashiers) * 100 : 0;
    @endphp

    <div class="anim-enter-up w-full min-h-screen overflow-hidden bg-white/85 lg:overflow-visible">
        <div class="grid min-h-screen grid-cols-1 lg:grid-cols-12">
            @include('admin.sidebar.sidebar', ['activeAdminMenu' => 'attendance'])

            <main
                class="anim-enter-right bg-[#f8f8f8] p-4 pt-20 sm:p-6 sm:pt-20 lg:col-span-9 lg:p-8 lg:pt-8 xl:col-span-10">
                <header class="mb-6 flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.16em] text-[#b16231]">Attendance Report</p>
                        <h1 class="mt-2 text-4xl font-black tracking-tight text-[#2f241f]">Cashier Attendance Dashboard</h1>
                        <p class="mt-2 text-3 text-slate-600">
                            {{ now()->format('l, F jS Y') }} • Today Status and Attendance History
                        </p>
                    </div>

                    <a href="{{ route('admin.index') }}"
                        class="inline-flex items-center gap-2 rounded-xl border border-[#edd5c4] bg-white px-4 py-2 text-sm font-semibold text-[#7a5c4e] transition hover:bg-[#fff6f0]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.9">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m10.5 19.5-7.5-7.5 7.5-7.5" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12h18" />
                        </svg>
                        Back to dashboard
                    </a>
                </header>

                <section class="grid grid-cols-1 gap-4 sm:auto-rows-fr sm:grid-cols-2 xl:grid-cols-4">
                    <article
                        class="anim-pop group h-full sm:min-h-50 rounded-[26px] border border-orange-100 bg-gradient-to-br from-orange-50 to-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-xl">
                        <div class="flex items-start justify-between gap-3">
                            <div
                                class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-orange-100 text-orange-700">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M8.25 3v1.5m7.5-1.5v1.5M3.75 8.25h16.5M5.25 5.25h13.5A1.5 1.5 0 0 1 20.25 6.75v12a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5v-12a1.5 1.5 0 0 1 1.5-1.5Z" />
                                </svg>
                            </div>
                            <span class="rounded-full bg-orange-100 px-2.5 py-1 text-xs font-bold text-orange-700">Today</span>
                        </div>
                        <p class="mt-3 text-[11px] font-bold uppercase tracking-[0.14em] text-orange-700">Date</p>
                        <h3 class="mt-3 text-3xl font-black tracking-tight text-slate-900">{{ now()->format('d M Y') }}</h3>
                        <p class="mt-1 text-sm text-slate-500">{{ now()->format('l') }}</p>
                    </article>

                    <article
                        class="anim-pop group h-full sm:min-h-50 rounded-[26px] border border-sky-100 bg-gradient-to-br from-sky-50 to-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-xl">
                        <div class="flex items-start justify-between gap-3">
                            <div class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-sky-100 text-sky-700">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15.75 6.75a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Zm-9 13.5a5.25 5.25 0 0 1 10.5 0" />
                                </svg>
                            </div>
                            <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-bold text-emerald-700">
                                {{ number_format($totalCashiers) }} users
                            </span>
                        </div>
                        <p class="mt-3 text-[11px] font-bold uppercase tracking-[0.14em] text-sky-700">Cashiers</p>
                        <h3 class="mt-3 text-3xl font-black tracking-tight text-slate-900">{{ number_format($totalCashiers) }}</h3>
                        <p class="mt-1 text-sm text-slate-500">Total cashier accounts</p>
                    </article>

                    <article
                        class="anim-pop group h-full sm:min-h-50 rounded-[26px] border border-violet-100 bg-gradient-to-br from-violet-50 to-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-xl">
                        <div class="flex items-start justify-between gap-3">
                            <div
                                class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-violet-100 text-violet-700">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                </svg>
                            </div>
                            <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-bold text-emerald-700">
                                {{ number_format($checkedPercent, 1) }}% checked
                            </span>
                        </div>
                        <p class="mt-3 text-[11px] font-bold uppercase tracking-[0.14em] text-violet-700">Checked Today</p>
                        <h3 class="mt-3 text-3xl font-black tracking-tight text-slate-900">{{ number_format($checkedTodayCount) }}</h3>
                        <p class="mt-1 text-sm text-slate-500">Cashiers checked for today</p>
                    </article>

                    <article
                        class="anim-pop group h-full sm:min-h-50 rounded-[26px] border border-emerald-100 bg-gradient-to-br from-emerald-50 to-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-xl">
                        <div class="flex items-start justify-between gap-3">
                            <div
                                class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 9v3.75m9.303 3.376c.866 1.5-.217 3.374-1.949 3.374H4.646c-1.732 0-2.815-1.874-1.949-3.374L10.051 3.38c.866-1.5 3.032-1.5 3.898 0l7.354 12.746Z" />
                                </svg>
                            </div>
                            <span class="rounded-full bg-[#eef5ff] px-2.5 py-1 text-xs font-bold text-[#3f79ba]">
                                {{ number_format($pendingPercent, 1) }}% pending
                            </span>
                        </div>
                        <p class="mt-3 text-[11px] font-bold uppercase tracking-[0.14em] text-emerald-700">Pending</p>
                        <h3 class="mt-3 text-3xl font-black tracking-tight text-slate-900">{{ number_format($pendingTodayCount) }}</h3>
                        <p class="mt-1 text-sm text-slate-500">Needs attendance check-in</p>
                    </article>
                </section>

                <section
                    class="mt-6 rounded-[30px] border border-white/60 bg-white/90 p-5 shadow-[0_18px_50px_-24px_rgba(15,23,42,0.22)]">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <h2 class="inline-flex items-center gap-2 text-xl font-black text-[#2f241f]">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#b16231]" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M18 18.72a9.094 9.094 0 0 0 3.742-.479 3 3 0 0 0-4.682-2.72m.94 3.198v.038c0 .264-.21.478-.476.478H6.476A.477.477 0 0 1 6 18.757v-.038m12 0a9.14 9.14 0 0 1-12 0m12 0v-.038a3 3 0 0 0-.94-2.16m-10.12 2.198a9.14 9.14 0 0 0 12 0m-12 0v-.038a3 3 0 0 1 .94-2.16m10.12 2.198a3 3 0 0 0-.94-2.16m-8.24 2.16a3 3 0 0 1 .94-2.16m0 0a3 3 0 1 1 5.6 0m-5.6 0a9.093 9.093 0 0 0 5.6 0" />
                            </svg>
                            Today Status
                        </h2>
                        <span class="rounded-full bg-[#fff2e7] px-2.5 py-1 text-xs font-semibold text-[#be6f3c]">
                            {{ number_format($todayRows->count()) }} cashiers
                        </span>
                    </div>

                    <div class="grid grid-cols-1 gap-3 xl:grid-cols-2">
                        @forelse ($todayRows as $row)
                            @php
                                $cashierName = (string) ($row['cashier_name'] ?? 'Cashier');
                                $initial = strtoupper(substr($cashierName, 0, 1));
                            @endphp
                            <article
                                class="rounded-2xl border p-4 transition hover:-translate-y-1 hover:shadow-md {{ $row['is_checked'] ? 'border-emerald-100 bg-gradient-to-br from-emerald-50 to-white' : 'border-amber-100 bg-gradient-to-br from-amber-50 to-white' }}">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex min-w-0 items-center gap-3">
                                        <span
                                            class="inline-flex h-11 w-11 items-center justify-center rounded-full {{ $row['is_checked'] ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }} text-sm font-black">
                                            {{ $initial !== '' ? $initial : 'C' }}
                                        </span>
                                        <div class="min-w-0">
                                            <p class="truncate text-base font-bold text-[#2f241f]">{{ $cashierName }}</p>
                                            <p class="truncate text-sm text-slate-500">{{ $row['cashier_email'] }}</p>
                                        </div>
                                    </div>
                                    <span
                                        class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $row['is_checked'] ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                        {{ $row['is_checked'] ? 'Checked' : 'Pending' }}
                                    </span>
                                </div>
                                <div class="mt-3 rounded-xl border border-[#f0e3da] bg-white px-3 py-2">
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-500">Check-In Time</p>
                                    <p class="mt-1 text-sm font-bold text-[#2f241f]">
                                        {{ $row['checked_in_at']?->format('H:i:s') ?? '--:--:--' }}
                                    </p>
                                </div>
                            </article>
                        @empty
                            <p class="rounded-2xl border border-[#f0e3da] bg-white px-4 py-3 text-slate-500 xl:col-span-2">
                                No cashier users found.
                            </p>
                        @endforelse
                    </div>
                </section>

                <section
                    class="mt-6 rounded-[30px] border border-white/60 bg-white/90 p-5 shadow-[0_18px_50px_-24px_rgba(15,23,42,0.22)]">
                    <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
                        <h2 class="inline-flex items-center gap-2 text-xl font-black text-[#2f241f]">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#b16231]" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3.75 6h16.5m-13.5 6h10.5m-7.5 6h4.5" />
                            </svg>
                            Attendance History
                        </h2>

                        <form method="GET" action="{{ route('admin.attendance.index') }}"
                            class="grid grid-cols-1 gap-2 sm:grid-cols-4 sm:items-end">
                            <div class="sm:col-span-2">
                                <label for="attendance_search"
                                    class="mb-1 block text-[11px] font-semibold uppercase tracking-[0.1em] text-slate-500">
                                    Search
                                </label>
                                <input id="attendance_search" type="text" name="search" value="{{ $search }}"
                                    placeholder="Cashier name or email"
                                    class="w-full rounded-2xl border border-[#ebded5] bg-white px-3 py-2.5 text-sm text-[#2f241f] outline-none transition focus:border-[#f4a06b] focus:ring-2 focus:ring-[#f4a06b]/20">
                            </div>
                            <div>
                                <label for="attendance_date"
                                    class="mb-1 block text-[11px] font-semibold uppercase tracking-[0.1em] text-slate-500">
                                    Date
                                </label>
                                <input id="attendance_date" type="date" name="date" value="{{ $date }}"
                                    class="w-full rounded-2xl border border-[#ebded5] bg-white px-3 py-2.5 text-sm text-[#2f241f] outline-none transition focus:border-[#f4a06b] focus:ring-2 focus:ring-[#f4a06b]/20">
                            </div>
                            <div class="flex gap-2">
                                <button type="submit"
                                    class="inline-flex items-center gap-2 rounded-xl bg-[#2f241f] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#3c2f29]">
                                    Apply
                                </button>
                                <a href="{{ route('admin.attendance.index') }}"
                                    class="inline-flex items-center gap-2 rounded-xl border border-[#ebded5] bg-white px-4 py-2.5 text-sm font-semibold text-[#5f4b40] transition hover:bg-[#fff6f0]">
                                    Reset
                                </a>
                            </div>
                        </form>
                    </div>

                    <div class="space-y-3">
                        @forelse ($attendanceRows as $attendance)
                            @php
                                $cashier = $attendance->cashier;
                                $cashierName = trim(
                                    (string) ($cashier?->first_name ?? '') . ' ' . (string) ($cashier?->last_name ?? ''),
                                );
                                $cashierName = $cashierName !== '' ? $cashierName : (string) ($cashier?->name ?? 'Cashier');
                            @endphp
                            <article class="rounded-2xl border border-[#f0e3da] bg-white p-4 transition hover:bg-[#fffaf6]">
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <div>
                                        <p class="text-base font-bold text-[#2f241f]">{{ $cashierName }}</p>
                                        <p class="mt-0.5 text-xs text-slate-500">{{ $cashier?->email ?? '-' }}</p>
                                    </div>
                                    @if ($attendance->admin_notified_at)
                                        <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                                            {{ $attendance->admin_notified_at->format('d/m/Y H:i') }}
                                        </span>
                                    @else
                                        <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-700">
                                            Pending
                                        </span>
                                    @endif
                                </div>

                                <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-3">
                                    <div class="rounded-xl border border-[#f0e3da] bg-white px-3 py-2.5">
                                        <p class="text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-500">Attendance Date</p>
                                        <p class="mt-1 text-sm font-bold text-[#2f241f]">
                                            {{ $attendance->attended_on?->format('d/m/Y') ?? '-' }}
                                        </p>
                                    </div>
                                    <div class="rounded-xl border border-[#f0e3da] bg-white px-3 py-2.5">
                                        <p class="text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-500">Checked At</p>
                                        <p class="mt-1 text-sm font-bold text-[#2f241f]">
                                            {{ $attendance->checked_in_at?->format('d/m/Y H:i:s') ?? '-' }}
                                        </p>
                                    </div>
                                    <div class="rounded-xl border border-[#f0e3da] bg-white px-3 py-2.5">
                                        <p class="text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-500">Admin Notified</p>
                                        <p class="mt-1 text-sm font-bold {{ $attendance->admin_notified_at ? 'text-emerald-700' : 'text-amber-700' }}">
                                            {{ $attendance->admin_notified_at ? $attendance->admin_notified_at->format('d/m/Y H:i') : 'Pending' }}
                                        </p>
                                    </div>
                                </div>
                            </article>
                        @empty
                            <p class="rounded-2xl border border-[#f0e3da] bg-white px-4 py-3 text-slate-500">No attendance records found.</p>
                        @endforelse
                    </div>

                    <div class="mt-4 border-t border-slate-100 pt-3">
                        {{ $attendanceRows->links() }}
                    </div>
                </section>
            </main>
        </div>
    </div>
@endsection
