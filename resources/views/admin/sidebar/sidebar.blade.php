@php
    $activeAdminMenu = $activeAdminMenu ?? 'dashboard';
@endphp

<button type="button" data-admin-sidebar-open
    class="fixed left-4 top-4 z-40 inline-flex items-center gap-2 rounded-xl border border-[#ecd9cc] bg-white px-3 py-2 text-sm font-semibold text-[#5d4438] shadow-sm transition hover:bg-[#fff6f0] lg:hidden">
    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
        stroke-width="1.9">
        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5m-16.5 5.25h16.5m-16.5 5.25h16.5" />
    </svg>
    Menu
</button>

<div data-admin-sidebar-overlay class="fixed inset-0 z-40 hidden bg-[#1f1713]/50 backdrop-blur-[1px] lg:hidden"></div>

<aside data-admin-sidebar
    class="fixed inset-y-0 left-0 z-50 w-[82vw] max-w-[320px] -translate-x-full overflow-y-auto bg-[#2f241f] p-6 text-white shadow-2xl transition-transform duration-300 ease-out lg:sticky lg:top-0 lg:z-20 lg:col-span-3 lg:h-screen lg:w-auto lg:max-w-none lg:translate-x-0 lg:self-start lg:overflow-visible xl:col-span-2">
    <div class="anim-enter-left flex h-full flex-col">
        <div class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-[#f4a06b] text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="1.9">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M8.25 7.5h9a2.25 2.25 0 0 1 2.25 2.25V12a3 3 0 0 1-3 3H8.25m0-7.5v7.5m0-7.5H6A2.25 2.25 0 0 0 3.75 9.75V12A3 3 0 0 0 6.75 15h1.5m0 0V18m4.5-3v3m4.5-3v3" />
                    </svg>
                </span>
                <div>
                    <p class="text-lg font-black">Purr's Coffee</p>
                    <p class="text-xs text-white/60">Admin Workspace</p>
                </div>
            </div>
            <button type="button" data-admin-sidebar-close
                class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-white/20 text-white/80 transition hover:bg-white/10 hover:text-white lg:hidden">
                <span class="sr-only">Close menu</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="1.9">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <nav data-sidebar-scroll class="mt-8 space-y-2 scroll-smooth lg:min-h-0 lg:flex-1 lg:overflow-y-auto lg:pr-1">
            <a href="{{ route('admin.index') }}" data-admin-sidebar-close
                @class([
                    'flex items-center gap-3 rounded-xl px-4 py-3 transition',
                    'bg-[#f4a06b] font-medium text-white' => $activeAdminMenu === 'dashboard',
                    'text-white/80 hover:bg-white/10 hover:text-white' => $activeAdminMenu !== 'dashboard',
                ])>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="1.9">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="m2.25 12 8.954-8.955a1.125 1.125 0 0 1 1.59 0L21.75 12M4.5 9.75V19.5A2.25 2.25 0 0 0 6.75 21.75h3.75v-6h3v6h3.75a2.25 2.25 0 0 0 2.25-2.25V9.75" />
                </svg>
                Dashboard
            </a>

            <a href="{{ route('admin.products.index') }}" data-admin-sidebar-close
                @class([
                    'flex items-center gap-3 rounded-xl px-4 py-3 transition',
                    'bg-[#f4a06b] font-medium text-white' => $activeAdminMenu === 'products',
                    'text-white/80 hover:bg-white/10 hover:text-white' => $activeAdminMenu !== 'products',
                ])>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="1.9">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M6.75 7.5h10.5m-10.5 4.5h10.5m-10.5 4.5h6.75M3.75 5.25A1.5 1.5 0 0 1 5.25 3.75h13.5a1.5 1.5 0 0 1 1.5 1.5v13.5a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5V5.25Z" />
                </svg>
                Products
            </a>

            <a href="{{ route('admin.categories.index') }}" data-admin-sidebar-close
                @class([
                    'flex items-center gap-3 rounded-xl px-4 py-3 transition',
                    'bg-[#f4a06b] font-medium text-white' => $activeAdminMenu === 'categories',
                    'text-white/80 hover:bg-white/10 hover:text-white' => $activeAdminMenu !== 'categories',
                ])>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="1.9">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M6.75 4.5h10.5M6.75 9.75h10.5m-10.5 5.25h10.5m-10.5 5.25h10.5" />
                </svg>
                Categories
            </a>

            <a href="{{ route('admin.users.index') }}" data-admin-sidebar-close
                @class([
                    'flex items-center gap-3 rounded-xl px-4 py-3 transition',
                    'bg-[#f4a06b] font-medium text-white' => $activeAdminMenu === 'users',
                    'text-white/80 hover:bg-white/10 hover:text-white' => $activeAdminMenu !== 'users',
                ])>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="1.9">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M18 18.75a3.75 3.75 0 1 0-7.5 0m7.5 0v.75h1.5a2.25 2.25 0 0 0 2.25-2.25v-.824a2.25 2.25 0 0 0-.663-1.588l-1.02-1.021a2.25 2.25 0 0 1-.659-1.591V8.25A6.75 6.75 0 0 0 6 8.25v4.976c0 .597-.237 1.169-.659 1.591l-1.02 1.02a2.25 2.25 0 0 0-.663 1.59v.824A2.25 2.25 0 0 0 5.908 20.5h1.5v-.75m10.592-1.5a6 6 0 0 0-12 0" />
                </svg>
                Users
            </a>

            <a href="{{ route('admin.settings.index') }}" data-admin-sidebar-close
                @class([
                    'flex items-center gap-3 rounded-xl px-4 py-3 transition',
                    'bg-[#f4a06b] font-medium text-white' => $activeAdminMenu === 'settings',
                    'text-white/80 hover:bg-white/10 hover:text-white' => $activeAdminMenu !== 'settings',
                ])>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="1.9">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.592c.55 0 1.02.398 1.11.94l.213 1.278a1.125 1.125 0 0 0 .846.894l1.251.313c.534.133.878.657.813 1.203l-.153 1.288a1.125 1.125 0 0 0 .323.939l.925.926c.39.39.39 1.024 0 1.414l-.925.926a1.125 1.125 0 0 0-.323.938l.153 1.29c.065.545-.279 1.07-.813 1.202l-1.251.313a1.125 1.125 0 0 0-.846.894l-.213 1.278c-.09.542-.56.94-1.11.94h-2.592c-.55 0-1.02-.398-1.11-.94l-.213-1.278a1.125 1.125 0 0 0-.846-.894l-1.251-.313a1.125 1.125 0 0 1-.813-1.203l.153-1.288a1.125 1.125 0 0 0-.323-.939l-.925-.926a1 1 0 0 1 0-1.414l.925-.926a1.125 1.125 0 0 0 .323-.938l-.153-1.29a1.125 1.125 0 0 1 .813-1.202l1.251-.313a1.125 1.125 0 0 0 .846-.894l.213-1.278Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                </svg>
                Settings
            </a>
        </nav>

        <div class="mt-8 rounded-2xl border border-white/15 bg-white/5 p-4">
            <p class="text-sm font-semibold text-white">{{ auth()->user()->name }}</p>
            <p class="mt-1 text-xs text-white/60">{{ auth()->user()->email }}</p>

            <form method="POST" action="{{ route('logout') }}" class="mt-4">
                @csrf
                <button type="submit" data-admin-sidebar-close
                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-white/25 px-4 py-2 text-sm font-medium text-white/80 transition hover:bg-white/10 hover:text-white">
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
</aside>

<script>
    (function() {
        const root = document.documentElement;
        const sidebar = document.querySelector('[data-admin-sidebar]');
        const overlay = document.querySelector('[data-admin-sidebar-overlay]');
        const openButtons = document.querySelectorAll('[data-admin-sidebar-open]');
        const closeButtons = document.querySelectorAll('[data-admin-sidebar-close]');

        if (!sidebar || !overlay) return;

        const openSidebar = function() {
            sidebar.classList.remove('-translate-x-full');
            overlay.classList.remove('hidden');
            root.classList.add('overflow-hidden');
        };

        const closeSidebar = function() {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
            root.classList.remove('overflow-hidden');
        };

        openButtons.forEach(function(button) {
            button.addEventListener('click', openSidebar);
        });

        closeButtons.forEach(function(button) {
            button.addEventListener('click', closeSidebar);
        });

        overlay.addEventListener('click', closeSidebar);

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeSidebar();
            }
        });

        const desktopMediaQuery = window.matchMedia('(min-width: 1024px)');
        const handleDesktopChange = function(event) {
            if (event.matches) {
                root.classList.remove('overflow-hidden');
                overlay.classList.add('hidden');
            } else {
                closeSidebar();
            }
        };

        handleDesktopChange(desktopMediaQuery);

        if (desktopMediaQuery.addEventListener) {
            desktopMediaQuery.addEventListener('change', handleDesktopChange);
        } else if (desktopMediaQuery.addListener) {
            desktopMediaQuery.addListener(handleDesktopChange);
        }
    })();
</script>
