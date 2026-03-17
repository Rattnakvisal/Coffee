@extends('layouts.app')

@section('content')
<div class="anim-enter-up mx-auto w-full max-w-[1500px] overflow-hidden rounded-[32px] border border-white/60 bg-white/85 shadow-2xl shadow-[#bc7f54]/20">
    <div class="grid min-h-[85vh] grid-cols-1 lg:grid-cols-12">

        <aside class="anim-enter-left lg:col-span-3 xl:col-span-2 border-r border-[#f0e3da] bg-[#fffaf6] p-6">
            <div>
                <div class="flex items-center gap-3">
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-[#f4a06b] text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 7.5h9a2.25 2.25 0 0 1 2.25 2.25V12a3 3 0 0 1-3 3H8.25m0-7.5v7.5m0-7.5H6A2.25 2.25 0 0 0 3.75 9.75V12A3 3 0 0 0 6.75 15h1.5m0 0V18m4.5-3v3m4.5-3v3" />
                        </svg>
                    </span>
                    <div>
                        <p class="text-lg font-black text-[#2f241f]">Purr's Coffee</p>
                        <p class="text-xs text-[#8b6a59]">Cashier Workspace</p>
                    </div>
                </div>

                <nav class="mt-8 space-y-2 text-[#4f3b31]">
                    <a href="{{ route('cashier.index') }}" class="flex items-center gap-3 rounded-xl bg-[#fff1e8] px-4 py-3 font-semibold text-[#c56d39] ring-1 ring-[#f6d7c2]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955a1.125 1.125 0 0 1 1.59 0L21.75 12M4.5 9.75V19.5A2.25 2.25 0 0 0 6.75 21.75h3.75v-6h3v6h3.75a2.25 2.25 0 0 0 2.25-2.25V9.75" />
                        </svg>
                        Home page
                    </a>
                    <a href="#" class="flex items-center gap-3 rounded-xl px-4 py-3 transition hover:bg-[#f8ede6]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 5.25h16.5m-16.5 4.5h16.5m-16.5 4.5h16.5m-16.5 4.5h16.5" />
                        </svg>
                        Menu
                    </a>
                    <a href="#" class="flex items-center gap-3 rounded-xl px-4 py-3 transition hover:bg-[#f8ede6]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 6.75A2.25 2.25 0 0 1 5.25 4.5h13.5A2.25 2.25 0 0 1 21 6.75v10.5A2.25 2.25 0 0 1 18.75 19.5H5.25A2.25 2.25 0 0 1 3 17.25V6.75Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 9.75h9m-9 4.5h6" />
                        </svg>
                        My orders
                    </a>
                    <a href="#" class="flex items-center gap-3 rounded-xl px-4 py-3 transition hover:bg-[#f8ede6]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2.25M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                        History
                    </a>
                </nav>

                <div class="mt-8 space-y-2 border-t border-[#f0e3da] pt-6 text-[#4f3b31]">
                    <a href="#" class="flex items-center gap-3 rounded-xl px-4 py-3 transition hover:bg-[#f8ede6]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9A2.25 2.25 0 0 1 5.25 16.5v-9A2.25 2.25 0 0 1 7.5 5.25h9A2.25 2.25 0 0 1 18.75 7.5v9A2.25 2.25 0 0 1 16.5 18.75Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 9.75h9m-9 4.5h6" />
                        </svg>
                        Partners
                    </a>
                    <a href="#" class="flex items-center gap-3 rounded-xl px-4 py-3 transition hover:bg-[#f8ede6]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.592c.55 0 1.02.398 1.11.94l.213 1.278a1.125 1.125 0 0 0 .846.894l1.251.313c.534.133.878.657.813 1.203l-.153 1.288a1.125 1.125 0 0 0 .323.939l.925.926c.39.39.39 1.024 0 1.414l-.925.926a1.125 1.125 0 0 0-.323.938l.153 1.29c.065.545-.279 1.07-.813 1.202l-1.251.313a1.125 1.125 0 0 0-.846.894l-.213 1.278c-.09.542-.56.94-1.11.94h-2.592c-.55 0-1.02-.398-1.11-.94l-.213-1.278a1.125 1.125 0 0 0-.846-.894l-1.251-.313a1.125 1.125 0 0 1-.813-1.203l.153-1.288a1.125 1.125 0 0 0-.323-.939l-.925-.926a1 1 0 0 1 0-1.414l.925-.926a1.125 1.125 0 0 0 .323-.938l-.153-1.29a1.125 1.125 0 0 1 .813-1.202l1.251-.313a1.125 1.125 0 0 0 .846-.894l.213-1.278Z" />
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
                    <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-xl px-4 py-3 text-sm font-medium text-[#7a5c4e] transition hover:bg-[#f8ede6]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-7.5a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 6 21h7.5a2.25 2.25 0 0 0 2.25-2.25V15m5.25-3H9.75m0 0 3-3m-3 3 3 3" />
                        </svg>
                        Log out
                    </button>
                </form>
            </div>
        </aside>

        <main class="anim-enter-up anim-delay-100 lg:col-span-6 xl:col-span-7 bg-[#f8f8f8] p-6">
            <div class="flex flex-wrap items-center gap-3">
                <div class="relative min-w-[240px] flex-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35m1.35-5.4a7.5 7.5 0 1 1-15 0 7.5 7.5 0 0 1 15 0Z" />
                    </svg>
                    <input
                        type="text"
                        placeholder="Search menu..."
                        class="w-full rounded-2xl border border-gray-200 bg-white py-3 pl-12 pr-4 outline-none transition focus:border-[#f4a06b] focus:ring-2 focus:ring-[#f4a06b]/25"
                    >
                </div>
                <button class="inline-flex items-center gap-2 rounded-2xl bg-[#f4a06b] px-5 py-3 font-semibold text-white shadow-lg shadow-[#e8b28f] transition hover:brightness-105">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Filter
                </button>
            </div>

            <div class="mt-6 flex flex-wrap gap-3">
                <button class="rounded-full bg-[#f4a06b] px-5 py-2 text-sm font-semibold text-white shadow">Coffee</button>
                <button class="rounded-full border border-gray-300 bg-white px-5 py-2 text-sm">Non Coffee</button>
                <button class="rounded-full border border-gray-300 bg-white px-5 py-2 text-sm">Food</button>
                <button class="rounded-full border border-gray-300 bg-white px-5 py-2 text-sm">Snack</button>
                <button class="rounded-full border border-gray-300 bg-white px-5 py-2 text-sm">Dessert</button>
            </div>

            <div class="mt-8 flex items-end justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.12em] text-[#b16231]">Cashier</p>
                    <h2 class="mt-1 text-3xl font-black text-[#2f241f]">Coffee Menu</h2>
                </div>
                <button class="rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-[#5c4438] transition hover:bg-[#fff8f2]">View all items</button>
            </div>

            <div class="mt-6 grid grid-cols-1 gap-5 xl:grid-cols-2">
                @foreach ([
                    ['name' => 'Cappuccino', 'price' => '4.98'],
                    ['name' => 'Coffee Latte', 'price' => '5.98'],
                    ['name' => 'Americano', 'price' => '5.98'],
                    ['name' => 'V60', 'price' => '5.98'],
                ] as $item)
                    <div class="rounded-3xl bg-white p-5 shadow-sm ring-1 ring-black/5 anim-pop anim-stagger" style="--stagger: {{ $loop->index + 2 }};">
                        <div class="flex gap-4">
                            <div class="flex h-28 w-24 items-center justify-center rounded-2xl bg-[#fff4ec] text-[#d97f46]">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 7.5h9a2.25 2.25 0 0 1 2.25 2.25V12a3 3 0 0 1-3 3H8.25m0-7.5v7.5m0-7.5H6A2.25 2.25 0 0 0 3.75 9.75V12A3 3 0 0 0 6.75 15h1.5m0 0V18m4.5-3v3m4.5-3v3" />
                                </svg>
                            </div>

                            <div class="flex-1">
                                <div class="flex items-start justify-between gap-2">
                                    <h3 class="text-lg font-bold text-[#2f241f]">{{ $item['name'] }}</h3>
                                    <span class="font-bold text-[#d97f46]">${{ $item['price'] }}</span>
                                </div>

                                <p class="mt-2 text-sm text-gray-500">Freshly brewed and perfect for quick customer orders.</p>

                                <div class="mt-4 flex items-center gap-2 text-xs">
                                    <span class="font-semibold uppercase tracking-[0.1em] text-gray-500">Size</span>
                                    <button class="rounded-full bg-[#2f241f] px-3 py-1 text-white">Small</button>
                                    <button class="rounded-full border border-gray-300 px-3 py-1">Large</button>
                                </div>

                                <div class="mt-4 flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <button class="flex h-8 w-8 items-center justify-center rounded-full border border-gray-300 text-base">-</button>
                                        <span class="text-sm font-medium">1</span>
                                        <button class="flex h-8 w-8 items-center justify-center rounded-full border border-gray-300 text-base">+</button>
                                    </div>

                                    <button class="rounded-full bg-[#f4a06b] px-5 py-2 text-sm font-semibold text-white transition hover:brightness-105">
                                        Add to cart
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </main>

        <aside class="anim-enter-right lg:col-span-3 border-l border-[#f0e3da] bg-[#fffaf6] p-6">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h3 class="text-lg font-bold text-[#2f241f]">Cart</h3>
                    <p class="text-sm text-gray-400">Order #3243</p>
                </div>
                <div class="text-right">
                    <p class="font-semibold text-[#2f241f]">Albert Flores</p>
                    <p class="text-xs text-gray-400">purrcof@gmail.com</p>
                </div>
            </div>

            <div class="mt-6 flex flex-wrap gap-2 text-sm">
                <button class="rounded-full bg-[#2f241f] px-4 py-2 font-medium text-white">Delivery</button>
                <button class="rounded-full border border-gray-300 bg-white px-4 py-2">Dine in</button>
                <button class="rounded-full border border-gray-300 bg-white px-4 py-2">Take away</button>
            </div>

            <div class="mt-7 space-y-4">
                @foreach ([
                    ['name' => 'Cappuccino', 'price' => '14.94', 'qty' => 3],
                    ['name' => 'Coffee Latte', 'price' => '5.98', 'qty' => 1],
                ] as $cart)
                    <div class="flex items-center gap-3 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-black/5 anim-pop anim-stagger" style="--stagger: {{ $loop->index + 3 }};">
                        <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-[#fff4ec] text-[#d97f46]">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 7.5h9a2.25 2.25 0 0 1 2.25 2.25V12a3 3 0 0 1-3 3H8.25m0-7.5v7.5m0-7.5H6A2.25 2.25 0 0 0 3.75 9.75V12A3 3 0 0 0 6.75 15h1.5m0 0V18m4.5-3v3m4.5-3v3" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-[#2f241f]">{{ $cart['name'] }}</h4>
                            <p class="text-xs text-gray-400">Small - 200g</p>
                            <div class="mt-2 flex items-center justify-between">
                                <span class="font-semibold text-[#2f241f]">${{ $cart['price'] }}</span>
                                <div class="flex items-center gap-2">
                                    <button class="flex h-7 w-7 items-center justify-center rounded-full border border-gray-300">-</button>
                                    <span class="text-sm">{{ $cart['qty'] }}</span>
                                    <button class="flex h-7 w-7 items-center justify-center rounded-full border border-gray-300">+</button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-7 space-y-3 border-t border-[#f0e3da] pt-6 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">Items</span>
                    <span>$20.92</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Discount</span>
                    <span>-$3.00</span>
                </div>
                <div class="flex justify-between text-lg font-bold">
                    <span>Total</span>
                    <span class="text-[#d97f46]">$17.92</span>
                </div>
            </div>

            <button class="anim-pop anim-delay-400 mt-7 inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-[#f4a06b] py-4 font-semibold text-white shadow-lg shadow-[#e8b28f] transition hover:brightness-105">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5m-15 0 1.5 11.25A2.25 2.25 0 0 0 9 20.25h6a2.25 2.25 0 0 0 2.25-2.25l1.5-11.25M9.75 6.75v-1.5A2.25 2.25 0 0 1 12 3h0a2.25 2.25 0 0 1 2.25 2.25v1.5" />
                </svg>
                Place an order
            </button>
        </aside>
    </div>
</div>
@endsection
