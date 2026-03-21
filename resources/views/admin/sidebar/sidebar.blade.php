@php
    $activeAdminMenu = $activeAdminMenu ?? 'dashboard';
    $authUser = auth()->user();

    $profileName = trim((string) ($authUser->first_name ?? '') . ' ' . (string) ($authUser->last_name ?? ''));
    $profileName = $profileName !== '' ? $profileName : (string) ($authUser->name ?? 'User');

    $avatarUrl = !empty($authUser?->avatar_path) ? asset('storage/' . $authUser->avatar_path) : null;

    $initials = collect(explode(' ', $profileName))
        ->filter()
        ->map(fn(string $part): string => strtoupper(substr($part, 0, 1)))
        ->take(2)
        ->implode('');
    $initials = $initials !== '' ? $initials : 'U';

    $menuGroups = [
        [
            'label' => 'Main',
            'items' => [
                [
                    'key' => 'dashboard',
                    'label' => 'Dashboard',
                    'route' => route('admin.index'),
                    'icon' =>
                        '<path stroke-linecap="round" stroke-linejoin="round" d="m3.75 3.75 7.5 6 7.5-6v15a1.5 1.5 0 0 1-1.5 1.5h-3.75V13.5h-4.5v6.75H5.25a1.5 1.5 0 0 1-1.5-1.5v-15Z" />',
                ],
                [
                    'key' => 'reports',
                    'label' => 'Reports',
                    'route' => route('admin.reports'),
                    'icon' =>
                        '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 19.5h16.5M7.5 16.5V9.75m4.5 6.75V6.75m4.5 9.75v-3.75" />',
                ],
                [
                    'key' => 'inventory',
                    'label' => 'Inventory',
                    'route' => route('admin.inventory.index'),
                    'icon' =>
                        '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 7.5h16.5m-13.5 4.5h10.5m-10.5 4.5h6m6.75-12.75h-15a1.5 1.5 0 0 0-1.5 1.5v13.5a1.5 1.5 0 0 0 1.5 1.5h15a1.5 1.5 0 0 0 1.5-1.5V5.25a1.5 1.5 0 0 0-1.5-1.5Z" />',
                ],
            ],
        ],
        [
            'label' => 'Management',
            'items' => [
                [
                    'key' => 'products',
                    'label' => 'Products',
                    'route' => route('admin.products.index'),
                    'icon' =>
                        '<path stroke-linecap="round" stroke-linejoin="round" d="M5.25 4.5h13.5A1.5 1.5 0 0 1 20.25 6v12a1.5 1.5 0 0 1-1.5 1.5H5.25A1.5 1.5 0 0 1 3.75 18V6a1.5 1.5 0 0 1 1.5-1.5Zm3 3h7.5m-7.5 4.5h7.5m-7.5 4.5h4.5" />',
                ],
                [
                    'key' => 'categories',
                    'label' => 'Categories',
                    'route' => route('admin.categories.index'),
                    'icon' =>
                        '<path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75h10.5M6.75 12h10.5M6.75 17.25h10.5" />',
                ],
                [
                    'key' => 'users',
                    'label' => 'Users',
                    'route' => route('admin.users.index'),
                    'icon' =>
                        '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6.75a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 19.125a7.125 7.125 0 0 1 15 0" />',
                ],
            ],
        ],
        [
            'label' => 'System',
            'items' => [
                [
                    'key' => 'settings',
                    'label' => 'Settings',
                    'route' => route('admin.settings.index'),
                    'icon' =>
                        '<path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h3m-7.348 1.652 2.121 2.121m7.454 7.454 2.121 2.121M6 10.5v3m12-3v3m-1.652-7.348-2.121 2.121m-7.454 7.454-2.121 2.121M12 8.25A3.75 3.75 0 1 1 12 15.75 3.75 3.75 0 0 1 12 8.25Z" />',
                ],
            ],
        ],
    ];
@endphp

<button type="button" data-admin-sidebar-open aria-label="Open sidebar"
    class="fixed left-4 top-4 z-40 inline-flex items-center gap-2 rounded-2xl border border-[#ead8cb] bg-white/95 px-4 py-2.5 text-sm font-semibold text-[#5d4438] shadow-lg shadow-[#2f241f]/10 backdrop-blur transition lg:hidden">
    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
        stroke-width="1.9">
        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5m-16.5 5.25h16.5m-16.5 5.25h16.5" />
    </svg>
    Menu
</button>

<div data-admin-sidebar-overlay
    class="fixed inset-0 z-40 hidden bg-[#1a120e]/60 opacity-0 backdrop-blur-[2px] transition-opacity duration-300 lg:hidden">
</div>

<aside data-admin-sidebar
    class="fixed inset-y-0 left-0 z-50 flex w-[86vw] max-w-82.5 -translate-x-full flex-col overflow-hidden bg-linear-to-b from-[#2f241f] via-[#2a211d] to-[#241c18] text-white shadow-2xl transition-transform duration-300 ease-out lg:sticky lg:top-0 lg:z-20 lg:col-span-3 lg:h-screen lg:w-auto lg:max-w-none lg:translate-x-0 xl:col-span-2">
    <div class="flex h-full min-h-0 flex-col">
        <div class="border-b border-white/10 px-6 pb-5 pt-6">
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-center gap-3">
                    <span
                        class="flex h-12 w-12 items-center justify-center rounded-2xl bg-linear-to-br from-[#f4a06b] to-[#df7e43] shadow-lg shadow-[#f4a06b]/25">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.9">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M8.25 7.5h9a2.25 2.25 0 0 1 2.25 2.25V12a3 3 0 0 1-3 3H8.25m0-7.5v7.5m0-7.5H6A2.25 2.25 0 0 0 3.75 9.75V12A3 3 0 0 0 6.75 15h1.5m0 0V18m4.5-3v3m4.5-3v3" />
                        </svg>
                    </span>

                    <div>
                        <p class="text-lg font-black tracking-wide">Purr's Coffee</p>
                        <p class="text-xs text-white/60">Admin Workspace</p>
                    </div>
                </div>

                <button type="button" data-admin-sidebar-close aria-label="Close sidebar"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-white/10 text-white/70 transition hover:text-white lg:hidden">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="1.9">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <div data-admin-sidebar-scroll class="admin-sidebar-scroll flex-1 min-h-0 overflow-y-auto overflow-x-hidden px-4 py-5">
            @foreach ($menuGroups as $group)
                <div class="{{ !$loop->first ? 'mt-6' : '' }}">
                    <p class="px-3 text-[11px] font-bold uppercase tracking-[0.2em] text-white/35">
                        {{ $group['label'] }}
                    </p>

                    <nav class="mt-3 space-y-1.5">
                        @foreach ($group['items'] as $item)
                            @php
                                $isActive = $activeAdminMenu === $item['key'];
                            @endphp

                            <a href="{{ $item['route'] }}" data-admin-sidebar-close @class([
                                'group relative flex items-center gap-3 overflow-hidden rounded-2xl px-4 py-3 text-sm font-medium transition-all duration-200',
                                'text-white' => $isActive,
                                'text-white/75 hover:text-white' => !$isActive,
                            ])>
                                @if ($isActive)
                                    <span class="absolute inset-y-2 left-0 w-1 rounded-r-full bg-[#f4a06b]"></span>
                                @endif

                                <span @class([
                                    'flex h-10 w-10 items-center justify-center rounded-xl border transition',
                                    'border-[#f6d3bf] text-[#f4a06b]' => $isActive,
                                    'border-white/10 text-white/70 group-hover:text-white' => !$isActive,
                                ])>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                                        {!! $item['icon'] !!}
                                    </svg>
                                </span>

                                <span class="flex-1">{{ $item['label'] }}</span>

                                @if ($isActive)
                                    <span class="h-2.5 w-2.5 rounded-full bg-[#f4a06b]"></span>
                                @endif
                            </a>
                        @endforeach
                    </nav>
                </div>
            @endforeach
        </div>

        <div class="border-t border-white/10 p-4">
            <div class="rounded-3xl border border-white/10 bg-white/5 p-4 backdrop-blur">
                <div class="flex items-center gap-3">
                    <div
                        class="flex h-14 w-14 items-center justify-center overflow-hidden rounded-full border-4 border-white/40 bg-white/20 shadow-lg">
                        @if ($avatarUrl)
                            <img src="{{ $avatarUrl }}" alt="Profile avatar" class="h-full w-full object-cover">
                        @else
                            <span class="text-lg font-black text-white">{{ $initials }}</span>
                        @endif
                    </div>

                    <div class="min-w-0">
                        <p class="truncate text-sm font-semibold text-white">{{ $profileName }}</p>
                        <p class="truncate text-xs text-white/55">{{ $authUser->email ?? '-' }}</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('logout') }}" class="mt-4">
                    @csrf
                    <button type="submit" data-admin-sidebar-close
                        class="inline-flex w-full items-center justify-center gap-2 rounded-2xl border border-white/15 bg-white/5 px-4 py-2.5 text-sm font-semibold text-white/80 transition hover:text-white">
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
</aside>
@vite('resources/js/app.js')
