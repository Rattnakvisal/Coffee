@extends('layouts.app')

@section('content')
    @php
        $products = collect($products ?? []);
        $categories = collect($categories ?? []);
        $searchSuggestions = collect($searchSuggestions ?? []);
        $search = (string) ($search ?? '');
        $category = (string) ($category ?? '');
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
                <div class="flex flex-wrap items-center gap-3">
                    <form method="GET" action="{{ route('cashier.index') }}" class="relative min-w-[240px] flex-1"
                        data-cashier-search-form>
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

                <div class="mt-6 grid grid-cols-1 gap-5 xl:grid-cols-2">
                    @forelse ($products as $item)
                        <div class="rounded-3xl bg-white p-5 shadow-sm ring-1 ring-black/5 anim-pop anim-stagger"
                            style="--stagger: {{ $loop->index + 2 }};">
                            <div class="flex gap-4">
                                @if ($item->image_path)
                                    <img src="{{ asset('storage/' . $item->image_path) }}" alt="{{ $item->name }}"
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
                                        <span
                                            class="font-bold text-[#d97f46]">${{ number_format((float) $item->price, 2) }}</span>
                                    </div>

                                    <p class="mt-1 text-xs font-semibold uppercase tracking-[0.11em] text-[#b16231]">
                                        {{ $item->category?->name ?? 'Uncategorized' }}
                                    </p>
                                    <p class="mt-2 text-sm text-gray-500">
                                        {{ $item->description ?: 'Freshly brewed and perfect for quick customer orders.' }}
                                    </p>

                                    <form method="POST" action="{{ route('cashier.cart.add') }}"
                                        class="js-product-cart-form mt-4 flex items-center justify-between gap-3">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $item->id }}">
                                        <input type="hidden" name="qty" value="1" class="js-product-qty-input">
                                        <div class="w-full space-y-3 rounded-2xl bg-[#fff9f4] p-3 ring-1 ring-[#f2dfd2]">
                                            <div class="space-y-2">
                                                <div class="flex items-center justify-between">
                                                    <span
                                                        class="text-xs font-semibold uppercase tracking-[0.1em] text-gray-500">Size</span>
                                                    <span
                                                        class="js-size-label rounded-full bg-[#fff1e8] px-2.5 py-1 text-[11px] font-semibold uppercase text-[#b16231]">Small</span>
                                                </div>
                                                <input type="hidden" name="size" value="small" class="js-size-input">
                                                <div class="grid grid-cols-3 gap-2 text-xs">
                                                    <button type="button" data-size="small"
                                                        class="js-size-option coffee-size-chip is-active px-3 py-1.5 font-semibold"
                                                        aria-pressed="true">
                                                        Small
                                                    </button>
                                                    <button type="button" data-size="medium"
                                                        class="js-size-option coffee-size-chip px-3 py-1.5 font-semibold"
                                                        aria-pressed="false">
                                                        Medium
                                                    </button>
                                                    <button type="button" data-size="large"
                                                        class="js-size-option coffee-size-chip px-3 py-1.5 font-semibold"
                                                        aria-pressed="false">
                                                        Large
                                                    </button>
                                                </div>
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

                                                <button type="submit"
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

    <script>
        (function() {
            const root = document.documentElement;
            const overlay = document.querySelector('[data-cashier-overlay]');
            const menu = document.querySelector('[data-cashier-menu]');
            let cart = document.querySelector('[data-cashier-cart]');
            const openMenuButton = document.querySelector('[data-cashier-open-menu]');
            const openCartButton = document.querySelector('[data-cashier-open-cart]');
            const productCartForms = document.querySelectorAll('.js-product-cart-form');
            const searchForm = document.querySelector('[data-cashier-search-form]');
            const searchInput = searchForm ? searchForm.querySelector('[data-cashier-search-input]') : null;
            const searchDropdown = searchForm ? searchForm.querySelector('[data-cashier-search-dropdown]') : null;
            const searchResults = searchForm ? searchForm.querySelector('[data-cashier-search-results]') : null;
            const searchEmpty = searchForm ? searchForm.querySelector('[data-cashier-search-empty]') : null;
            const searchSuggestions = @json($searchSuggestions->values()->all());
            const loadingOverlay = document.getElementById('coffee-add-loading');
            const loadingText = loadingOverlay ? loadingOverlay.querySelector('p') : null;
            const desktopMediaQuery = window.matchMedia('(min-width: 1024px)');

            const showLoading = function(text) {
                if (!loadingOverlay) return;
                if (loadingText && text) {
                    loadingText.textContent = text;
                }

                loadingOverlay.classList.remove('hidden');
                loadingOverlay.classList.add('flex');
            };

            const hideLoading = function() {
                if (!loadingOverlay) return;
                loadingOverlay.classList.add('hidden');
                loadingOverlay.classList.remove('flex');
            };

            const setPlaceOrderFeedback = function(form, message, isError) {
                if (!form) return;

                const feedback = form.querySelector('[data-place-order-feedback]');
                if (!feedback) return;

                if (!message) {
                    feedback.classList.add('hidden');
                    feedback.textContent = '';
                    feedback.classList.remove('text-emerald-700', 'text-red-600');
                    return;
                }

                feedback.textContent = message;
                feedback.classList.remove('hidden');
                feedback.classList.toggle('text-emerald-700', !isError);
                feedback.classList.toggle('text-red-600', !!isError);
            };

            const initPlaceOrderForm = function(scope) {
                const rootElement = scope && scope.querySelector ? scope : document;
                const placeOrderForm = rootElement.querySelector('.js-place-order-form');
                if (!placeOrderForm) return;

                const paymentMethodField = placeOrderForm.querySelector('[data-payment-method]');
                const amountReceivedField = placeOrderForm.querySelector('[data-amount-received]');
                const paymentHint = placeOrderForm.querySelector('[data-payment-hint]');
                const orderTotal = Math.max(0, Number(placeOrderForm.dataset.orderTotal) || 0);

                if (!paymentMethodField || !amountReceivedField) return;

                const syncPaymentInputs = function() {
                    const paymentMethod = paymentMethodField.value;
                    const totalString = orderTotal.toFixed(2);
                    const isCashPayment = paymentMethod === 'cash';

                    amountReceivedField.readOnly = !isCashPayment;
                    amountReceivedField.min = totalString;

                    if (!isCashPayment) {
                        amountReceivedField.value = totalString;
                    } else if ((Number(amountReceivedField.value) || 0) < orderTotal) {
                        amountReceivedField.value = totalString;
                    }

                    if (paymentHint) {
                        paymentHint.textContent = isCashPayment ?
                            'For cash payment, received amount should be >= total.' :
                            'For card or QR payment, amount is set to exact total.';
                    }
                };

                if (placeOrderForm.dataset.paymentBound !== '1') {
                    paymentMethodField.addEventListener('change', syncPaymentInputs);
                    placeOrderForm.dataset.paymentBound = '1';
                }

                syncPaymentInputs();
            };

            const replaceCartHtml = function(html) {
                if (!cart || !html) return;

                const wasOpen = !cart.classList.contains('translate-x-full');
                const wrapper = document.createElement('div');
                wrapper.innerHTML = html.trim();

                const nextCart = wrapper.firstElementChild;
                if (!nextCart) return;

                if (wasOpen || desktopMediaQuery.matches) {
                    nextCart.classList.remove('translate-x-full');
                }

                cart.replaceWith(nextCart);
                cart = nextCart;
                initPlaceOrderForm(nextCart);
            };

            const submitCartFormAjax = async function(form, submitButton) {
                if (!form || form.dataset.submitting === '1') return;

                form.dataset.submitting = '1';
                if (submitButton) {
                    submitButton.disabled = true;
                }

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            Accept: 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: new FormData(form),
                    });

                    const payload = await response.json();

                    if (!response.ok) {
                        const failure = new Error(payload && payload.message ? payload.message : 'Request failed');
                        failure.payload = payload;
                        failure.status = response.status;
                        throw failure;
                    }

                    if (payload && payload.ok && payload.cart_html) {
                        replaceCartHtml(payload.cart_html);
                    }

                    return payload;
                } catch (error) {
                    if (error && error.status === 422 && error.payload) {
                        return error.payload;
                    }

                    form.submit();
                    return null;
                } finally {
                    form.dataset.submitting = '0';
                    if (submitButton) {
                        submitButton.disabled = false;
                    }
                }
            };

            initPlaceOrderForm(document);

            if (searchForm && searchInput && searchDropdown && searchResults && searchEmpty && Array.isArray(
                    searchSuggestions)) {
                const suggestionPool = searchSuggestions
                    .map(function(value) {
                        return String(value || '').trim();
                    })
                    .filter(function(value) {
                        return value !== '';
                    });

                let filteredSuggestions = [];
                let activeSuggestionIndex = -1;

                const closeSearchDropdown = function() {
                    searchDropdown.classList.add('hidden');
                    searchResults.innerHTML = '';
                    searchEmpty.classList.add('hidden');
                    filteredSuggestions = [];
                    activeSuggestionIndex = -1;
                };

                const openSearchDropdown = function() {
                    searchDropdown.classList.remove('hidden');
                };

                const setActiveSuggestion = function(nextIndex) {
                    const optionButtons = searchResults.querySelectorAll('[data-suggestion-index]');
                    optionButtons.forEach(function(button, index) {
                        const isActive = index === nextIndex;
                        button.classList.toggle('is-active', isActive);
                        button.setAttribute('aria-selected', isActive ? 'true' : 'false');

                        if (isActive) {
                            button.scrollIntoView({
                                block: 'nearest'
                            });
                        }
                    });
                };

                const selectSuggestion = function(value) {
                    searchInput.value = value;
                    closeSearchDropdown();
                    searchInput.focus();
                };

                const renderSearchSuggestions = function(queryValue) {
                    const query = String(queryValue || '').trim().toLowerCase();
                    const maxItems = 8;

                    filteredSuggestions = suggestionPool
                        .filter(function(suggestion) {
                            if (query === '') {
                                return true;
                            }

                            return suggestion.toLowerCase().includes(query);
                        })
                        .slice(0, maxItems);

                    searchResults.innerHTML = '';
                    activeSuggestionIndex = -1;

                    if (!filteredSuggestions.length) {
                        if (query === '') {
                            closeSearchDropdown();
                            return;
                        }

                        searchEmpty.classList.remove('hidden');
                        openSearchDropdown();
                        return;
                    }

                    searchEmpty.classList.add('hidden');
                    const fragment = document.createDocumentFragment();

                    filteredSuggestions.forEach(function(suggestion, index) {
                        const item = document.createElement('li');
                        const button = document.createElement('button');
                        button.type = 'button';
                        button.className = 'coffee-search-option';
                        button.textContent = suggestion;
                        button.dataset.suggestionIndex = String(index);
                        button.dataset.suggestionValue = suggestion;
                        button.setAttribute('role', 'option');
                        button.setAttribute('aria-selected', 'false');
                        item.appendChild(button);
                        fragment.appendChild(item);
                    });

                    searchResults.appendChild(fragment);
                    openSearchDropdown();
                };

                searchInput.addEventListener('focus', function() {
                    renderSearchSuggestions(searchInput.value);
                });

                searchInput.addEventListener('input', function() {
                    renderSearchSuggestions(searchInput.value);
                });

                searchInput.addEventListener('keydown', function(event) {
                    if (event.key === 'Escape') {
                        closeSearchDropdown();
                        return;
                    }

                    if (!filteredSuggestions.length) {
                        return;
                    }

                    if (event.key === 'ArrowDown') {
                        event.preventDefault();
                        activeSuggestionIndex = (activeSuggestionIndex + 1) % filteredSuggestions.length;
                        setActiveSuggestion(activeSuggestionIndex);
                        return;
                    }

                    if (event.key === 'ArrowUp') {
                        event.preventDefault();
                        activeSuggestionIndex = activeSuggestionIndex <= 0 ?
                            filteredSuggestions.length - 1 :
                            activeSuggestionIndex - 1;
                        setActiveSuggestion(activeSuggestionIndex);
                        return;
                    }

                    if (event.key === 'Enter' && activeSuggestionIndex >= 0) {
                        event.preventDefault();
                        selectSuggestion(filteredSuggestions[activeSuggestionIndex]);
                    }
                });

                searchResults.addEventListener('mousedown', function(event) {
                    const targetButton = event.target.closest('[data-suggestion-value]');
                    if (!targetButton) return;
                    event.preventDefault();
                });

                searchResults.addEventListener('click', function(event) {
                    const targetButton = event.target.closest('[data-suggestion-value]');
                    if (!targetButton) return;
                    selectSuggestion(targetButton.dataset.suggestionValue || '');
                });

                searchForm.addEventListener('submit', function() {
                    closeSearchDropdown();
                });

                document.addEventListener('click', function(event) {
                    if (searchForm.contains(event.target)) {
                        return;
                    }

                    closeSearchDropdown();
                });
            }

            productCartForms.forEach(function(form) {
                const qtyInput = form.querySelector('.js-product-qty-input');
                const qtyLabel = form.querySelector('.js-product-qty-label');
                const decreaseButton = form.querySelector('.js-product-qty-decrease');
                const increaseButton = form.querySelector('.js-product-qty-increase');
                const sizeInput = form.querySelector('.js-size-input');
                const sizeLabel = form.querySelector('.js-size-label');
                const sizeButtons = form.querySelectorAll('.js-size-option');
                const sugarRange = form.querySelector('.js-sugar-range');
                const sugarLabel = form.querySelector('.js-sugar-label');

                if (!qtyInput || !qtyLabel || !decreaseButton || !increaseButton) return;

                const syncQty = function(value) {
                    const nextQty = Math.min(99, Math.max(1, Number(value) || 1));
                    qtyInput.value = String(nextQty);
                    qtyLabel.textContent = String(nextQty);
                };

                const syncSize = function(value) {
                    if (!sizeInput || !sizeLabel || !sizeButtons.length) return;

                    const normalized = ['small', 'medium', 'large'].includes(String(value)) ?
                        String(value) :
                        'small';

                    sizeInput.value = normalized;
                    sizeLabel.textContent = normalized.charAt(0).toUpperCase() + normalized.slice(1);

                    sizeButtons.forEach(function(button) {
                        const isActive = button.dataset.size === normalized;
                        button.classList.toggle('is-active', isActive);
                        button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
                    });
                };

                const syncSugar = function(value) {
                    if (!sugarRange || !sugarLabel) return;

                    const normalized = Math.min(100, Math.max(0, Number(value) || 0));
                    sugarRange.value = String(normalized);
                    sugarLabel.textContent = String(normalized);
                    sugarRange.style.background =
                        'linear-gradient(90deg, #f4a06b 0%, #f4a06b ' + normalized +
                        '%, #f6e2d4 ' + normalized + '%, #f6e2d4 100%)';
                };

                decreaseButton.addEventListener('click', function() {
                    syncQty((Number(qtyInput.value) || 1) - 1);
                });

                increaseButton.addEventListener('click', function() {
                    syncQty((Number(qtyInput.value) || 1) + 1);
                });

                sizeButtons.forEach(function(button) {
                    button.addEventListener('click', function() {
                        syncSize(button.dataset.size);
                    });
                });

                if (sugarRange) {
                    sugarRange.addEventListener('input', function() {
                        syncSugar(sugarRange.value);
                    });
                }

                if (sizeInput) {
                    syncSize(sizeInput.value);
                }

                if (sugarRange) {
                    syncSugar(sugarRange.value);
                }

                form.addEventListener('submit', function(event) {
                    event.preventDefault();

                    const addButton = form.querySelector('.js-add-to-cart-btn');
                    const originalText = addButton ? addButton.textContent : null;

                    if (addButton) {
                        addButton.textContent = 'Adding...';
                    }

                    submitCartFormAjax(form, addButton).finally(function() {
                        if (addButton && originalText) {
                            addButton.textContent = originalText;
                        }
                    });
                });
            });

            if (!overlay || !menu || !cart) return;

            const closeAll = function() {
                menu.classList.add('-translate-x-full');
                cart.classList.add('translate-x-full');
                overlay.classList.add('hidden');
                root.classList.remove('overflow-hidden');
            };

            const openMenu = function() {
                menu.classList.remove('-translate-x-full');
                cart.classList.add('translate-x-full');
                overlay.classList.remove('hidden');
                root.classList.add('overflow-hidden');
            };

            const openCart = function() {
                cart.classList.remove('translate-x-full');
                menu.classList.add('-translate-x-full');
                overlay.classList.remove('hidden');
                root.classList.add('overflow-hidden');
            };

            if (openMenuButton) {
                openMenuButton.addEventListener('click', openMenu);
            }

            if (openCartButton) {
                openCartButton.addEventListener('click', openCart);
            }

            document.addEventListener('submit', function(event) {
                const targetForm = event.target.closest('.js-cart-item-form');
                if (targetForm) {
                    event.preventDefault();
                    const submitButton = targetForm.querySelector('button[type="submit"]');
                    submitCartFormAjax(targetForm, submitButton);
                    return;
                }

                const placeOrderForm = event.target.closest('.js-place-order-form');
                if (!placeOrderForm) return;
                event.preventDefault();

                const placeOrderButton = placeOrderForm.querySelector('[data-place-order]');
                const originalLabel = placeOrderButton ? placeOrderButton.textContent : '';

                setPlaceOrderFeedback(placeOrderForm, '', false);

                if (placeOrderButton) {
                    placeOrderButton.textContent = 'Placing order...';
                }

                showLoading('Processing payment...');

                submitCartFormAjax(placeOrderForm, placeOrderButton).then(function(payload) {
                    if (!payload || !payload.ok) {
                        hideLoading();
                        setPlaceOrderFeedback(
                            placeOrderForm,
                            payload && payload.message ? payload.message : 'Unable to place order.',
                            true,
                        );
                        return;
                    }

                    const orderNumber = payload.order_number ? String(payload.order_number) : '';
                    const changeAmount = Number(payload.change_amount || 0);
                    const successMessage = orderNumber !== '' ?
                        'Order ' + orderNumber + ' placed successfully.' :
                        'Order placed successfully.';
                    const loadingMessage = changeAmount > 0 ?
                        successMessage + ' Change: $' + changeAmount.toFixed(2) :
                        successMessage;

                    showLoading(loadingMessage);

                    window.setTimeout(function() {
                        hideLoading();
                    }, 700);
                }).finally(function() {
                    if (placeOrderButton) {
                        placeOrderButton.textContent = originalLabel || 'Place an order';
                    }
                });
            });

            document.addEventListener('click', function(event) {
                const closeTrigger = event.target.closest('[data-cashier-close]');
                if (closeTrigger) {
                    closeAll();
                }
            });

            overlay.addEventListener('click', closeAll);

            menu.querySelectorAll('a').forEach(function(link) {
                link.addEventListener('click', closeAll);
            });

            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape') {
                    closeAll();
                }
            });

            const handleDesktopChange = function(event) {
                if (event.matches) {
                    overlay.classList.add('hidden');
                    root.classList.remove('overflow-hidden');
                } else {
                    closeAll();
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
@endsection
