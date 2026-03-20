@extends('layouts.app')

@section('content')
    @php
        $categories = collect($categories ?? []);
    @endphp
    <div class="anim-enter-up w-full min-h-screen overflow-hidden lg:overflow-visible bg-white/85">
        <div class="grid min-h-screen grid-cols-1 lg:grid-cols-12">
            @include('admin.sidebar.sidebar', ['activeAdminMenu' => 'products'])
            <main
                class="anim-enter-right bg-[#f8f8f8] p-4 pt-20 sm:p-6 sm:pt-20 lg:col-span-9 lg:p-8 lg:pt-8 xl:col-span-10">
                <div class="anim-enter-up anim-delay-100 mb-6 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p
                            class="inline-flex items-center gap-2 rounded-full bg-[#ffe7d5] px-3 py-1 text-xs font-semibold uppercase tracking-[0.12em] text-[#b16231]">
                            <span class="h-2 w-2 rounded-full bg-[#f4a06b]"></span>
                            Admin Panel
                        </p>
                        <h1 class="mt-3 text-3xl font-black text-[#2f241f]">Product Management</h1>
                        <p class="mt-1 text-sm text-slate-500">Add products here and they appear in cashier menu.</p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <a href="{{ route('admin.index') }}"
                            class="anim-pop inline-flex items-center gap-2 rounded-xl border border-[#edd5c4] bg-white px-4 py-2 text-sm font-semibold text-[#7a5c4e] transition hover:bg-[#fff6f0]">
                            Back to dashboard
                        </a>
                        <button type="button"
                            class="js-open-add-product anim-pop inline-flex items-center gap-2 rounded-xl bg-[#f4a06b] px-4 py-2 text-sm font-semibold text-white transition hover:brightness-105">
                            Add Product
                        </button>
                    </div>
                </div>

                @if ($errors->any())
                    <div class="anim-pop mt-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        Please check the form and try again.
                    </div>
                @endif

                <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-3">
                    <template id="add-product-template">
                        <form id="swal-add-product-form" method="POST" action="{{ route('admin.products.store') }}"
                            enctype="multipart/form-data" class="space-y-4 text-left">
                            @csrf

                            <div>
                                <label for="swal-add-product-name"
                                    class="mb-1 block text-sm font-semibold text-[#5f4b40]">Product Name</label>
                                <input id="swal-add-product-name" name="name" type="text" value="{{ old('name') }}"
                                    required
                                    class="w-full rounded-xl border border-[#ecd9cc] bg-white px-4 py-3 text-sm outline-none"
                                    placeholder="Cappuccino">
                            </div>

                            <div>
                                <label class="mb-1 block text-sm font-semibold text-[#5f4b40]">Size Prices (USD)</label>
                                <div class="grid grid-cols-1 gap-2 sm:grid-cols-3">
                                    <input id="swal-add-product-price-small" name="price_small" type="number"
                                        value="{{ old('price_small', old('price')) }}" min="0" step="0.01"
                                        required
                                        class="w-full rounded-xl border border-[#ecd9cc] bg-white px-4 py-3 text-sm outline-none"
                                        placeholder="Small">
                                    <input id="swal-add-product-price-medium" name="price_medium" type="number"
                                        value="{{ old('price_medium', old('price')) }}" min="0" step="0.01"
                                        required
                                        class="w-full rounded-xl border border-[#ecd9cc] bg-white px-4 py-3 text-sm outline-none"
                                        placeholder="Medium">
                                    <input id="swal-add-product-price-large" name="price_large" type="number"
                                        value="{{ old('price_large', old('price')) }}" min="0" step="0.01"
                                        required
                                        class="w-full rounded-xl border border-[#ecd9cc] bg-white px-4 py-3 text-sm outline-none"
                                        placeholder="Large">
                                </div>
                            </div>

                            <div>
                                <label class="mb-1 block text-sm font-semibold text-[#5f4b40]">Size Availability</label>
                                <div class="grid grid-cols-1 gap-2 sm:grid-cols-3">
                                    <label
                                        class="inline-flex items-center gap-2 rounded-xl border border-[#ecd9cc] bg-white px-3 py-2 text-sm text-[#5f4b40]">
                                        <input type="hidden" name="is_small_active" value="0">
                                        <input type="checkbox" name="is_small_active" value="1"
                                            @checked(old('is_small_active', '1') === '1')
                                            class="h-4 w-4 rounded border-[#d8c3b4] text-[#f4a06b]">
                                        Small
                                    </label>
                                    <label
                                        class="inline-flex items-center gap-2 rounded-xl border border-[#ecd9cc] bg-white px-3 py-2 text-sm text-[#5f4b40]">
                                        <input type="hidden" name="is_medium_active" value="0">
                                        <input type="checkbox" name="is_medium_active" value="1"
                                            @checked(old('is_medium_active', '1') === '1')
                                            class="h-4 w-4 rounded border-[#d8c3b4] text-[#f4a06b]">
                                        Medium
                                    </label>
                                    <label
                                        class="inline-flex items-center gap-2 rounded-xl border border-[#ecd9cc] bg-white px-3 py-2 text-sm text-[#5f4b40]">
                                        <input type="hidden" name="is_large_active" value="0">
                                        <input type="checkbox" name="is_large_active" value="1"
                                            @checked(old('is_large_active', '1') === '1')
                                            class="h-4 w-4 rounded border-[#d8c3b4] text-[#f4a06b]">
                                        Large
                                    </label>
                                </div>
                            </div>

                            <div>
                                <label for="swal-add-product-discount"
                                    class="mb-1 block text-sm font-semibold text-[#5f4b40]">Discount (%)</label>
                                <input id="swal-add-product-discount" name="discount_percent" type="number"
                                    value="{{ old('discount_percent', '0') }}" min="0" max="100" step="0.01"
                                    class="w-full rounded-xl border border-[#ecd9cc] bg-white px-4 py-3 text-sm outline-none"
                                    placeholder="0">
                            </div>

                            <div>
                                <label for="swal-add-product-category"
                                    class="mb-1 block text-sm font-semibold text-[#5f4b40]">Category</label>
                                <select id="swal-add-product-category" name="category_id" required
                                    class="w-full rounded-xl border border-[#ecd9cc] bg-white px-4 py-3 text-sm outline-none">
                                    <option value="">Select category</option>
                                    @foreach ($categories as $categoryOption)
                                        <option value="{{ $categoryOption->id }}" @selected((string) old('category_id') === (string) $categoryOption->id)>
                                            {{ $categoryOption->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="swal-add-product-description"
                                    class="mb-1 block text-sm font-semibold text-[#5f4b40]">Description</label>
                                <textarea id="swal-add-product-description" name="description" rows="4"
                                    class="w-full rounded-xl border border-[#ecd9cc] bg-white px-4 py-3 text-sm outline-none"
                                    placeholder="Freshly brewed and perfect for customer orders.">{{ old('description') }}</textarea>
                            </div>

                            <div>
                                <label for="swal-add-product-image"
                                    class="mb-1 block text-sm font-semibold text-[#5f4b40]">Product Image</label>
                                <input id="swal-add-product-image" name="image" type="file" accept="image/*"
                                    class="w-full rounded-xl border border-[#ecd9cc] bg-white px-4 py-3 text-sm outline-none">
                            </div>

                            <label class="inline-flex items-center gap-2 text-sm text-[#5f4b40]">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', '1') === '1')
                                    class="h-4 w-4 rounded border-[#d8c3b4] text-[#f4a06b]">
                                Active in cashier
                            </label>

                            <button type="submit"
                                class="inline-flex w-full items-center justify-center rounded-xl bg-[#2f241f] px-4 py-3 text-sm font-semibold text-white transition hover:bg-[#201813]">
                                Add Product
                            </button>
                        </form>
                    </template>

                    <section
                        class="anim-enter-up anim-delay-300 rounded-3xl border border-[#f0e3da] bg-white p-5 xl:col-span-3">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <h2 class="text-xl font-bold text-[#2f241f]">Products</h2>
                            <form method="GET" action="{{ route('admin.products.index') }}"
                                class="relative w-full max-w-sm">
                                <input type="text" name="search" value="{{ $search }}"
                                    placeholder="Search products..."
                                    class="w-full rounded-xl border border-[#e9d8cc] bg-[#fffaf6] px-4 py-2.5 pr-28 text-sm outline-none transition focus:border-[#f4a06b] focus:ring-2 focus:ring-[#f4a06b]/20">
                                <div class="absolute right-2 top-1/2 flex -translate-y-1/2 items-center gap-1">
                                    @if ($search !== '')
                                        <a href="{{ route('admin.products.index') }}"
                                            class="rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-semibold text-slate-600 transition hover:bg-slate-100">Clear</a>
                                    @endif
                                    <button type="submit"
                                        class="rounded-lg bg-[#f4a06b] px-3 py-1.5 text-xs font-semibold text-white">Search</button>
                                </div>
                            </form>
                        </div>

                        <div class="mt-5 overflow-x-auto">
                            <table class="w-full min-w-[640px] text-left text-sm">
                                <thead>
                                    <tr class="border-b border-slate-200 text-[#7b5e50]">
                                        <th class="pb-3 font-semibold">Image</th>
                                        <th class="pb-3 font-semibold">Name</th>
                                        <th class="pb-3 font-semibold">Category</th>
                                        <th class="pb-3 font-semibold">Size Prices</th>
                                        <th class="pb-3 font-semibold">Discount</th>
                                        <th class="pb-3 font-semibold">Status</th>
                                        <th class="pb-3 font-semibold">Created By</th>
                                        <th class="pb-3 font-semibold">Created</th>
                                        <th class="pb-3 text-right font-semibold">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($products as $product)
                                        @php
                                            $createdByName = trim(
                                                (string) ($product->creator?->first_name ?? '') .
                                                    ' ' .
                                                    (string) ($product->creator?->last_name ?? ''),
                                            );
                                            $createdByName =
                                                $createdByName !== ''
                                                    ? $createdByName
                                                    : (string) ($product->creator?->name ?? 'System');
                                        @endphp
                                        <tr class="border-b border-slate-100 anim-pop anim-stagger"
                                            style="--stagger: {{ $loop->index + 1 }};">
                                            <td class="py-3.5">
                                                @if ($product->image_path)
                                                    <img src="{{ asset('storage/' . $product->image_path) }}"
                                                        alt="{{ $product->name }}"
                                                        class="h-14 w-14 rounded-xl object-cover ring-1 ring-black/5">
                                                @else
                                                    <span
                                                        class="flex h-14 w-14 items-center justify-center rounded-xl bg-[#fff4ec] text-[#d97f46]">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7"
                                                            fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                            stroke-width="1.9">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                d="M8.25 7.5h9a2.25 2.25 0 0 1 2.25 2.25V12a3 3 0 0 1-3 3H8.25m0-7.5v7.5m0-7.5H6A2.25 2.25 0 0 0 3.75 9.75V12A3 3 0 0 0 6.75 15h1.5m0 0V18m4.5-3v3m4.5-3v3" />
                                                        </svg>
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="py-3.5">
                                                <p class="font-semibold text-[#2f241f]">{{ $product->name }}</p>
                                                <p class="mt-0.5 text-xs text-slate-500">
                                                    {{ $product->description ?: 'No description' }}
                                                </p>
                                            </td>
                                            <td class="py-3.5 text-slate-600">
                                                {{ $product->category?->name ?? 'N/A' }}
                                            </td>
                                            @php
                                                $smallPrice = (float) ($product->price_small ?? ($product->price ?? 0));
                                                $mediumPrice =
                                                    (float) ($product->price_medium ?? ($product->price ?? 0));
                                                $largePrice = (float) ($product->price_large ?? ($product->price ?? 0));
                                                $smallActive = (bool) ($product->is_small_active ?? true);
                                                $mediumActive = (bool) ($product->is_medium_active ?? true);
                                                $largeActive = (bool) ($product->is_large_active ?? true);
                                                $discountPercent = max(
                                                    0,
                                                    min(100, (float) ($product->discount_percent ?? 0)),
                                                );
                                            @endphp
                                            <td class="py-3.5">
                                                <div class="flex flex-wrap items-center gap-1 text-xs">
                                                    <span @class([
                                                        'rounded-full px-2 py-1 font-semibold',
                                                        'bg-[#fff3ea] text-[#7f4a2a]' => $smallActive,
                                                        'bg-slate-100 text-slate-500' => !$smallActive,
                                                    ])>
                                                        S
                                                        ${{ number_format($smallPrice, 2) }}{{ $smallActive ? '' : ' (Off)' }}
                                                    </span>
                                                    <span @class([
                                                        'rounded-full px-2 py-1 font-semibold',
                                                        'bg-[#fff3ea] text-[#7f4a2a]' => $mediumActive,
                                                        'bg-slate-100 text-slate-500' => !$mediumActive,
                                                    ])>
                                                        M
                                                        ${{ number_format($mediumPrice, 2) }}{{ $mediumActive ? '' : ' (Off)' }}
                                                    </span>
                                                    <span @class([
                                                        'rounded-full px-2 py-1 font-semibold',
                                                        'bg-[#fff3ea] text-[#7f4a2a]' => $largeActive,
                                                        'bg-slate-100 text-slate-500' => !$largeActive,
                                                    ])>
                                                        L
                                                        ${{ number_format($largePrice, 2) }}{{ $largeActive ? '' : ' (Off)' }}
                                                    </span>
                                                </div>
                                            </td>
                                            <td class="py-3.5">
                                                @if ($discountPercent > 0)
                                                    <span
                                                        class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-amber-700">
                                                        {{ rtrim(rtrim(number_format($discountPercent, 2), '0'), '.') }}%
                                                        OFF
                                                    </span>
                                                @else
                                                    <span class="text-xs font-semibold text-slate-400">No Discount</span>
                                                @endif
                                            </td>
                                            <td class="py-3.5">
                                                @if ($product->is_active)
                                                    <span
                                                        class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-emerald-700">
                                                        Active
                                                    </span>
                                                @else
                                                    <span
                                                        class="rounded-full bg-slate-200 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-slate-600">
                                                        Inactive
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="py-3.5 text-slate-600">{{ $createdByName }}</td>
                                            <td class="py-3.5 text-slate-500">
                                                {{ optional($product->created_at)->format('M d, Y') }}
                                            </td>
                                            <td class="py-3.5 text-right">
                                                <button type="button"
                                                    class="js-edit-product-trigger rounded-lg border border-[#edd5c4] bg-white px-3 py-1.5 text-xs font-semibold text-[#7a5c4e] transition hover:bg-[#fff6f0]"
                                                    data-update-url="{{ route('admin.products.update', $product) }}"
                                                    data-name="{{ $product->name }}"
                                                    data-price-small="{{ $smallPrice }}"
                                                    data-price-medium="{{ $mediumPrice }}"
                                                    data-price-large="{{ $largePrice }}"
                                                    data-discount-percent="{{ $discountPercent }}"
                                                    data-category-id="{{ $product->category_id }}"
                                                    data-description="{{ $product->description }}"
                                                    data-small-active="{{ $smallActive ? '1' : '0' }}"
                                                    data-medium-active="{{ $mediumActive ? '1' : '0' }}"
                                                    data-large-active="{{ $largeActive ? '1' : '0' }}"
                                                    data-active="{{ $product->is_active ? '1' : '0' }}"
                                                    data-image-url="{{ $product->image_path ? asset('storage/' . $product->image_path) : '' }}">
                                                    Edit
                                                </button>
                                                <form method="POST"
                                                    action="{{ route('admin.products.destroy', $product) }}"
                                                    class="js-delete-product-form inline-block">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="rounded-lg border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-600 transition hover:bg-red-100">
                                                        Delete
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr class="anim-enter-up">
                                            <td colspan="9" class="py-8 text-center text-slate-500">No products found.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-5">
                            {{ $products->links() }}
                        </div>
                    </section>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const alertData = @json(session('alert'));
            const csrfToken = @json(csrf_token());
            const addProductTrigger = document.querySelector('.js-open-add-product');
            const addProductTemplate = document.getElementById('add-product-template');
            const categoryOptions = @json(
                $categories->map(fn($category) => [
                            'id' => (string) $category->id,
                            'name' => (string) $category->name,
                        ])->values());

            if (addProductTrigger && addProductTemplate) {
                addProductTrigger.addEventListener('click', function() {
                    Swal.fire({
                        title: 'Add product',
                        html: addProductTemplate.innerHTML,
                        showConfirmButton: false,
                        showCloseButton: true,
                        width: 720,
                        didOpen: function() {
                            const form = document.getElementById('swal-add-product-form');

                            if (!form) return;

                            form.addEventListener('submit', function(event) {
                                const smallActive = form.querySelector(
                                    'input[name="is_small_active"][type="checkbox"]'
                                )?.checked;
                                const mediumActive = form.querySelector(
                                    'input[name="is_medium_active"][type="checkbox"]'
                                )?.checked;
                                const largeActive = form.querySelector(
                                    'input[name="is_large_active"][type="checkbox"]'
                                )?.checked;

                                if (!smallActive && !mediumActive && !largeActive) {
                                    event.preventDefault();
                                    Swal.fire({
                                        icon: 'warning',
                                        title: 'Size required',
                                        text: 'At least one size must be active.',
                                        confirmButtonColor: '#f4a06b',
                                    });
                                    return;
                                }

                                if (!form.reportValidity()) {
                                    event.preventDefault();
                                }
                            });
                        },
                    });
                });
            }

            if (alertData) {
                Swal.fire({
                    icon: alertData.icon ?? 'success',
                    title: alertData.title ?? 'Done',
                    text: alertData.text ?? '',
                    confirmButtonColor: '#f4a06b',
                });
            }

            document.querySelectorAll('.js-delete-product-form').forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    event.preventDefault();

                    Swal.fire({
                        title: 'Delete this product?',
                        text: 'This action cannot be undone.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, delete',
                        cancelButtonText: 'Cancel',
                        confirmButtonColor: '#e11d48',
                        cancelButtonColor: '#64748b',
                    }).then(function(result) {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });

            document.querySelectorAll('.js-edit-product-trigger').forEach(function(button) {
                button.addEventListener('click', function() {
                    const updateUrl = button.dataset.updateUrl;
                    const currentName = button.dataset.name ?? '';
                    const currentPriceSmall = button.dataset.priceSmall ?? '';
                    const currentPriceMedium = button.dataset.priceMedium ?? '';
                    const currentPriceLarge = button.dataset.priceLarge ?? '';
                    const currentDiscountPercent = button.dataset.discountPercent ?? '0';
                    const currentCategoryId = button.dataset.categoryId ?? '';
                    const currentDescription = button.dataset.description ?? '';
                    const currentSmallActive = button.dataset.smallActive !== '0';
                    const currentMediumActive = button.dataset.mediumActive !== '0';
                    const currentLargeActive = button.dataset.largeActive !== '0';
                    const currentActive = button.dataset.active === '1';
                    const currentImageUrl = button.dataset.imageUrl ?? '';

                    const categorySelectOptions = categoryOptions.map(function(category) {
                        const selected = category.id === currentCategoryId ? 'selected' :
                        '';
                        return '<option value="' + category.id + '" ' + selected + '>' +
                            category
                            .name + '</option>';
                    }).join('');

                    Swal.fire({
                        title: 'Edit product',
                        html: `
                            <div class="space-y-4 text-left">
                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-[#5f4b40]">Name</label>
                                    <input id="swal-product-name" class="w-full rounded-xl border border-[#ecd9cc] bg-white px-4 py-3 text-sm outline-none" value="${currentName}">
                                </div>
                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-[#5f4b40]">Size Prices</label>
                                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-3">
                                        <input id="swal-product-price-small" type="number" min="0" step="0.01" class="w-full rounded-xl border border-[#ecd9cc] bg-white px-4 py-3 text-sm outline-none" value="${currentPriceSmall}" placeholder="Small">
                                        <input id="swal-product-price-medium" type="number" min="0" step="0.01" class="w-full rounded-xl border border-[#ecd9cc] bg-white px-4 py-3 text-sm outline-none" value="${currentPriceMedium}" placeholder="Medium">
                                        <input id="swal-product-price-large" type="number" min="0" step="0.01" class="w-full rounded-xl border border-[#ecd9cc] bg-white px-4 py-3 text-sm outline-none" value="${currentPriceLarge}" placeholder="Large">
                                    </div>
                                </div>
                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-[#5f4b40]">Size Availability</label>
                                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-3">
                                        <label class="inline-flex items-center gap-2 rounded-xl border border-[#ecd9cc] bg-white px-3 py-2 text-sm text-[#5f4b40]">
                                            <input id="swal-product-small-active" type="checkbox" class="h-4 w-4" ${currentSmallActive ? 'checked' : ''}>
                                            Small
                                        </label>
                                        <label class="inline-flex items-center gap-2 rounded-xl border border-[#ecd9cc] bg-white px-3 py-2 text-sm text-[#5f4b40]">
                                            <input id="swal-product-medium-active" type="checkbox" class="h-4 w-4" ${currentMediumActive ? 'checked' : ''}>
                                            Medium
                                        </label>
                                        <label class="inline-flex items-center gap-2 rounded-xl border border-[#ecd9cc] bg-white px-3 py-2 text-sm text-[#5f4b40]">
                                            <input id="swal-product-large-active" type="checkbox" class="h-4 w-4" ${currentLargeActive ? 'checked' : ''}>
                                            Large
                                        </label>
                                    </div>
                                </div>
                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-[#5f4b40]">Discount (%)</label>
                                    <input id="swal-product-discount" type="number" min="0" max="100" step="0.01" class="w-full rounded-xl border border-[#ecd9cc] bg-white px-4 py-3 text-sm outline-none" value="${currentDiscountPercent}">
                                </div>
                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-[#5f4b40]">Category</label>
                                    <select id="swal-product-category" class="w-full rounded-xl border border-[#ecd9cc] bg-white px-4 py-3 text-sm outline-none">
                                        <option value="">Select category</option>
                                        ${categorySelectOptions}
                                    </select>
                                </div>
                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-[#5f4b40]">Description</label>
                                    <textarea id="swal-product-description" class="w-full rounded-xl border border-[#ecd9cc] bg-white px-4 py-3 text-sm outline-none">${currentDescription}</textarea>
                                </div>
                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-[#5f4b40]">New Image (Optional)</label>
                                    ${currentImageUrl ? `<img src="${currentImageUrl}" alt="Current image" class="mb-2 h-16 w-16 rounded-lg object-cover ring-1 ring-black/10">` : ''}
                                    <input id="swal-product-image" type="file" accept="image/*" class="w-full rounded-xl border border-[#ecd9cc] bg-white px-4 py-3 text-sm outline-none">
                                </div>
                                <label class="inline-flex items-center gap-2 text-sm text-[#5f4b40]">
                                    <input id="swal-product-active" type="checkbox" class="h-4 w-4" ${currentActive ? 'checked' : ''}>
                                    Active
                                </label>
                            </div>
                        `,
                        showCancelButton: true,
                        confirmButtonText: 'Update',
                        cancelButtonText: 'Cancel',
                        confirmButtonColor: '#2f241f',
                        preConfirm: function() {
                            const name = document.getElementById('swal-product-name')
                                .value.trim();
                            const priceSmall = document.getElementById(
                                'swal-product-price-small').value.trim();
                            const priceMedium = document.getElementById(
                                'swal-product-price-medium').value.trim();
                            const priceLarge = document.getElementById(
                                'swal-product-price-large').value.trim();
                            const discountPercentRaw = document.getElementById(
                                'swal-product-discount').value.trim();
                            const categoryId = document.getElementById(
                                'swal-product-category').value;
                            const description = document.getElementById(
                                'swal-product-description').value.trim();
                            const isSmallActive = document.getElementById(
                                'swal-product-small-active').checked;
                            const isMediumActive = document.getElementById(
                                'swal-product-medium-active').checked;
                            const isLargeActive = document.getElementById(
                                'swal-product-large-active').checked;
                            const isActive = document.getElementById(
                                'swal-product-active').checked;
                            const imageInput = document.getElementById(
                                'swal-product-image');
                            const imageFile = imageInput && imageInput.files &&
                                imageInput.files[0] ? imageInput.files[0] : null;

                            if (!name || !priceSmall || !priceMedium || !priceLarge || !
                                categoryId) {
                                Swal.showValidationMessage(
                                    'Name, size prices, and category are required.');
                                return false;
                            }

                            const numericPriceSmall = Number(priceSmall);
                            const numericPriceMedium = Number(priceMedium);
                            const numericPriceLarge = Number(priceLarge);
                            const hasInvalidPrice = [numericPriceSmall,
                                    numericPriceMedium, numericPriceLarge
                                ]
                                .some(function(priceValue) {
                                    return Number.isNaN(priceValue) || priceValue <
                                        0;
                                });

                            if (hasInvalidPrice) {
                                Swal.showValidationMessage(
                                    'All size prices must be valid non-negative numbers.'
                                    );
                                return false;
                            }

                            if (!isSmallActive && !isMediumActive && !isLargeActive) {
                                Swal.showValidationMessage(
                                    'At least one size must be active.');
                                return false;
                            }

                            const numericDiscountPercent = discountPercentRaw === '' ?
                                0 : Number(
                                    discountPercentRaw);
                            if (
                                Number.isNaN(numericDiscountPercent) ||
                                numericDiscountPercent < 0 ||
                                numericDiscountPercent > 100
                            ) {
                                Swal.showValidationMessage(
                                    'Discount must be between 0 and 100.');
                                return false;
                            }

                            if (imageFile && imageFile.size > 2 * 1024 * 1024) {
                                Swal.showValidationMessage(
                                    'Image must be at most 2MB.');
                                return false;
                            }

                            return {
                                name: name,
                                price_small: numericPriceSmall.toFixed(2),
                                price_medium: numericPriceMedium.toFixed(2),
                                price_large: numericPriceLarge.toFixed(2),
                                discount_percent: numericDiscountPercent.toFixed(2),
                                category_id: categoryId,
                                description: description,
                                is_small_active: isSmallActive ? '1' : '0',
                                is_medium_active: isMediumActive ? '1' : '0',
                                is_large_active: isLargeActive ? '1' : '0',
                                is_active: isActive ? '1' : '0',
                                image: imageFile,
                            };
                        },
                    }).then(function(result) {
                        if (!result.isConfirmed || !result.value) {
                            return;
                        }

                        const formData = new FormData();

                        const fields = {
                            _token: csrfToken,
                            _method: 'PUT',
                            name: result.value.name,
                            price_small: result.value.price_small,
                            price_medium: result.value.price_medium,
                            price_large: result.value.price_large,
                            discount_percent: result.value.discount_percent,
                            category_id: result.value.category_id,
                            description: result.value.description,
                            is_small_active: result.value.is_small_active,
                            is_medium_active: result.value.is_medium_active,
                            is_large_active: result.value.is_large_active,
                            is_active: result.value.is_active,
                        };

                        Object.keys(fields).forEach(function(key) {
                            formData.append(key, fields[key] ?? '');
                        });

                        if (result.value.image) {
                            formData.append('image', result.value.image);
                        }

                        fetch(updateUrl, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                        }).then(function(response) {
                            if (response.ok || response.redirected) {
                                window.location.reload();
                                return;
                            }

                            Swal.fire({
                                icon: 'error',
                                title: 'Update failed',
                                text: 'Please check your input and try again.',
                                confirmButtonColor: '#f4a06b',
                            });
                        }).catch(function() {
                            Swal.fire({
                                icon: 'error',
                                title: 'Network error',
                                text: 'Could not update product right now.',
                                confirmButtonColor: '#f4a06b',
                            });
                        });
                    });
                });
            });
        });
    </script>
@endsection
