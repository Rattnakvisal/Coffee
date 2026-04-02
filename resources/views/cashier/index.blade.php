@extends('layouts.app')

@section('content')
    @php
        $products = collect($products ?? []);
        $categories = collect($categories ?? []);
        $searchSuggestions = collect($searchSuggestions ?? []);
        $search = (string) ($search ?? '');
        $category = (string) ($category ?? '');
        $todayAttendance = $todayAttendance ?? null;
        $canWork = $todayAttendance !== null;
    @endphp
    <div class="anim-enter-up w-full min-h-screen overflow-hidden lg:overflow-visible bg-white/85">
        <div class="grid min-h-screen grid-cols-1 lg:grid-cols-12">
            <div data-cashier-overlay class="fixed inset-0 z-40 hidden bg-[#1f1713]/50 backdrop-blur-[1px] lg:hidden"></div>
            @include('cashier.sidebar.sidebar', ['activeCashierMenu' => 'home'])
            <main
                class="anim-enter-up anim-delay-100 bg-[#f8f8f8] p-4 pt-20 sm:p-6 sm:pt-20 lg:col-span-6 lg:p-6 lg:pt-6 xl:col-span-7">
                <div class="mb-4 flex items-center justify-between gap-2 lg:hidden">
                    <button type="button" data-cashier-open-menu
                        class="inline-flex items-center gap-2 rounded-xl border border-[#e9d8cc] bg-white px-3 py-2 text-sm font-semibold text-[#6d4e3f] shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.9">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3.75 6.75h16.5m-16.5 5.25h16.5m-16.5 5.25h16.5" />
                        </svg>
                        Menu
                    </button>
                    <button type="button" data-cashier-open-cart
                        class="inline-flex items-center gap-2 rounded-xl bg-[#f4a06b] px-3 py-2 text-sm font-semibold text-white shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.9">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3.75 6.75h16.5m-15 0 1.5 11.25A2.25 2.25 0 0 0 9 20.25h6a2.25 2.25 0 0 0 2.25-2.25l1.5-11.25M9.75 6.75v-1.5A2.25 2.25 0 0 1 12 3h0a2.25 2.25 0 0 1 2.25 2.25v1.5" />
                        </svg>
                        Cart
                    </button>
                </div>

                @if (session('status'))
                    <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->has('attendance'))
                    <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                        {{ $errors->first('attendance') }}
                    </div>
                @endif

                @unless ($canWork)
                    <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">
                        POS is locked until any cashier checks attendance.
                    </div>
                @endunless

                <div class="flex flex-wrap items-center gap-3">
                    <form method="GET" action="{{ route('cashier.index') }}" class="relative min-w-[240px] flex-1"
                        data-cashier-search-form data-cashier-search-suggestions='@json($searchSuggestions->values()->all())'>
                        <svg xmlns="http://www.w3.org/2000/svg"
                            class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="m21 21-4.35-4.35m1.35-5.4a7.5 7.5 0 1 1-15 0 7.5 7.5 0 0 1 15 0Z" />
                        </svg>
                        <input type="text" name="search" value="{{ $search }}" placeholder="Search menu..."
                            autocomplete="off" data-cashier-search-input
                            class="w-full rounded-2xl border border-gray-200 bg-white py-3 pl-12 pr-28 outline-none transition focus:border-[#f4a06b] focus:ring-2 focus:ring-[#f4a06b]/25">
                        <div class="absolute right-2 top-1/2 flex -translate-y-1/2 items-center gap-1">
                            @if ($search !== '')
                                <a href="{{ route('cashier.index') }}"
                                    class="rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-semibold text-slate-600 transition hover:bg-slate-100">Clear</a>
                            @endif
                            <button type="submit"
                                class="rounded-lg bg-[#f4a06b] px-3 py-1.5 text-xs font-semibold text-white">Search</button>
                        </div>
                        <div data-cashier-search-dropdown class="coffee-search-dropdown hidden">
                            <ul data-cashier-search-results role="listbox" class="max-h-72 overflow-y-auto py-1"></ul>
                            <p data-cashier-search-empty
                                class="hidden px-4 py-3 text-xs font-semibold uppercase tracking-[0.08em] text-[#9f7a63]">
                                No matching menu found
                            </p>
                        </div>
                    </form>
                </div>

                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="{{ route('cashier.index', array_filter(['search' => $search])) }}"
                        @class([
                            'rounded-full px-5 py-2 text-sm font-semibold shadow transition',
                            'bg-[#f4a06b] text-white' => $category === '',
                            'border border-gray-300 bg-white text-[#4f3b31]' => $category !== '',
                        ])>
                        All
                    </a>
                    @foreach ($categories as $categoryOption)
                        <a href="{{ route('cashier.index', array_filter(['search' => $search, 'category' => $categoryOption->slug])) }}"
                            @class([
                                'rounded-full px-5 py-2 text-sm font-semibold shadow transition',
                                'bg-[#f4a06b] text-white' => $category === $categoryOption->slug,
                                'border border-gray-300 bg-white text-[#4f3b31]' =>
                                    $category !== $categoryOption->slug,
                            ])>
                            {{ $categoryOption->name }}
                        </a>
                    @endforeach
                </div>

                <div class="mt-8 flex items-end justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.12em] text-[#b16231]">Cashier</p>
                        <h2 class="mt-1 text-3xl font-black text-[#2f241f]">Coffee Menu</h2>
                    </div>
                    <a href="{{ route('cashier.index') }}"
                        class="rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-[#5c4438] transition hover:bg-[#fff8f2]">
                        View all items
                    </a>
                </div>

                <div @class([
                    'mt-6 grid grid-cols-1 gap-5 xl:grid-cols-2',
                    'pointer-events-none opacity-60 select-none' => !$canWork,
                ])>
                    @forelse ($products as $item)
                        @php
                            $discountPercent = $item->normalizedDiscountPercent();
                            $smallBasePrice = $item->sizeBasePrice('small');
                            $mediumBasePrice = $item->sizeBasePrice('medium');
                            $largeBasePrice = $item->sizeBasePrice('large');
                            $smallPrice = $item->sizePrice('small');
                            $mediumPrice = $item->sizePrice('medium');
                            $largePrice = $item->sizePrice('large');
                            $allSizeOptions = collect([
                                [
                                    'key' => 'small',
                                    'label' => 'Small',
                                    'is_active' => $item->isSizeActive('small'),
                                ],
                                [
                                    'key' => 'medium',
                                    'label' => 'Medium',
                                    'is_active' => $item->isSizeActive('medium'),
                                ],
                                [
                                    'key' => 'large',
                                    'label' => 'Large',
                                    'is_active' => $item->isSizeActive('large'),
                                ],
                            ]);
                            $activeSizeOptions = $allSizeOptions
                                ->filter(fn(array $size): bool => (bool) $size['is_active'])
                                ->values();
                            $activeSizeKeys = $activeSizeOptions->pluck('key')->values()->all();
                            $hasActiveSizes = $activeSizeOptions->isNotEmpty();
                            $defaultSizeKey = (string) ($activeSizeOptions->first()['key'] ?? 'small');
                            $defaultSizeLabel = (string) ($activeSizeOptions->first()['label'] ?? 'Unavailable');
                            $defaultBasePrice = $hasActiveSizes ? $item->sizeBasePrice($defaultSizeKey) : 0;
                            $defaultPrice = $hasActiveSizes ? $item->sizePrice($defaultSizeKey) : 0;
                        @endphp
                        <div class="rounded-3xl bg-white p-5 shadow-sm ring-1 ring-black/5 anim-pop anim-stagger"
                            style="--stagger: {{ $loop->index + 2 }};">
                            <div class="flex gap-4">
                                @php
                                    $productImageUrl = $item->imageUrl();
                                @endphp
                                @if ($productImageUrl)
                                    <img src="{{ $productImageUrl }}" alt="{{ $item->name }}"
                                        class="h-28 w-24 rounded-2xl object-cover">
                                @else
                                    <div
                                        class="flex h-28 w-24 items-center justify-center rounded-2xl bg-[#fff4ec] text-[#d97f46]">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M8.25 7.5h9a2.25 2.25 0 0 1 2.25 2.25V12a3 3 0 0 1-3 3H8.25m0-7.5v7.5m0-7.5H6A2.25 2.25 0 0 0 3.75 9.75V12A3 3 0 0 0 6.75 15h1.5m0 0V18m4.5-3v3m4.5-3v3" />
                                        </svg>
                                    </div>
                                @endif

                                <div class="flex-1">
                                    <div class="flex items-start justify-between gap-2">
                                        <h3 class="text-lg font-bold text-[#2f241f]">{{ $item->name }}</h3>
                                        <div class="text-right">
                                            @if ($discountPercent > 0 && $hasActiveSizes)
                                                <p
                                                    class="js-size-base-price-label text-xs font-semibold text-slate-400 line-through">
                                                    ${{ number_format($defaultBasePrice, 2) }}
                                                </p>
                                            @endif
                                            <span class="js-size-price-label font-bold text-[#d97f46]">
                                                {{ $hasActiveSizes ? '$' . number_format($defaultPrice, 2) : 'Unavailable' }}
                                            </span>
                                            <p class="text-[11px] font-semibold uppercase tracking-[0.08em] text-[#b98b70]">
                                                Size price
                                            </p>
                                        </div>
                                    </div>

                                    <p class="mt-1 text-xs font-semibold uppercase tracking-[0.11em] text-[#b16231]">
                                        {{ $item->category?->name ?? 'Uncategorized' }}
                                    </p>
                                    @if ($discountPercent > 0)
                                        <p class="mt-1 text-xs font-semibold uppercase tracking-[0.09em] text-emerald-700">
                                            {{ rtrim(rtrim(number_format($discountPercent, 2), '0'), '.') }}% discount
                                        </p>
                                    @endif
                                    <p class="mt-2 text-sm text-gray-500">
                                        {{ $item->description ?: 'Freshly brewed and perfect for quick customer orders.' }}
                                    </p>

                                    <form method="POST" action="{{ route('cashier.cart.add') }}"
                                        class="js-product-cart-form mt-4 flex items-center justify-between gap-3"
                                        data-base-price-small="{{ number_format($smallBasePrice, 2, '.', '') }}"
                                        data-base-price-medium="{{ number_format($mediumBasePrice, 2, '.', '') }}"
                                        data-base-price-large="{{ number_format($largeBasePrice, 2, '.', '') }}"
                                        data-price-small="{{ number_format($smallPrice, 2, '.', '') }}"
                                        data-price-medium="{{ number_format($mediumPrice, 2, '.', '') }}"
                                        data-price-large="{{ number_format($largePrice, 2, '.', '') }}"
                                        data-active-sizes='@json($activeSizeKeys)'>
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $item->id }}">
                                        <input type="hidden" name="qty" value="1"
                                            class="js-product-qty-input">
                                        <div class="w-full space-y-3 rounded-2xl bg-[#fff9f4] p-3 ring-1 ring-[#f2dfd2]">
                                            <div class="space-y-2">
                                                <div class="flex items-center justify-between">
                                                    <span
                                                        class="text-xs font-semibold uppercase tracking-[0.1em] text-gray-500">Size</span>
                                                    <span
                                                        class="js-size-label rounded-full bg-[#fff1e8] px-2.5 py-1 text-[11px] font-semibold uppercase text-[#b16231]">{{ $defaultSizeLabel }}</span>
                                                </div>
                                                <input type="hidden" name="size" value="{{ $defaultSizeKey }}"
                                                    class="js-size-input">
                                                <div class="grid grid-cols-3 gap-2 text-xs">
                                                    @foreach ($allSizeOptions as $sizeOption)
                                                        @php
                                                            $sizeKey = (string) ($sizeOption['key'] ?? 'small');
                                                            $sizeIsActive = (bool) ($sizeOption['is_active'] ?? false);
                                                            $isDefaultSize =
                                                                $sizeKey === $defaultSizeKey && $sizeIsActive;
                                                        @endphp
                                                        <button type="button" data-size="{{ $sizeKey }}"
                                                            @class([
                                                                'js-size-option coffee-size-chip px-3 py-1.5 font-semibold',
                                                                'is-active' => $isDefaultSize,
                                                                'is-disabled' => !$sizeIsActive,
                                                            ])
                                                            aria-pressed="{{ $isDefaultSize ? 'true' : 'false' }}"
                                                            @disabled(!$sizeIsActive)>
                                                            {{ $sizeOption['label'] }}
                                                        </button>
                                                    @endforeach
                                                </div>
                                                @if (!$hasActiveSizes)
                                                    <p
                                                        class="text-[11px] font-semibold uppercase tracking-[0.08em] text-red-500">
                                                        This product currently has no active sizes.
                                                    </p>
                                                @endif
                                            </div>

                                            <div class="space-y-2">
                                                <div class="flex items-center justify-between">
                                                    <span
                                                        class="text-xs font-semibold uppercase tracking-[0.1em] text-gray-500">Sugar</span>
                                                    <span
                                                        class="rounded-full bg-[#fff1e8] px-2.5 py-1 text-[11px] font-semibold text-[#b16231]">
                                                        <span class="js-sugar-label">50</span>%
                                                    </span>
                                                </div>
                                                <input type="range" name="sugar" min="0" max="100"
                                                    step="5" value="50"
                                                    class="js-sugar-range coffee-sugar-range h-2.5 w-full cursor-pointer appearance-none rounded-full bg-[#f6e2d4] accent-[#f4a06b]">
                                                <div class="flex items-center justify-between text-[11px] text-gray-400">
                                                    <span>0%</span>
                                                    <span>100%</span>
                                                </div>
                                            </div>

                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center gap-2">
                                                    <button type="button"
                                                        class="js-product-qty-decrease flex h-8 w-8 items-center justify-center rounded-full border border-gray-300 text-base">-</button>
                                                    <span class="js-product-qty-label text-sm font-medium">1</span>
                                                    <button type="button"
                                                        class="js-product-qty-increase flex h-8 w-8 items-center justify-center rounded-full border border-gray-300 text-base">+</button>
                                                </div>

                                                <button type="submit" @disabled(!$hasActiveSizes || !$canWork)
                                                    class="js-add-to-cart-btn rounded-full bg-[#f4a06b] px-5 py-2 text-sm font-semibold text-white transition hover:brightness-105 disabled:opacity-70">
                                                    Add to cart
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div
                            class="col-span-full rounded-2xl border border-dashed border-[#d8c5b8] bg-[#fffaf5] p-6 text-center text-[#7a5c4e]">
                            No products found. Add products from Admin -> Products.
                        </div>
                    @endforelse
                </div>
            </main>
            @include('cashier.sidebar.cart', ['activeCashierMenu' => 'cart'])
        </div>
    </div>
    <div id="coffee-add-loading"
        class="fixed inset-0 z-[80] hidden items-center justify-center bg-[#2f241f]/35 backdrop-blur-sm">
        <div class="coffee-loader-card rounded-3xl bg-white/95 px-8 py-7 text-center shadow-2xl shadow-[#6a432b]/25">
            <div class="coffee-loader mx-auto">
                <div class="coffee-loader-steam">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
                <div class="coffee-loader-cup"></div>
            </div>
            <p class="mt-4 text-sm font-semibold tracking-wide text-[#7a5c4e]">Brewing your coffee...</p>
        </div>
    </div>
@endsection
