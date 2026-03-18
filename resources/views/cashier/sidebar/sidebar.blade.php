@php
    $activeCashierMenu = $activeCashierMenu ?? 'home';
@endphp

<aside data-cashier-menu
    class="anim-enter-left -translate-x-full overflow-y-auto border-r border-[#f0e3da] bg-[#fffaf6] p-6 transition-transform duration-300 ease-out max-lg:fixed max-lg:inset-y-0 max-lg:left-0 max-lg:z-50 max-lg:w-[82vw] max-lg:max-w-[320px] max-lg:shadow-2xl lg:sticky lg:top-0 lg:z-20 lg:col-span-3 lg:h-screen lg:max-h-screen lg:w-auto lg:max-w-none lg:translate-x-0 lg:self-start lg:overflow-y-auto lg:shadow-none xl:col-span-2">
    <div class="anim-enter-left flex h-full flex-col">
        <div>
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
                        <p class="text-lg font-black text-[#2f241f]">Purr's Coffee</p>
                        <p class="text-xs text-[#8b6a59]">Cashier Workspace</p>
                    </div>
                </div>
                <button type="button" data-cashier-close
                    class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-[#ead7ca] text-[#7f6456] transition hover:bg-[#f8ede6] lg:hidden">
                    <span class="sr-only">Close menu</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="1.9">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <nav class="mt-8 space-y-2 text-[#4f3b31]">
                <a href="{{ route('cashier.index') }}" @class([
                    'flex items-center gap-3 rounded-xl px-4 py-3 transition',
                    'bg-[#fff1e8] font-semibold text-[#c56d39] ring-1 ring-[#f6d7c2]' =>
                        $activeCashierMenu === 'home',
                    'hover:bg-[#f8ede6]' => $activeCashierMenu !== 'home',
                ])>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="1.9">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="m2.25 12 8.954-8.955a1.125 1.125 0 0 1 1.59 0L21.75 12M4.5 9.75V19.5A2.25 2.25 0 0 0 6.75 21.75h3.75v-6h3v6h3.75a2.25 2.25 0 0 0 2.25-2.25V9.75" />
                    </svg>
                    Home page
                </a>
                <a href="{{ route('cashier.history') }}" @class([
                    'flex items-center gap-3 rounded-xl px-4 py-3 transition',
                    'bg-[#fff1e8] font-semibold text-[#c56d39] ring-1 ring-[#f6d7c2]' =>
                        $activeCashierMenu === 'history',
                    'hover:bg-[#f8ede6]' => $activeCashierMenu !== 'history',
                ])>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="1.9">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 6v6l4 2.25M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    History
                </a>
            </nav>

            <div class="mt-8 space-y-2 border-t border-[#f0e3da] pt-6 text-[#4f3b31]">
                <a href="#" class="flex items-center gap-3 rounded-xl px-4 py-3 transition hover:bg-[#f8ede6]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="1.9">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M16.5 18.75h-9A2.25 2.25 0 0 1 5.25 16.5v-9A2.25 2.25 0 0 1 7.5 5.25h9A2.25 2.25 0 0 1 18.75 7.5v9A2.25 2.25 0 0 1 16.5 18.75Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 9.75h9m-9 4.5h6" />
                    </svg>
                    Partners
                </a>
                <a href="#" class="flex items-center gap-3 rounded-xl px-4 py-3 transition hover:bg-[#f8ede6]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="1.9">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.592c.55 0 1.02.398 1.11.94l.213 1.278a1.125 1.125 0 0 0 .846.894l1.251.313c.534.133.878.657.813 1.203l-.153 1.288a1.125 1.125 0 0 0 .323.939l.925.926c.39.39.39 1.024 0 1.414l-.925.926a1.125 1.125 0 0 0-.323.938l.153 1.29c.065.545-.279 1.07-.813 1.202l-1.251.313a1.125 1.125 0 0 0-.846.894l-.213 1.278c-.09.542-.56.94-1.11.94h-2.592c-.55 0-1.02-.398-1.11-.94l-.213-1.278a1.125 1.125 0 0 0-.846-.894l-1.251-.313a1.125 1.125 0 0 1-.813-1.203l.153-1.288a1.125 1.125 0 0 0-.323-.939l-.925-.926a1 1 0 0 1 0-1.414l.925-.926a1.125 1.125 0 0 0 .323-.938l-.153-1.29a1.125 1.125 0 0 1 .813-1.202l1.251-.313a1.125 1.125 0 0 0 .846-.894l.213-1.278Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    </svg>
                    Settings
                </a>
            </div>
        </div>

        <div class="mt-8 rounded-2xl border border-[#f0d4c2] bg-white p-4">
            <p class="text-sm font-semibold text-[#2f241f]">{{ auth()->user()->name }}</p>
            <p class="mt-1 text-xs text-[#8b6a59]">{{ auth()->user()->email }}</p>

            <form method="POST" action="{{ route('logout') }}" class="mt-4">
                @csrf
                <button type="submit"
                    class="flex w-full items-center justify-center gap-2 rounded-xl px-4 py-3 text-sm font-medium text-[#7a5c4e] transition hover:bg-[#f8ede6]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="1.9">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-7.5a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 6 21h7.5a2.25 2.25 0 0 0 2.25-2.25V15m5.25-3H9.75m0 0 3-3m-3 3 3 3" />
                    </svg>
                    Log out
                </button>
            </form>
        </div>
</aside>
