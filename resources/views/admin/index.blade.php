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
            @include('admin.sidebar.sidebar', ['activeAdminMenu' => 'dashboard'])

            <main
                class="anim-enter-right bg-[#f8f8f8] p-4 pt-20 sm:p-6 sm:pt-20 lg:col-span-9 lg:p-8 lg:pt-8 xl:col-span-10">
                <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
                    <form action="{{ route('admin.search') }}" method="GET" data-dashboard-search-form
                        class="relative w-full max-w-xl">
                        <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="1.9">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="m21 21-4.35-4.35m1.35-5.4a7.5 7.5 0 1 1-15 0 7.5 7.5 0 0 1 15 0Z" />
                            </svg>
                        </span>
                        <input id="dashboard-search-input" type="text" name="q" value="{{ $searchQuery ?? '' }}"
                            autocomplete="off" placeholder="Search products, categories, users, settings..."
                            class="w-full rounded-2xl border border-slate-200 bg-white py-3 pl-12 pr-4 text-sm outline-none transition focus:border-[#f4a06b] focus:ring-2 focus:ring-[#f4a06b]/20">

                        <div id="dashboard-search-dropdown"
                            class="absolute left-0 right-0 top-[calc(100%+0.45rem)] z-40 hidden overflow-hidden rounded-2xl border border-[#eadfd7] bg-white shadow-xl">
                            <div id="dashboard-search-option-list" role="listbox" class="max-h-72 overflow-y-auto p-1.5">
                                @foreach ($searchSuggestions ?? [] as $suggestion)
                                    <button type="button" data-search-option data-value="{{ $suggestion['value'] }}"
                                        aria-selected="false"
                                        data-search-text="{{ strtolower($suggestion['label'] . ' ' . $suggestion['type'] . ' ' . ($suggestion['meta'] ?? '')) }}"
                                        class="flex w-full items-center justify-between gap-3 rounded-xl px-3 py-2.5 text-left transition hover:bg-[#fff3ea]">
                                        <span class="min-w-0">
                                            <span class="block truncate text-sm font-semibold text-[#2f241f]">
                                                {{ $suggestion['label'] }}
                                            </span>
                                            @if (!empty($suggestion['meta']))
                                                <span class="block truncate text-xs text-slate-500">
                                                    {{ $suggestion['meta'] }}
                                                </span>
                                            @endif
                                        </span>
                                        <span
                                            class="rounded-full border border-[#f1ddce] bg-[#fff7f1] px-2.5 py-1 text-[11px] font-semibold uppercase tracking-[0.08em] text-[#b16231]">
                                            {{ $suggestion['type'] }}
                                        </span>
                                    </button>
                                @endforeach
                            </div>
                            <p id="dashboard-search-empty"
                                class="hidden border-t border-[#f3e7dd] px-4 py-3 text-sm text-slate-500">
                                No matching items
                            </p>
                        </div>
                    </form>

                    <div class="ml-auto flex items-center gap-2 sm:gap-3">
                        <div class="relative" data-admin-notification
                            data-fetch-url="{{ route('admin.notifications.index') }}"
                            data-mark-read-url="{{ route('admin.notifications.read') }}"
                            data-remove-item-url="{{ route('admin.notifications.remove.item') }}"
                            data-remove-url="{{ route('admin.notifications.remove') }}">
                            <button type="button" data-admin-notification-button
                                class="relative inline-flex h-11 w-11 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 transition hover:border-[#f4a06b] hover:text-[#b16231] hover:shadow-sm"
                                aria-label="Open notifications" aria-expanded="false">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M14.857 17.082a23.848 23.848 0 0 1-5.714 0A8.967 8.967 0 0 1 6 16.139V11a6 6 0 1 1 12 0v5.139a8.967 8.967 0 0 1-3.143.943ZM15 19.5a3 3 0 1 1-6 0" />
                                </svg>
                                <span data-admin-notification-dot
                                    class="absolute -right-1 -top-1 inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-rose-500 px-1 text-[10px] font-bold text-white {{ $notificationCount > 0 ? '' : 'hidden' }}">
                                    <span
                                        class="absolute inline-flex h-full w-full animate-ping rounded-full bg-rose-400 opacity-70"></span>
                                    <span data-admin-notification-count
                                        class="relative">{{ number_format($notificationCount) }}</span>
                                </span>
                            </button>

                            <div data-admin-notification-panel
                                class="absolute right-0 top-[calc(100%+0.55rem)] z-40 hidden w-[320px] overflow-hidden rounded-2xl border border-[#eadfd7] bg-white shadow-xl sm:w-[360px]">
                                <div class="flex items-center justify-between border-b border-[#f2e8df] px-4 py-3">
                                    <div>
                                        <p class="text-sm font-bold text-[#2f241f]">Notifications</p>
                                        <p class="text-[11px] text-slate-400">Latest 5 items</p>
                                    </div>
                                    <div class="flex items-center gap-1.5">
                                        <button type="button" data-admin-notification-mark
                                            class="rounded-lg border border-[#f1ddce] bg-[#fff7f1] px-2 py-1 text-[10px] font-semibold uppercase tracking-[0.08em] text-[#b16231] transition hover:bg-[#ffeede]">
                                            Mark
                                        </button>
                                        <button type="button" data-admin-notification-remove
                                            class="rounded-lg border border-rose-200 bg-rose-50 px-2 py-1 text-[10px] font-semibold uppercase tracking-[0.08em] text-rose-600 transition hover:bg-rose-100">
                                            Remove All
                                        </button>
                                        <span data-admin-notification-header-count
                                            class="rounded-full bg-[#fff2e7] px-2.5 py-1 text-[11px] font-semibold uppercase tracking-[0.08em] text-[#b16231]">
                                            {{ number_format($notificationCount) }}
                                        </span>
                                    </div>
                                </div>
                                <div data-admin-notification-list class="max-h-80 overflow-y-auto p-2">
                                    @foreach ($dashboardNotifications as $notification)
                                        <div data-admin-notification-item
                                            data-admin-notification-id="{{ (int) ($notification['id'] ?? 0) }}"
                                            data-admin-notification-source="{{ (string) ($notification['source'] ?? '') }}"
                                            class="mb-2 rounded-xl border border-[#f2e6dd] bg-[#fffaf6] p-3 last:mb-0">
                                            <div class="flex items-start justify-between gap-2">
                                                <p class="text-xs font-semibold uppercase tracking-[0.08em] text-[#b16231]">
                                                    {{ $notification['title'] }}
                                                </p>
                                                <div class="flex items-center gap-2">
                                                    <span
                                                        class="text-[11px] text-slate-400">{{ $notification['time'] }}</span>
                                                    @if (!empty($notification['source']) && !empty($notification['id']))
                                                        <button type="button" data-admin-notification-item-remove
                                                            data-source="{{ (string) $notification['source'] }}"
                                                            data-id="{{ (int) $notification['id'] }}"
                                                            class="inline-flex h-6 w-6 items-center justify-center rounded-full border border-rose-200 bg-rose-50 text-xs font-bold text-rose-600 transition hover:bg-rose-100"
                                                            aria-label="Remove notification item">
                                                            x
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                            <p class="mt-1 text-sm text-[#4f3b31]">{{ $notification['message'] }}</p>
                                        </div>
                                    @endforeach
                                    <div data-admin-notification-empty
                                        class="rounded-xl border border-dashed border-[#eadfd7] px-4 py-5 text-center {{ $notificationCount > 0 ? 'hidden' : '' }}">
                                        <p class="text-sm font-semibold text-slate-500">No new notifications.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="relative" data-admin-profile>
                            <button type="button" data-admin-profile-button
                                class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-2.5 py-1.5 text-[#2f241f] transition hover:border-[#f4a06b] hover:bg-[#fff9f4]"
                                aria-label="Open profile menu" aria-expanded="false">
                                <span
                                    class="flex h-8 w-8 items-center justify-center overflow-hidden rounded-full bg-[#2f241f] text-xs font-bold text-white">
                                    @if ($avatarUrl)
                                        <img src="{{ $avatarUrl }}" alt="Profile avatar" class="h-full w-full object-cover">
                                    @else
                                        {{ $initials !== '' ? $initials : 'A' }}
                                    @endif
                                </span>
                                <span class="hidden text-sm font-semibold sm:inline">{{ $displayName }}</span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-500" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                </svg>
                            </button>

                            <div data-admin-profile-panel
                                class="absolute right-0 top-[calc(100%+0.55rem)] z-40 hidden w-[280px] overflow-hidden rounded-2xl border border-[#eadfd7] bg-white shadow-xl">
                                <div class="border-b border-[#f2e8df] px-4 py-3">
                                    <p class="truncate text-sm font-bold text-[#2f241f]">{{ $displayName }}</p>
                                    <p class="truncate text-xs text-slate-500">{{ $currentUser->email ?? '-' }}</p>
                                </div>
                                <div class="p-2">
                                    <a href="{{ route('admin.settings.index') }}"
                                        class="mb-1 flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-semibold text-[#4f3b31] transition hover:bg-[#fff3ea]">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-[#b16231]"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M10.5 6h3m-7.348 1.652 2.121 2.121m7.454 7.454 2.121 2.121M6 10.5v3m12-3v3m-1.652-7.348-2.121 2.121m-7.454 7.454-2.121 2.121M12 8.25A3.75 3.75 0 1 1 12 15.75 3.75 3.75 0 0 1 12 8.25Z" />
                                        </svg>
                                        Settings
                                    </a>
                                    <a href="{{ route('admin.attendance.index') }}"
                                        class="mb-1 flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-semibold text-[#4f3b31] transition hover:bg-[#fff3ea]">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-[#b16231]"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M8.25 3v1.5m7.5-1.5v1.5M3.75 8.25h16.5M5.25 5.25h13.5A1.5 1.5 0 0 1 20.25 6.75v12a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5v-12a1.5 1.5 0 0 1 1.5-1.5Zm3.75 6h6m-6 3h3" />
                                        </svg>
                                        Attendance Detail
                                    </a>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit"
                                            class="flex w-full items-center gap-2 rounded-xl px-3 py-2 text-sm font-semibold text-rose-700 transition hover:bg-rose-50">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-7.5a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 6 21h7.5a2.25 2.25 0 0 0 2.25-2.25V15m5.25-3H9.75m0 0 3-3m-3 3 3 3" />
                                            </svg>
                                            Log out
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

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
                    <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm text-emerald-700">
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
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <h3 class="text-xl font-bold text-[#2f241f]">Inventory Details</h3>
                                    <p class="mt-1 text-sm text-slate-500">Open full income and outgoing ledger page.</p>
                                </div>
                                <a href="{{ route('admin.inventory.index') }}"
                                    class="inline-flex items-center rounded-xl bg-[#2f241f] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#201813]">
                                    Open Inventory Page
                                </a>
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
                                                <td class="px-4 py-3 text-slate-600">{{ $attendance['cashier_email'] }}</td>
                                                <td class="px-4 py-3 text-slate-600">{{ $attendance['checked_in_at'] }}</td>
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
                                            $memberAvatarUrl = $member->avatar_path
                                                ? asset('storage/' . $member->avatar_path)
                                                : null;
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
