@php
    $cartItems = collect($cartItems ?? []);
    $cartSubtotal = (float) ($cartSubtotal ?? 0);
    $cartDiscount = (float) ($cartDiscount ?? 0);
    $cartTotal = (float) ($cartTotal ?? 0);
@endphp

<aside data-cashier-cart
    class="anim-enter-right translate-x-full overflow-y-auto border-l border-[#f0e3da] bg-[#fffaf6] p-6 transition-transform duration-300 ease-out max-lg:fixed max-lg:inset-y-0 max-lg:right-0 max-lg:z-50 max-lg:w-[82vw] max-lg:max-w-[360px] max-lg:shadow-2xl lg:sticky lg:top-0 lg:col-span-3 lg:h-screen lg:max-h-screen lg:w-auto lg:max-w-none lg:translate-x-0 lg:self-start lg:overflow-y-auto lg:shadow-none">
    <div class="flex items-start justify-between gap-3">
        <div>
            <h3 class="text-lg font-bold text-[#2f241f]">Cart</h3>
            <p class="text-sm text-gray-400">Ready to checkout</p>
        </div>
        <div class="flex items-start gap-2">
            <button type="button" data-cashier-close
                class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-[#ead7ca] text-[#7f6456] transition hover:bg-[#f8ede6] lg:hidden">
                <span class="sr-only">Close cart</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="1.9">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>
    <div class="mt-7 space-y-4">
        @forelse ($cartItems as $item)
            <div class="flex items-center gap-3 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-black/5 anim-pop anim-stagger"
                style="--stagger: {{ $loop->index + 3 }};">
                @if (!empty($item['image_path']))
                    <img src="{{ asset('storage/' . $item['image_path']) }}" alt="{{ $item['name'] }}"
                        class="h-14 w-14 rounded-xl object-cover">
                @else
                    <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-[#fff4ec] text-[#d97f46]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.9">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M8.25 7.5h9a2.25 2.25 0 0 1 2.25 2.25V12a3 3 0 0 1-3 3H8.25m0-7.5v7.5m0-7.5H6A2.25 2.25 0 0 0 3.75 9.75V12A3 3 0 0 0 6.75 15h1.5m0 0V18m4.5-3v3m4.5-3v3" />
                        </svg>
                    </div>
                @endif

                <div class="flex-1">
                    <h4 class="font-semibold text-[#2f241f]">{{ $item['name'] }}</h4>
                    <p class="text-xs text-gray-400">{{ ucfirst($item['size']) }} - Sugar {{ $item['sugar'] }}%</p>
                    <div class="mt-2 flex items-center justify-between">
                        <span
                            class="font-semibold text-[#2f241f]">${{ number_format((float) $item['line_total'], 2) }}</span>
                        <div class="flex items-center gap-2">
                            <form method="POST" action="{{ route('cashier.cart.decrement', $item['item_key']) }}"
                                class="js-cart-item-form">
                                @csrf
                                <button type="submit"
                                    class="flex h-7 w-7 items-center justify-center rounded-full border border-gray-300">-</button>
                            </form>
                            <span class="text-sm">{{ $item['qty'] }}</span>
                            <form method="POST" action="{{ route('cashier.cart.increment', $item['item_key']) }}"
                                class="js-cart-item-form">
                                @csrf
                                <button type="submit"
                                    class="flex h-7 w-7 items-center justify-center rounded-full border border-gray-300">+</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div
                class="rounded-2xl border border-dashed border-[#e8d4c6] bg-white p-5 text-center text-sm text-[#8b6a59]">
                Your cart is empty. Add items from menu.
            </div>
        @endforelse
    </div>

    <div class="mt-7 space-y-3 border-t border-[#f0e3da] pt-6 text-sm">
        <div class="flex justify-between">
            <span class="text-gray-500">Items</span>
            <span>${{ number_format($cartSubtotal, 2) }}</span>
        </div>
        <div class="flex justify-between">
            <span class="text-gray-500">Discount</span>
            <span>-${{ number_format($cartDiscount, 2) }}</span>
        </div>
        <div class="flex justify-between text-lg font-bold">
            <span>Total</span>
            <span class="text-[#d97f46]">${{ number_format($cartTotal, 2) }}</span>
        </div>
    </div>

    <form method="POST" action="{{ route('cashier.order.place') }}" class="js-place-order-form mt-7 space-y-3"
        data-order-total="{{ number_format($cartTotal, 2, '.', '') }}">
        @csrf
        <div class="rounded-2xl bg-white p-4 ring-1 ring-black/5">
            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-[0.1em] text-gray-500">Payment
                        Method</label>
                    <select name="payment_method" data-payment-method
                        class="mt-1.5 w-full rounded-xl border border-[#ead8cc] bg-white px-3 py-2.5 text-sm text-[#4b372d] outline-none transition focus:border-[#f4a06b] focus:ring-2 focus:ring-[#f4a06b]/25"
                        @disabled($cartItems->isEmpty())>
                        <option value="cash" selected>Cash</option>
                        <option value="card">Card</option>
                        <option value="qr">QR</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-[0.1em] text-gray-500">Amount
                        Received</label>
                    <input type="number" name="amount_received" data-amount-received min="0" step="0.01"
                        value="{{ number_format($cartTotal, 2, '.', '') }}"
                        class="mt-1.5 w-full rounded-xl border border-[#ead8cc] bg-white px-3 py-2.5 text-sm text-[#4b372d] outline-none transition focus:border-[#f4a06b] focus:ring-2 focus:ring-[#f4a06b]/25 disabled:cursor-not-allowed disabled:bg-slate-100"
                        @disabled($cartItems->isEmpty())>
                    <p data-payment-hint class="mt-1.5 text-[11px] text-[#8b6a59]">
                        For cash payment, received amount should be >= total.
                    </p>
                </div>
            </div>
        </div>

        <p data-place-order-feedback class="hidden text-xs font-semibold"></p>

        <button type="submit" data-place-order
            class="anim-pop anim-delay-400 inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-[#f4a06b] py-4 font-semibold text-white shadow-lg shadow-[#e8b28f] transition hover:brightness-105 disabled:cursor-not-allowed disabled:opacity-50"
            @disabled($cartItems->isEmpty())>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" stroke-width="1.9">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M3.75 6.75h16.5m-15 0 1.5 11.25A2.25 2.25 0 0 0 9 20.25h6a2.25 2.25 0 0 0 2.25-2.25l1.5-11.25M9.75 6.75v-1.5A2.25 2.25 0 0 1 12 3h0a2.25 2.25 0 0 1 2.25 2.25v1.5" />
            </svg>
            Place an order
        </button>
    </form>
</aside>
