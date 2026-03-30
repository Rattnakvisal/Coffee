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
        $avatarUrl = $currentUser->avatarUrl();

        $roleLabels = collect($charts['roleLabels'] ?? [])->values();
        $roleCounts = collect($charts['roleCounts'] ?? [])->values();
        $roleRows = $roleLabels->map(function ($label, $index) use ($roleCounts) {
            return [
                'label' => (string) $label,
                'count' => (int) ($roleCounts->get($index) ?? 0),
            ];
        });
        $roleMaxCount = (int) ($roleRows->max('count') ?? 0);
        $teamUsers = (int) ($stats['cashiersCount'] ?? 0) + (int) ($stats['adminsCount'] ?? 0);
        $todayLabel = now()->format('M d, Y');
        $alertData = session('alert');
        $attendanceAlert = $attendanceAlert ?? null;
        $orderAlert = $orderAlert ?? null;
        $orderNotifications = collect($orderNotifications ?? []);
        $attendanceRows = collect($attendanceRows ?? []);
        $dashboardNotifications = collect();

        if ($orderNotifications->isNotEmpty()) {
            $dashboardNotifications = $dashboardNotifications->merge(
                $orderNotifications->map(function ($notification): array {
                    return [
                        'type' => 'order',
                        'id' => (int) ($notification['id'] ?? 0),
                        'source' => (string) ($notification['source'] ?? ''),
                        'title' => (string) ($notification['title'] ?? 'New Order'),
                        'message' => (string) ($notification['message'] ?? ''),
                        'time' => (string) ($notification['time'] ?? now()->format('d/m/Y H:i')),
                    ];
                }),
            );
        } elseif ($orderAlert) {
            $dashboardNotifications->push([
                'type' => 'order',
                'title' => 'Order Update',
                'message' => (string) $orderAlert,
                'time' => now()->format('d/m/Y H:i'),
            ]);
        }

        if ($attendanceAlert) {
            $dashboardNotifications->push([
                'type' => 'attendance',
                'title' => 'Attendance Update',
                'message' => (string) $attendanceAlert,
                'time' => now()->format('d/m/Y H:i'),
            ]);
        }

        if (!empty($alertData['text'])) {
            $dashboardNotifications->push([
                'type' => 'success',
                'title' => 'System Update',
                'message' => (string) $alertData['text'],
                'time' => now()->format('d/m/Y H:i'),
            ]);
        }

        if (session('status')) {
            $dashboardNotifications->push([
                'type' => 'info',
                'title' => 'Status',
                'message' => (string) session('status'),
                'time' => now()->format('d/m/Y H:i'),
            ]);
        }

        if ($errors->any()) {
            $dashboardNotifications->push([
                'type' => 'warning',
                'title' => 'Action Required',
                'message' => (string) $errors->first(),
                'time' => now()->format('d/m/Y H:i'),
            ]);
        }

        $dashboardNotifications = $dashboardNotifications->take(5)->values();
        $notificationCount = (int) $dashboardNotifications->count();
    @endphp

    <div class="anim-enter-up w-full min-h-screen overflow-hidden bg-white/85 lg:overflow-visible">
        <div class="grid min-h-screen grid-cols-1 lg:grid-cols-12">
            @include('admin.sidebar.sidebar', [
                'activeAdminMenu' => 'dashboard',
                'showFloatingAdminMenuButton' => false,
            ])

            <main
                class="anim-enter-right bg-[#f8f8f8] p-3 pt-4 sm:p-5 sm:pt-5 lg:col-span-9 lg:p-8 lg:pt-8 xl:col-span-10">
                @include('admin.partials.header')

                @if (!empty($searchFeedback))
                    <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-2 text-sm text-amber-700">
                        {{ $searchFeedback }}
                    </div>
                @endif

                @if (session('status'))
                    <div class="mb-4 rounded-xl border border-sky-200 bg-sky-50 px-4 py-2 text-sm text-sky-700">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($alertData)
                    <div
                        class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm text-emerald-700">
                        {{ $alertData['text'] ?? 'Saved successfully.' }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-2 text-sm text-rose-700">
                        {{ $errors->first() }}
                    </div>
                @endif

                <div class="grid grid-cols-1 gap-6 xl:grid-cols-12">
                    <section class="space-y-4 xl:col-span-8">
                        <div
                            class="anim-enter-up rounded-3xl bg-linear-to-r from-[#cca25a] via-[#c79d56] to-[#b88d47] p-6 text-white shadow-lg shadow-[#b88d47]/25">
                            <div class="flex flex-wrap items-center justify-between gap-4">
                                <div>
                                    <h2 class="text-3xl font-black">Dashboard</h2>
                                    <p class="mt-2 text-sm text-white/90">Welcome back, {{ $displayName }}. Today is
                                        {{ $todayLabel }}.</p>
                                    <div class="mt-4 flex flex-wrap items-center gap-2">
                                        <a href="{{ route('admin.products.index') }}"
                                            class="rounded-xl bg-white px-4 py-2 text-sm font-semibold text-[#7a5c4e] transition hover:bg-[#fff3ea]">
                                            Manage Products
                                        </a>
                                        <a href="{{ route('admin.reports') }}"
                                            class="rounded-xl border border-white/40 bg-white/15 px-4 py-2 text-sm font-semibold text-white transition hover:bg-white/25">
                                            View Reports
                                        </a>
                                        <a href="{{ route('admin.inventory.index') }}"
                                            class="rounded-xl border border-white/40 bg-white/15 px-4 py-2 text-sm font-semibold text-white transition hover:bg-white/25">
                                            Inventory Details
                                        </a>
                                    </div>
                                </div>

                                <div
                                    class="flex h-24 w-24 items-center justify-center overflow-hidden rounded-full border-4 border-white/45 bg-white/20 shadow-xl shadow-[#7a5c4e]/25">
                                    @if ($avatarUrl)
                                        <img src="{{ $avatarUrl }}" alt="Profile avatar"
                                            class="h-full w-full object-cover">
                                    @else
                                        <span class="text-2xl font-black text-white">{{ $initials }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
                            <article
                                class="anim-pop group rounded-[26px] border border-slate-200/70 bg-white/90 p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-xl">
                                <div class="flex items-center justify-between gap-2">
                                    <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-slate-500">Teams</p>
                                    <span
                                        class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M18 18.72a9.094 9.094 0 0 0 3.742-.479 3 3 0 0 0-4.682-2.72m.94 3.198v.038c0 .264-.21.478-.476.478H6.476A.477.477 0 0 1 6 18.757v-.038m12 0a9.14 9.14 0 0 1-12 0m12 0v-.038a3 3 0 0 0-.94-2.16m-10.12 2.198a9.14 9.14 0 0 0 12 0m-12 0v-.038a3 3 0 0 1 .94-2.16m10.12 2.198a3 3 0 0 0-.94-2.16m-8.24 2.16a3 3 0 0 1 .94-2.16m0 0a3 3 0 1 1 5.6 0m-5.6 0a9.093 9.093 0 0 0 5.6 0" />
                                        </svg>
                                    </span>
                                </div>
                                <h3 class="mt-3 text-3xl font-black tracking-tight text-slate-900"
                                    data-counter-value="{{ $teamUsers }}" data-counter-type="number">0</h3>
                                <p class="mt-1 text-sm text-slate-500">
                                    {{ number_format((int) ($stats['adminsCount'] ?? 0)) }} admins /
                                    {{ number_format((int) ($stats['cashiersCount'] ?? 0)) }} cashiers
                                </p>
                            </article>

                            <article
                                class="anim-pop group rounded-[26px] border border-sky-100 bg-gradient-to-br from-sky-50 to-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-xl">
                                <div class="flex items-center justify-between gap-2">
                                    <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-sky-700">Products</p>
                                    <span
                                        class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-sky-100 text-sky-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M20.25 7.5 12 3 3.75 7.5m16.5 0V16.5L12 21m8.25-13.5L12 12m0 9V12m0 0L3.75 7.5" />
                                        </svg>
                                    </span>
                                </div>
                                <h3 class="mt-3 text-3xl font-black tracking-tight text-slate-900"
                                    data-counter-value="{{ $stats['activeProductsCount'] }}" data-counter-type="number">0
                                </h3>
                                <p
                                    class="mt-1 text-sm font-semibold {{ $stats['productsGrowth']['isPositive'] ?? true ? 'text-emerald-600' : 'text-rose-600' }}">
                                    {{ $stats['productsGrowth']['text'] ?? '+0.0% vs last week' }}
                                </p>
                            </article>

                            <article
                                class="anim-pop group rounded-[26px] border border-violet-100 bg-gradient-to-br from-violet-50 to-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-xl">
                                <div class="flex items-center justify-between gap-2">
                                    <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-violet-700">Categories
                                    </p>
                                    <span
                                        class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-violet-100 text-violet-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M3.75 6.75A2.25 2.25 0 0 1 6 4.5h2.25A2.25 2.25 0 0 1 10.5 6.75V9A2.25 2.25 0 0 1 8.25 11.25H6A2.25 2.25 0 0 1 3.75 9V6.75Zm9.75 0A2.25 2.25 0 0 1 15.75 4.5H18A2.25 2.25 0 0 1 20.25 6.75V9A2.25 2.25 0 0 1 18 11.25h-2.25A2.25 2.25 0 0 1 13.5 9V6.75ZM3.75 15A2.25 2.25 0 0 1 6 12.75h2.25A2.25 2.25 0 0 1 10.5 15v2.25A2.25 2.25 0 0 1 8.25 19.5H6a2.25 2.25 0 0 1-2.25-2.25V15Zm9.75 0a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 15v2.25A2.25 2.25 0 0 1 18 19.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V15Z" />
                                        </svg>
                                    </span>
                                </div>
                                <h3 class="mt-3 text-3xl font-black tracking-tight text-slate-900"
                                    data-counter-value="{{ $stats['categoriesCount'] }}" data-counter-type="number">0
                                </h3>
                                <p class="mt-1 text-sm text-slate-500">Used by active menu products</p>
                            </article>

                            <article
                                class="anim-pop group rounded-[26px] border border-emerald-100 bg-gradient-to-br from-emerald-50 to-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-xl">
                                <div class="flex items-center justify-between gap-2">
                                    <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-emerald-700">Inventory
                                    </p>
                                    <span
                                        class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6" />
                                        </svg>
                                    </span>
                                </div>
                                <h3 class="mt-3 text-3xl font-black tracking-tight text-slate-900"
                                    data-counter-value="{{ $stats['inventoryValue'] }}" data-counter-type="currency"
                                    data-counter-decimals="2">$0.00</h3>
                                <p
                                    class="mt-1 text-sm font-semibold {{ $stats['inventoryGrowth']['isPositive'] ?? true ? 'text-emerald-600' : 'text-rose-600' }}">
                                    {{ $stats['inventoryGrowth']['text'] ?? '+0.0% vs last month' }}
                                </p>
                            </article>
                        </div>

                        <section
                            class="anim-enter-up rounded-[30px] border border-white/60 bg-white/90 p-6 shadow-[0_18px_50px_-24px_rgba(15,23,42,0.22)]">
                            <div class="mb-5 flex items-center justify-between">
                                <h3 class="text-xl font-bold text-[#2f241f]">Team Executive</h3>
                                <p class="text-sm font-semibold text-[#7b5e50]">{{ number_format($teamUsers) }} users</p>
                            </div>

                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                <div class="relative">
                                    <div class="dashboard-chart-wrap dashboard-chart-wrap--compact">
                                        <canvas id="roleDistributionChart"></canvas>
                                    </div>
                                    <div
                                        class="pointer-events-none absolute inset-0 flex items-center justify-center text-center">
                                        <div>
                                            <p
                                                class="text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-400">
                                                Total</p>
                                            <p class="text-2xl font-black text-[#2f241f]">{{ number_format($teamUsers) }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="space-y-4 text-sm">
                                    @forelse ($roleRows as $roleRow)
                                        @php
                                            $progress =
                                                $roleMaxCount > 0 ? ((int) $roleRow['count'] / $roleMaxCount) * 100 : 0;
                                        @endphp
                                        <div>
                                            <div class="mb-1 flex items-center justify-between gap-2">
                                                <span class="font-semibold text-slate-700">{{ $roleRow['label'] }}</span>
                                                <span class="font-semibold text-[#2f241f]">{{ $roleRow['count'] }}</span>
                                            </div>
                                            <div class="h-2 rounded-full bg-slate-100">
                                                <div class="dashboard-progress-bar h-2 rounded-full bg-[#6d9f2f]"
                                                    style="--progress-width: {{ round($progress, 2) }}%;"></div>
                                            </div>
                                        </div>
                                    @empty
                                        <p class="text-slate-500">No team distribution data.</p>
                                    @endforelse
                                </div>
                            </div>
                        </section>
                        <section
                            class="anim-enter-up rounded-[30px] border border-white/60 bg-white/90 p-6 shadow-[0_18px_50px_-24px_rgba(15,23,42,0.22)]">
                            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <h3 class="text-xl font-bold text-[#2f241f]">Cashier Attendance Detail</h3>
                                    <p class="mt-1 text-sm text-slate-500">Recent cashier check-ins.</p>
                                </div>
                                <span
                                    class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.08em] text-slate-600">
                                    {{ $attendanceRows->count() }} rows
                                </span>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="min-w-full text-left text-sm">
                                    <thead class="bg-slate-50 text-slate-500">
                                        <tr>
                                            <th class="px-4 py-2.5 font-semibold">Cashier</th>
                                            <th class="px-4 py-2.5 font-semibold">Email</th>
                                            <th class="px-4 py-2.5 font-semibold">Check In</th>
                                            <th class="px-4 py-2.5 font-semibold">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        @forelse ($attendanceRows as $attendance)
                                            <tr class="transition hover:bg-slate-50/70">
                                                <td class="px-4 py-3 font-semibold text-[#2f241f]">
                                                    {{ $attendance['cashier_name'] }}
                                                </td>
                                                <td class="px-4 py-3 text-slate-600">{{ $attendance['cashier_email'] }}
                                                </td>
                                                <td class="px-4 py-3 text-slate-600">{{ $attendance['checked_in_at'] }}
                                                </td>
                                                <td class="px-4 py-3">
                                                    <span
                                                        class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $attendance['is_new'] ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-700' }}">
                                                        {{ $attendance['is_new'] ? 'New' : 'Recorded' }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="px-4 py-6 text-center text-slate-500">
                                                    No cashier attendance records yet.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </section>
                    </section>

                    <aside
                        class="anim-enter-up rounded-[30px] border border-white/60 bg-white/90 p-5 shadow-[0_18px_50px_-24px_rgba(15,23,42,0.22)] xl:col-span-4">
                        <h3 class="text-3xl font-black text-[#2f241f]">My Activity</h3>

                        <div class="mt-5 space-y-6">
                            <section>
                                <div class="mb-3 flex items-center justify-between">
                                    <p class="text-sm font-semibold text-[#8f6f5c]">Upcoming Tasks</p>
                                    <a href="{{ route('admin.products.index') }}"
                                        class="text-xs font-semibold text-[#b6784d] hover:underline">View all</a>
                                </div>
                                <div class="space-y-2">
                                    <a href="{{ route('admin.products.index') }}"
                                        class="block rounded-2xl border border-[#f0e3da] bg-white px-4 py-3 transition hover:bg-[#fff6f0]">
                                        <p class="text-sm font-semibold text-[#2f241f]">Review Product Prices</p>
                                        <p class="mt-0.5 text-xs text-slate-500">Validate size pricing and discounts.</p>
                                    </a>
                                    <a href="{{ route('admin.reports') }}"
                                        class="block rounded-2xl border border-[#f0e3da] bg-white px-4 py-3 transition hover:bg-[#fff6f0]">
                                        <p class="text-sm font-semibold text-[#2f241f]">Check Sales Report</p>
                                        <p class="mt-0.5 text-xs text-slate-500">Monitor revenue and order trends.</p>
                                    </a>
                                </div>
                            </section>

                            <section>
                                <div class="mb-3 flex items-center justify-between">
                                    <p class="text-sm font-semibold text-[#8f6f5c]">Latest Shoutouts</p>
                                    <a href="{{ route('admin.users.index') }}"
                                        class="text-xs font-semibold text-[#b6784d] hover:underline">View all</a>
                                </div>
                                <div class="space-y-2">
                                    @forelse ($latestShoutouts as $member)
                                        @php
                                            $memberDisplayName = trim(
                                                (string) ($member->first_name ?? '') .
                                                    ' ' .
                                                    (string) ($member->last_name ?? ''),
                                            );
                                            $memberDisplayName =
                                                $memberDisplayName !== '' ? $memberDisplayName : (string) $member->name;
                                            $memberInitials = collect(explode(' ', $memberDisplayName))
                                                ->filter()
                                                ->map(
                                                    fn(string $namePart): string => strtoupper(substr($namePart, 0, 1)),
                                                )
                                                ->take(2)
                                                ->implode('');
                                            $memberAvatarUrl = $member->avatarUrl();
                                            $memberRoleLabel = str($member->role?->name ?? 'Team')->headline();
                                        @endphp
                                        <a href="{{ route('admin.users.index', ['search' => $memberDisplayName]) }}"
                                            class="flex items-center gap-3 rounded-2xl border border-[#f0e3da] bg-white px-3 py-2.5 transition hover:bg-[#fff6f0]">
                                            <div
                                                class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-full bg-[#2f241f] text-xs font-bold text-white ring-2 ring-[#fff1e6]">
                                                @if ($memberAvatarUrl)
                                                    <img src="{{ $memberAvatarUrl }}" alt="{{ $memberDisplayName }}"
                                                        class="h-full w-full object-cover">
                                                @else
                                                    {{ $memberInitials !== '' ? $memberInitials : 'U' }}
                                                @endif
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p class="truncate text-sm font-semibold text-[#2f241f]">
                                                    {{ $memberDisplayName }}</p>
                                                <p class="truncate text-xs text-slate-500">
                                                    Joined as {{ strtolower($memberRoleLabel) }} •
                                                    {{ $member->created_at?->diffForHumans() }}
                                                </p>
                                            </div>
                                            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                        </a>
                                    @empty
                                        <p
                                            class="rounded-2xl border border-[#f0e3da] bg-white px-4 py-3 text-sm text-slate-500">
                                            No shoutouts yet.
                                        </p>
                                    @endforelse
                                </div>
                            </section>

                            <section>
                                <div class="mb-3 flex items-center justify-between">
                                    <p class="text-sm font-semibold text-[#8f6f5c]">Recent Products</p>
                                    <a href="{{ route('admin.products.index') }}"
                                        class="text-xs font-semibold text-[#b6784d] hover:underline">View all</a>
                                </div>
                                <div class="space-y-2">
                                    @forelse ($recentProducts->take(4) as $product)
                                        <div class="rounded-2xl border border-[#f0e3da] bg-white px-4 py-3">
                                            <p class="text-sm font-semibold text-[#2f241f]">{{ $product->name }}</p>
                                            <p class="mt-0.5 text-xs text-slate-500">
                                                {{ $product->category?->name ?? 'Uncategorized' }} •
                                                {{ $product->created_at?->format('M d, Y') ?? '-' }}
                                            </p>
                                        </div>
                                    @empty
                                        <p
                                            class="rounded-2xl border border-[#f0e3da] bg-white px-4 py-3 text-sm text-slate-500">
                                            No products added yet.
                                        </p>
                                    @endforelse
                                </div>
                            </section>

                            <section>
                                <div class="mb-3 flex items-center justify-between">
                                    <p class="text-sm font-semibold text-[#8f6f5c]">Top Priced</p>
                                    <a href="{{ route('admin.products.index') }}"
                                        class="text-xs font-semibold text-[#b6784d] hover:underline">Manage</a>
                                </div>
                                <div class="space-y-3 text-sm">
                                    @forelse ($topProducts->take(4) as $product)
                                        @php
                                            $progress =
                                                $topProductsMaxPrice > 0
                                                    ? ((float) $product->price / $topProductsMaxPrice) * 100
                                                    : 0;
                                        @endphp
                                        <div class="rounded-2xl border border-[#f0e3da] bg-white px-4 py-3">
                                            <div class="mb-1 flex items-center justify-between gap-2">
                                                <span
                                                    class="truncate font-semibold text-[#2f241f]">{{ $product->name }}</span>
                                                <span
                                                    class="font-semibold text-[#7a5c4e]">${{ number_format((float) $product->price, 2) }}</span>
                                            </div>
                                            <div class="h-2 rounded-full bg-slate-100">
                                                <div class="dashboard-progress-bar h-2 rounded-full bg-[#f4a06b]"
                                                    style="--progress-width: {{ round($progress, 2) }}%;"></div>
                                            </div>
                                        </div>
                                    @empty
                                        <p
                                            class="rounded-2xl border border-[#f0e3da] bg-white px-4 py-3 text-sm text-slate-500">
                                            No top product data yet.
                                        </p>
                                    @endforelse
                                </div>
                            </section>
                        </div>
                    </aside>
                </div>
            </main>
        </div>
    </div>

    <script id="dashboard-chart-payload" type="application/json">@json($charts)</script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.8/dist/chart.umd.min.js"></script>
@endsection
