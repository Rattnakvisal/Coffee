@extends('layouts.app')

@section('content')
@php
    $currentUser = auth()->user();
    $initials = collect(explode(' ', $currentUser->name))
        ->filter()
        ->map(fn (string $namePart): string => strtoupper(substr($namePart, 0, 1)))
        ->take(2)
        ->implode('');
@endphp
<div class="anim-enter-up mx-auto w-full max-w-[1500px] overflow-hidden rounded-[32px] border border-white/60 bg-white/85 shadow-2xl shadow-[#bc7f54]/20">
    <div class="grid min-h-[85vh] grid-cols-1 lg:grid-cols-12">

        <aside class="anim-enter-left lg:col-span-3 xl:col-span-2 bg-[#2f241f] p-6 text-white">
            <div class="flex items-center gap-3">
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-[#f4a06b] text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 7.5h9a2.25 2.25 0 0 1 2.25 2.25V12a3 3 0 0 1-3 3H8.25m0-7.5v7.5m0-7.5H6A2.25 2.25 0 0 0 3.75 9.75V12A3 3 0 0 0 6.75 15h1.5m0 0V18m4.5-3v3m4.5-3v3" />
                    </svg>
                </span>
                <div>
                    <p class="text-lg font-black">Purr's Coffee</p>
                    <p class="text-xs text-white/60">Admin Workspace</p>
                </div>
            </div>

            <nav class="mt-8 space-y-2">
                <a href="{{ route('admin.index') }}" class="flex items-center gap-3 rounded-xl bg-[#f4a06b] px-4 py-3 font-medium text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955a1.125 1.125 0 0 1 1.59 0L21.75 12M4.5 9.75V19.5A2.25 2.25 0 0 0 6.75 21.75h3.75v-6h3v6h3.75a2.25 2.25 0 0 0 2.25-2.25V9.75" />
                    </svg>
                    Dashboard
                </a>
                <a href="#" class="flex items-center gap-3 rounded-xl px-4 py-3 text-white/80 transition hover:bg-white/10 hover:text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 7.5h10.5m-10.5 4.5h10.5m-10.5 4.5h6.75M3.75 5.25A1.5 1.5 0 0 1 5.25 3.75h13.5a1.5 1.5 0 0 1 1.5 1.5v13.5a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5V5.25Z" />
                    </svg>
                    Products
                </a>
                <a href="#" class="flex items-center gap-3 rounded-xl px-4 py-3 text-white/80 transition hover:bg-white/10 hover:text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 7.5h18m-18 6h18m-18 6h18" />
                    </svg>
                    Categories
                </a>
                <a href="#" class="flex items-center gap-3 rounded-xl px-4 py-3 text-white/80 transition hover:bg-white/10 hover:text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M6.75 2.25v3m10.5-3v3m-12 16.5h13.5A2.25 2.25 0 0 0 21 19.5v-12A2.25 2.25 0 0 0 18.75 5.25H5.25A2.25 2.25 0 0 0 3 7.5v12a2.25 2.25 0 0 0 2.25 2.25Z" />
                    </svg>
                    Orders
                </a>
                <a href="#" class="flex items-center gap-3 rounded-xl px-4 py-3 text-white/80 transition hover:bg-white/10 hover:text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 3v18h18M7.5 14.25 10.5 11l3 2.25 4.5-6" />
                    </svg>
                    Reports
                </a>
                <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-white/80 transition hover:bg-white/10 hover:text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.75a3.75 3.75 0 1 0-7.5 0m7.5 0v.75h1.5a2.25 2.25 0 0 0 2.25-2.25v-.824a2.25 2.25 0 0 0-.663-1.588l-1.02-1.021a2.25 2.25 0 0 1-.659-1.591V8.25A6.75 6.75 0 0 0 6 8.25v4.976c0 .597-.237 1.169-.659 1.591l-1.02 1.02a2.25 2.25 0 0 0-.663 1.59v.824A2.25 2.25 0 0 0 5.908 20.5h1.5v-.75m10.592-1.5a6 6 0 0 0-12 0" />
                    </svg>
                    Users
                </a>
                <a href="#" class="flex items-center gap-3 rounded-xl px-4 py-3 text-white/80 transition hover:bg-white/10 hover:text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.592c.55 0 1.02.398 1.11.94l.213 1.278a1.125 1.125 0 0 0 .846.894l1.251.313c.534.133.878.657.813 1.203l-.153 1.288a1.125 1.125 0 0 0 .323.939l.925.926c.39.39.39 1.024 0 1.414l-.925.926a1.125 1.125 0 0 0-.323.938l.153 1.29c.065.545-.279 1.07-.813 1.202l-1.251.313a1.125 1.125 0 0 0-.846.894l-.213 1.278c-.09.542-.56.94-1.11.94h-2.592c-.55 0-1.02-.398-1.11-.94l-.213-1.278a1.125 1.125 0 0 0-.846-.894l-1.251-.313a1.125 1.125 0 0 1-.813-1.203l.153-1.288a1.125 1.125 0 0 0-.323-.939l-.925-.926a1 1 0 0 1 0-1.414l.925-.926a1.125 1.125 0 0 0 .323-.938l-.153-1.29a1.125 1.125 0 0 1 .813-1.202l1.251-.313a1.125 1.125 0 0 0 .846-.894l.213-1.278Z" />
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
                    <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-white/25 px-4 py-2 text-sm font-medium text-white/80 transition hover:bg-white/10 hover:text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-7.5a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 6 21h7.5a2.25 2.25 0 0 0 2.25-2.25V15m5.25-3H9.75m0 0 3-3m-3 3 3 3" />
                        </svg>
                        Log out
                    </button>
                </form>
            </div>
        </aside>

        <main class="anim-enter-right lg:col-span-9 xl:col-span-10 bg-[#f8f8f8] p-6 lg:p-8">
            <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
                <form action="#" method="GET" class="relative w-full max-w-xl">
                    <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35m1.35-5.4a7.5 7.5 0 1 1-15 0 7.5 7.5 0 0 1 15 0Z" />
                        </svg>
                    </span>
                    <input
                        type="text"
                        name="q"
                        placeholder="Search products, orders, reports..."
                        class="w-full rounded-2xl border border-slate-200 bg-white py-3 pl-12 pr-4 text-sm outline-none transition focus:border-[#f4a06b] focus:ring-2 focus:ring-[#f4a06b]/20"
                    >
                </form>

                <div class="flex items-center gap-3">
                    <div class="relative" data-dropdown>
                        <button
                            type="button"
                            data-dropdown-trigger
                            aria-expanded="false"
                            class="relative inline-flex h-12 w-12 items-center justify-center rounded-xl border border-slate-200 bg-white text-[#5d4438] transition hover:border-[#f4a06b] hover:text-[#d97f46]"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.08 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                            </svg>
                            <span class="absolute -right-1 -top-1 inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-[#f4a06b] px-1 text-xs font-bold text-white">3</span>
                        </button>

                        <div data-dropdown-menu class="absolute right-0 z-20 mt-3 hidden w-80 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl shadow-slate-200/60">
                            <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
                                <p class="text-sm font-semibold text-[#2f241f]">Notifications</p>
                                <button type="button" class="text-xs font-semibold text-[#d97f46] hover:underline">Mark all read</button>
                            </div>

                            <div class="max-h-80 overflow-y-auto">
                                @foreach ([
                                    ['title' => 'Low stock alert', 'description' => 'Arabica Beans left 6 packs', 'time' => '5m ago'],
                                    ['title' => 'New order paid', 'description' => 'Order #1045 by Evelyn - $22.40', 'time' => '16m ago'],
                                    ['title' => 'Shift check-in', 'description' => 'Cashier Maria checked in', 'time' => '1h ago'],
                                ] as $notification)
                                    <div class="border-b border-slate-100 px-4 py-3 last:border-b-0">
                                        <p class="text-sm font-semibold text-[#2f241f]">{{ $notification['title'] }}</p>
                                        <p class="mt-1 text-xs text-slate-500">{{ $notification['description'] }}</p>
                                        <p class="mt-2 text-[11px] font-medium uppercase tracking-wide text-[#b07a57]">{{ $notification['time'] }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="relative" data-dropdown>
                        <button
                            type="button"
                            data-dropdown-trigger
                            aria-expanded="false"
                            class="inline-flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-3 py-2 text-left transition hover:border-[#f4a06b]"
                        >
                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-[#2f241f] text-sm font-bold text-white">{{ $initials }}</span>
                            <span class="hidden min-w-0 sm:block">
                                <span class="block truncate text-sm font-semibold text-[#2f241f]">{{ $currentUser->name }}</span>
                                <span class="block truncate text-xs text-slate-500">{{ str($currentUser->role?->name ?? 'Admin')->headline() }}</span>
                            </span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                            </svg>
                        </button>

                        <div data-dropdown-menu class="absolute right-0 z-20 mt-3 hidden w-64 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl shadow-slate-200/60">
                            <div class="border-b border-slate-100 px-4 py-3">
                                <p class="text-sm font-semibold text-[#2f241f]">{{ $currentUser->name }}</p>
                                <p class="mt-0.5 text-xs text-slate-500">{{ $currentUser->email }}</p>
                            </div>

                            <div class="space-y-1 p-2 text-sm">
                                <a href="#" class="flex items-center gap-2 rounded-lg px-3 py-2 text-[#503a2f] transition hover:bg-[#fff3ea]">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275" />
                                    </svg>
                                    My profile
                                </a>
                                <a href="#" class="flex items-center gap-2 rounded-lg px-3 py-2 text-[#503a2f] transition hover:bg-[#fff3ea]">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.592c.55 0 1.02.398 1.11.94l.213 1.278a1.125 1.125 0 0 0 .846.894l1.251.313c.534.133.878.657.813 1.203l-.153 1.288a1.125 1.125 0 0 0 .323.939l.925.926c.39.39.39 1.024 0 1.414l-.925.926a1.125 1.125 0 0 0-.323.938l.153 1.29c.065.545-.279 1.07-.813 1.202l-1.251.313a1.125 1.125 0 0 0-.846.894l-.213 1.278c-.09.542-.56.94-1.11.94h-2.592c-.55 0-1.02-.398-1.11-.94l-.213-1.278a1.125 1.125 0 0 0-.846-.894l-1.251-.313a1.125 1.125 0 0 1-.813-1.203l.153-1.288a1.125 1.125 0 0 0-.323-.939l-.925-.926a1 1 0 0 1 0-1.414l.925-.926a1.125 1.125 0 0 0 .323-.938l-.153-1.29a1.125 1.125 0 0 1 .813-1.202l1.251-.313a1.125 1.125 0 0 0 .846-.894l.213-1.278Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    </svg>
                                    Account settings
                                </a>
                            </div>

                            <div class="border-t border-slate-100 p-2">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-[#9a4b35] transition hover:bg-[#fff3ea]">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-7.5a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 6 21h7.5a2.25 2.25 0 0 0 2.25-2.25V15m5.25-3H9.75m0 0 3-3m-3 3 3 3" />
                                        </svg>
                                        Log out
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="anim-enter-up anim-delay-100 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="inline-flex items-center gap-2 rounded-full bg-[#ffe7d5] px-3 py-1 text-xs font-semibold uppercase tracking-[0.12em] text-[#b16231]">
                        <span class="h-2 w-2 rounded-full bg-[#f4a06b]"></span>
                        Admin
                    </p>
                    <h2 class="mt-3 text-3xl font-black text-[#2f241f]">Dashboard Overview</h2>
                    <p class="mt-1 text-sm text-gray-500">Track sales, orders, and inventory performance in real time.</p>
                </div>

                <button class="anim-pop anim-delay-200 inline-flex items-center gap-2 rounded-xl bg-[#f4a06b] px-5 py-3 font-semibold text-white shadow-lg shadow-[#e9b08d] transition hover:brightness-105">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Add Product
                </button>
            </div>

            <div class="mt-8 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="anim-pop anim-delay-200 rounded-3xl bg-white p-5 shadow-sm ring-1 ring-black/5">
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-gray-500">Total Sales</p>
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#fff1e5] text-[#d97f46]">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m3-9H9.75a1.5 1.5 0 0 0 0 3h4.5a1.5 1.5 0 0 1 0 3H9m3 3v-1.5m0-12V6" />
                            </svg>
                        </span>
                    </div>
                    <h3 class="mt-3 text-3xl font-black text-[#2f241f]">$2,450</h3>
                    <p class="mt-2 text-xs font-medium text-emerald-600">+8.2% from last week</p>
                </div>

                <div class="anim-pop anim-delay-300 rounded-3xl bg-white p-5 shadow-sm ring-1 ring-black/5">
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-gray-500">Orders Today</p>
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#fff1e5] text-[#d97f46]">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 7.5h7.5m-7.5 4.5h7.5m-7.5 4.5h4.5M6 3.75h12A2.25 2.25 0 0 1 20.25 6v12A2.25 2.25 0 0 1 18 20.25H6A2.25 2.25 0 0 1 3.75 18V6A2.25 2.25 0 0 1 6 3.75Z" />
                            </svg>
                        </span>
                    </div>
                    <h3 class="mt-3 text-3xl font-black text-[#2f241f]">128</h3>
                    <p class="mt-2 text-xs font-medium text-emerald-600">+11 new since morning</p>
                </div>

                <div class="anim-pop anim-delay-400 rounded-3xl bg-white p-5 shadow-sm ring-1 ring-black/5">
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-gray-500">Products</p>
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#fff1e5] text-[#d97f46]">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5 12 3 3 7.5m18 0V16.5L12 21m9-13.5L12 12m0 9-9-4.5V7.5m9 4.5L3 7.5" />
                            </svg>
                        </span>
                    </div>
                    <h3 class="mt-3 text-3xl font-black text-[#2f241f]">42</h3>
                    <p class="mt-2 text-xs font-medium text-slate-500">6 low-stock items</p>
                </div>

                <div class="anim-pop anim-delay-500 rounded-3xl bg-white p-5 shadow-sm ring-1 ring-black/5">
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-gray-500">Cashiers</p>
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#fff1e5] text-[#d97f46]">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.75a3.75 3.75 0 1 0-7.5 0m7.5 0v.75h1.5a2.25 2.25 0 0 0 2.25-2.25v-.824a2.25 2.25 0 0 0-.663-1.588l-1.02-1.021a2.25 2.25 0 0 1-.659-1.591V8.25A6.75 6.75 0 0 0 6 8.25v4.976c0 .597-.237 1.169-.659 1.591l-1.02 1.02a2.25 2.25 0 0 0-.663 1.59v.824A2.25 2.25 0 0 0 5.908 20.5h1.5v-.75m10.592-1.5a6 6 0 0 0-12 0" />
                            </svg>
                        </span>
                    </div>
                    <h3 class="mt-3 text-3xl font-black text-[#2f241f]">6</h3>
                    <p class="mt-2 text-xs font-medium text-slate-500">All staff clocked in</p>
                </div>
            </div>

            <div class="mt-8 grid grid-cols-1 gap-6 xl:grid-cols-3">
                <div class="anim-enter-up anim-delay-300 xl:col-span-2 rounded-3xl bg-white p-6 shadow-sm ring-1 ring-black/5">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-xl font-bold text-[#2f241f]">Recent Orders</h3>
                        <button class="text-sm font-medium text-[#d97f46] hover:underline">View all</button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[520px] text-left">
                            <thead>
                                <tr class="border-b border-slate-200 text-sm text-gray-500">
                                    <th class="pb-3 font-medium">Order ID</th>
                                    <th class="pb-3 font-medium">Customer</th>
                                    <th class="pb-3 font-medium">Amount</th>
                                    <th class="pb-3 font-medium">Status</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm">
                                <tr class="border-b border-slate-100">
                                    <td class="py-4 font-semibold">#1001</td>
                                    <td>John Doe</td>
                                    <td>$12.50</td>
                                    <td><span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">Paid</span></td>
                                </tr>
                                <tr class="border-b border-slate-100">
                                    <td class="py-4 font-semibold">#1002</td>
                                    <td>Sophia</td>
                                    <td>$8.90</td>
                                    <td><span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">Pending</span></td>
                                </tr>
                                <tr>
                                    <td class="py-4 font-semibold">#1003</td>
                                    <td>Michael</td>
                                    <td>$15.75</td>
                                    <td><span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">Paid</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="anim-enter-up anim-delay-400 rounded-3xl bg-white p-6 shadow-sm ring-1 ring-black/5">
                    <h3 class="text-xl font-bold text-[#2f241f]">Top Products</h3>

                    <div class="mt-5 space-y-5 text-sm">
                        <div>
                            <div class="mb-2 flex items-center justify-between">
                                <span>Cappuccino</span>
                                <span class="font-semibold">120 sold</span>
                            </div>
                            <div class="h-2 rounded-full bg-slate-100">
                                <div class="h-2 w-[88%] rounded-full bg-[#f4a06b]"></div>
                            </div>
                        </div>
                        <div>
                            <div class="mb-2 flex items-center justify-between">
                                <span>Latte</span>
                                <span class="font-semibold">98 sold</span>
                            </div>
                            <div class="h-2 rounded-full bg-slate-100">
                                <div class="h-2 w-[74%] rounded-full bg-[#f4a06b]"></div>
                            </div>
                        </div>
                        <div>
                            <div class="mb-2 flex items-center justify-between">
                                <span>Americano</span>
                                <span class="font-semibold">76 sold</span>
                            </div>
                            <div class="h-2 rounded-full bg-slate-100">
                                <div class="h-2 w-[62%] rounded-full bg-[#f4a06b]"></div>
                            </div>
                        </div>
                        <div>
                            <div class="mb-2 flex items-center justify-between">
                                <span>Mocha</span>
                                <span class="font-semibold">65 sold</span>
                            </div>
                            <div class="h-2 rounded-full bg-slate-100">
                                <div class="h-2 w-[52%] rounded-full bg-[#f4a06b]"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
    (function () {
        const dropdownRoots = document.querySelectorAll('[data-dropdown]');

        const closeAllDropdowns = function () {
            dropdownRoots.forEach(function (root) {
                const trigger = root.querySelector('[data-dropdown-trigger]');
                const menu = root.querySelector('[data-dropdown-menu]');

                if (!trigger || !menu) return;

                trigger.setAttribute('aria-expanded', 'false');
                menu.classList.add('hidden');
            });
        };

        document.addEventListener('click', function (event) {
            const trigger = event.target.closest('[data-dropdown-trigger]');

            if (trigger) {
                const root = trigger.closest('[data-dropdown]');
                const menu = root ? root.querySelector('[data-dropdown-menu]') : null;

                if (!root || !menu) return;

                const isHidden = menu.classList.contains('hidden');

                closeAllDropdowns();

                if (isHidden) {
                    menu.classList.remove('hidden');
                    trigger.setAttribute('aria-expanded', 'true');
                }

                return;
            }

            if (!event.target.closest('[data-dropdown]')) {
                closeAllDropdowns();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeAllDropdowns();
            }
        });
    })();
</script>
@endsection
