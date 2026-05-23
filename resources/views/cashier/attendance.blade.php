@extends('layouts.app')

@section('content')
    @php
        $cashierRows = collect($cashierRows ?? []);
        $attendanceFeedback = session('status') ?: ($errors->has('attendance') ? $errors->first('attendance') : '');
        $attendanceFeedbackIsError = !session('status') && $errors->has('attendance');
    @endphp

    <div class="anim-enter-up w-full min-h-screen overflow-hidden bg-white/85 lg:overflow-visible">
        <div class="grid min-h-screen grid-cols-1 lg:grid-cols-12">
            <div data-cashier-overlay class="fixed inset-0 z-40 hidden bg-[#1f1713]/50 backdrop-blur-[1px] lg:hidden"></div>
            @include('cashier.sidebar.sidebar', ['activeCashierMenu' => 'attendance'])

            <main data-attendance-page
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

                <div data-attendance-feedback @class([
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
                    class="mt-6 rounded-[28px] border border-[#efe2d8] bg-white p-4 shadow-[0_18px_45px_rgba(47,36,31,0.07)] sm:p-5">
                    <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <h3 class="inline-flex items-center gap-2 text-lg font-black text-[#2f241f]">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-[#b16231]" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M18 18.72a9.094 9.094 0 0 0 3.742-.479 3 3 0 0 0-4.682-2.72m.94 3.198v.038c0 .264-.21.478-.476.478H6.476A.477.477 0 0 1 6 18.757v-.038m12 0a9.14 9.14 0 0 1-12 0m12 0v-.038a3 3 0 0 0-.94-2.16m-10.12 2.198a9.14 9.14 0 0 0 12 0m-12 0v-.038a3 3 0 0 1 .94-2.16m10.12 2.198a3 3 0 0 0-.94-2.16m-8.24 2.16a3 3 0 0 1 .94-2.16m0 0a3 3 0 1 1 5.6 0m-5.6 0a9.093 9.093 0 0 0 5.6 0" />
                                </svg>
                                Today Status
                            </h3>
                            <p class="mt-1 text-xs text-slate-500">
                                {{ number_format($cashierRows->count()) }} cashiers today
                            </p>
                        </div>
                    </div>

                    <div class="overflow-hidden rounded-[24px] border border-[#f0e3da] bg-white">
                        <div class="overflow-x-auto">
                            <table class="min-w-[760px] w-full text-left text-sm">
                                <thead class="bg-[#fff6f0] text-[#7a5c4e]">
                                    <tr>
                                        <th class="px-4 py-3 font-semibold">Cashier</th>
                                        <th class="px-4 py-3 font-semibold">Status</th>
                                        <th class="px-4 py-3 font-semibold">Check-In Time</th>
                                        <th class="px-4 py-3 font-semibold">Date</th>
                                        <th class="px-4 py-3 text-right font-semibold">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#f4e8df]">
                                    @forelse ($cashierRows as $row)
                                        @php
                                            $cashier = $row['cashier'] ?? null;
                                            $todayAttendance = $row['todayAttendance'] ?? null;
                                            $isChecked = $todayAttendance !== null;
                                            $cashierName = (string) ($row['name'] ?? '-');
                                            $avatarInitial = strtoupper(substr($cashierName, 0, 1));
                                        @endphp
                                        <tr data-attendance-card data-cashier-id="{{ (int) ($cashier?->id ?? 0) }}"
                                            class="align-middle transition hover:bg-[#fffaf6] {{ $isChecked ? 'bg-emerald-50/50' : 'bg-amber-50/40' }}">
                                            <td class="px-4 py-4">
                                                <div class="flex min-w-0 items-center gap-3">
                                                    <span data-attendance-avatar-badge
                                                        class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full {{ $isChecked ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }} text-sm font-black">
                                                        {{ $avatarInitial !== '' ? $avatarInitial : 'C' }}
                                                    </span>
                                                    <div class="min-w-0">
                                                        <p class="truncate font-bold text-[#2f241f]">{{ $cashierName }}
                                                        </p>
                                                        <p class="truncate text-xs text-slate-500">
                                                            {{ (string) ($row['email'] ?? '-') }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-4 py-4">
                                                <span data-attendance-status-badge
                                                    class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-semibold uppercase tracking-[0.08em] {{ $isChecked ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                                    {{ $isChecked ? 'Checked' : 'Pending' }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-4">
                                                <p data-attendance-time class="font-bold text-[#2f241f]">
                                                    {{ $todayAttendance?->checked_in_at?->format('H:i:s') ?? '--:--:--' }}
                                                </p>
                                            </td>
                                            <td class="px-4 py-4">
                                                <p data-attendance-date class="font-bold text-[#2f241f]">
                                                    {{ $todayAttendance?->attended_on?->format('d/m/Y') ?? now()->format('d/m/Y') }}
                                                </p>
                                            </td>
                                            <td class="px-4 py-4">
                                                <form method="POST" action="{{ route('cashier.attendance.check') }}"
                                                    class="js-attendance-check-form flex justify-end">
                                                    @csrf
                                                    <input type="hidden" name="redirect" value="attendance">
                                                    <input type="hidden" name="cashier_id"
                                                        value="{{ (int) ($cashier?->id ?? 0) }}">
                                                    <button type="submit" data-attendance-submit
                                                        @disabled($isChecked || !$cashier)
                                                        class="inline-flex min-w-40 items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold text-white transition disabled:cursor-not-allowed {{ $isChecked ? 'bg-emerald-600' : 'bg-[#2f241f] hover:bg-[#3c2f29]' }}">
                                                        <span data-attendance-submit-label>
                                                            {{ $isChecked ? 'Attendance Checked' : 'Check Attendance' }}
                                                        </span>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-4 py-10 text-center text-sm text-[#8b6a59]">
                                                No cashier found.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>

            </main>

            @include('cashier.sidebar.cart', ['activeCashierMenu' => 'cart'])
        </div>
    </div>
@endsection
