@php
    $headerCurrentUser = $currentUser ?? auth()->user();
    $headerDisplayName =
        $displayName ??
        trim((string) ($headerCurrentUser->first_name ?? '') . ' ' . (string) ($headerCurrentUser->last_name ?? ''));
    $headerDisplayName = $headerDisplayName !== '' ? $headerDisplayName : (string) ($headerCurrentUser->name ?? 'Admin');
    $headerInitials = $initials ??
        collect(explode(' ', $headerDisplayName))
            ->filter()
            ->map(fn(string $namePart): string => strtoupper(substr($namePart, 0, 1)))
            ->take(2)
            ->implode('');
    $headerAvatarUrl = $avatarUrl ?? $headerCurrentUser?->avatarUrl();
    $headerSearchQuery = $searchQuery ?? $adminHeaderSearchQuery ?? '';
    $headerSearchSuggestions = collect($searchSuggestions ?? $adminHeaderSearchSuggestions ?? []);
    $headerNotifications = collect($dashboardNotifications ?? $adminHeaderNotifications ?? []);
    $headerNotificationCount = (int) ($notificationCount ?? $adminHeaderNotificationCount ?? $headerNotifications->count());
@endphp

<div class="mb-5 flex items-center gap-2 sm:mb-6 sm:gap-3">
    <button type="button" data-admin-sidebar-open aria-label="Open sidebar"
        class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl border border-[#ead8cb] bg-white text-sm font-semibold text-[#5d4438] shadow-sm shadow-[#2f241f]/5 transition hover:border-[#f4a06b] hover:text-[#b16231] sm:w-auto sm:gap-2 sm:px-3 lg:hidden">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
            stroke-width="1.9">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5m-16.5 5.25h16.5m-16.5 5.25h16.5" />
        </svg>
        <span class="hidden sm:inline">Menu</span>
    </button>

    <form action="{{ route('admin.search') }}" method="GET" data-dashboard-search-form class="relative min-w-0 flex-1">
        <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 sm:left-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" stroke-width="1.9">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="m21 21-4.35-4.35m1.35-5.4a7.5 7.5 0 1 1-15 0 7.5 7.5 0 0 1 15 0Z" />
            </svg>
        </span>
        <input id="dashboard-search-input" type="text" name="q" value="{{ $headerSearchQuery }}" autocomplete="off"
            placeholder="Search products, categories, users, settings..."
            class="h-11 w-full rounded-2xl border border-slate-200 bg-white py-2.5 pl-10 pr-3 text-[13px] outline-none transition focus:border-[#f4a06b] focus:ring-2 focus:ring-[#f4a06b]/20 sm:h-12 sm:py-3 sm:pl-12 sm:pr-4 sm:text-sm">

        <div id="dashboard-search-dropdown"
            class="absolute left-0 right-0 top-[calc(100%+0.45rem)] z-40 hidden overflow-hidden rounded-2xl border border-[#eadfd7] bg-white shadow-xl">
            <div id="dashboard-search-option-list" role="listbox" class="max-h-72 overflow-y-auto p-1.5">
                @foreach ($headerSearchSuggestions as $suggestion)
                    <button type="button" data-search-option data-value="{{ $suggestion['value'] }}" aria-selected="false"
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
            <p id="dashboard-search-empty" class="hidden border-t border-[#f3e7dd] px-4 py-3 text-sm text-slate-500">
                No matching items
            </p>
        </div>
    </form>

    <div class="flex shrink-0 items-center justify-end gap-1.5 sm:gap-3">
        <div class="relative" data-admin-notification data-fetch-url="{{ route('admin.notifications.index') }}"
            data-mark-read-url="{{ route('admin.notifications.read') }}"
            data-remove-item-url="{{ route('admin.notifications.remove.item') }}"
            data-remove-url="{{ route('admin.notifications.remove') }}">
            <button type="button" data-admin-notification-button
                class="relative inline-flex h-11 w-11 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 transition hover:border-[#f4a06b] hover:text-[#b16231] hover:shadow-sm sm:h-12 sm:w-12"
                aria-label="Open notifications" aria-expanded="false">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="1.9">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M14.857 17.082a23.848 23.848 0 0 1-5.714 0A8.967 8.967 0 0 1 6 16.139V11a6 6 0 1 1 12 0v5.139a8.967 8.967 0 0 1-3.143.943ZM15 19.5a3 3 0 1 1-6 0" />
                </svg>
                <span data-admin-notification-dot
                    class="absolute -right-1 -top-1 inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-rose-500 px-1 text-[10px] font-bold text-white {{ $headerNotificationCount > 0 ? '' : 'hidden' }}">
                    <span
                        class="absolute inline-flex h-full w-full animate-ping rounded-full bg-rose-400 opacity-70"></span>
                    <span data-admin-notification-count class="relative">{{ number_format($headerNotificationCount) }}</span>
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
                            {{ number_format($headerNotificationCount) }}
                        </span>
                    </div>
                </div>
                <div data-admin-notification-list class="max-h-80 overflow-y-auto p-2">
                    @foreach ($headerNotifications as $notification)
                        <div data-admin-notification-item data-admin-notification-id="{{ (int) ($notification['id'] ?? 0) }}"
                            data-admin-notification-source="{{ (string) ($notification['source'] ?? '') }}"
                            class="mb-2 rounded-xl border border-[#f2e6dd] bg-[#fffaf6] p-3 last:mb-0">
                            <div class="flex items-start justify-between gap-2">
                                <p class="text-xs font-semibold uppercase tracking-[0.08em] text-[#b16231]">
                                    {{ $notification['title'] }}
                                </p>
                                <div class="flex items-center gap-2">
                                    <span class="text-[11px] text-slate-400">{{ $notification['time'] }}</span>
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
                        class="rounded-xl border border-dashed border-[#eadfd7] px-4 py-5 text-center {{ $headerNotificationCount > 0 ? 'hidden' : '' }}">
                        <p class="text-sm font-semibold text-slate-500">No new notifications.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="relative" data-admin-profile>
            <button type="button" data-admin-profile-button
                class="inline-flex h-11 items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-2 pr-2.5 text-[#2f241f] transition hover:border-[#f4a06b] hover:bg-[#fff9f4] sm:h-12 sm:gap-2 sm:px-2.5"
                aria-label="Open profile menu" aria-expanded="false">
                <span class="flex h-8 w-8 items-center justify-center overflow-hidden rounded-full bg-[#2f241f] text-xs font-bold text-white">
                    @if ($headerAvatarUrl)
                        <img src="{{ $headerAvatarUrl }}" alt="Profile avatar" class="h-full w-full object-cover">
                    @else
                        {{ $headerInitials !== '' ? $headerInitials : 'A' }}
                    @endif
                </span>
                <span class="hidden text-sm font-semibold sm:inline">{{ $headerDisplayName }}</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-500" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="1.9">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                </svg>
            </button>

            <div data-admin-profile-panel
                class="absolute right-0 top-[calc(100%+0.55rem)] z-40 hidden w-[280px] overflow-hidden rounded-2xl border border-[#eadfd7] bg-white shadow-xl">
                <div class="border-b border-[#f2e8df] px-4 py-3">
                    <p class="truncate text-sm font-bold text-[#2f241f]">{{ $headerDisplayName }}</p>
                    <p class="truncate text-xs text-slate-500">{{ $headerCurrentUser->email ?? '-' }}</p>
                </div>
                <div class="p-2">
                    <a href="{{ route('admin.settings.index') }}"
                        class="mb-1 flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-semibold text-[#4f3b31] transition hover:bg-[#fff3ea]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-[#b16231]" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M10.5 6h3m-7.348 1.652 2.121 2.121m7.454 7.454 2.121 2.121M6 10.5v3m12-3v3m-1.652-7.348-2.121 2.121m-7.454 7.454-2.121 2.121M12 8.25A3.75 3.75 0 1 1 12 15.75 3.75 3.75 0 0 1 12 8.25Z" />
                        </svg>
                        Settings
                    </a>
                    <a href="{{ route('admin.attendance.index') }}"
                        class="mb-1 flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-semibold text-[#4f3b31] transition hover:bg-[#fff3ea]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-[#b16231]" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M8.25 3v1.5m7.5-1.5v1.5M3.75 8.25h16.5M5.25 5.25h13.5A1.5 1.5 0 0 1 20.25 6.75v12a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5v-12a1.5 1.5 0 0 1 1.5-1.5Zm3.75 6h6m-6 3h3" />
                        </svg>
                        Attendance Detail
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="flex w-full items-center gap-2 rounded-xl px-3 py-2 text-sm font-semibold text-rose-700 transition hover:bg-rose-50">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="1.9">
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
