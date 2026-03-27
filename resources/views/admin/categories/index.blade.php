@extends('layouts.app')

@section('content')
    <div class="anim-enter-up w-full min-h-screen overflow-hidden lg:overflow-visible bg-white/85">
        <div class="grid min-h-screen grid-cols-1 lg:grid-cols-12">
            @include('admin.sidebar.sidebar', ['activeAdminMenu' => 'categories'])
            <main class="anim-enter-right bg-[#f8f8f8] p-4 pt-20 sm:p-6 sm:pt-20 lg:col-span-9 lg:p-8 lg:pt-8 xl:col-span-10">
                <div class="anim-enter-up anim-delay-100 mb-6 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p
                            class="inline-flex items-center gap-2 rounded-full bg-[#ffe7d5] px-3 py-1 text-xs font-semibold uppercase tracking-[0.12em] text-[#b16231]">
                            <span class="h-2 w-2 rounded-full bg-[#f4a06b]"></span>
                            Admin Panel
                        </p>
                        <h1 class="mt-3 text-3xl font-black text-[#2f241f]">Category Management</h1>
                        <p class="mt-1 text-sm text-slate-500">Create and manage product categories.</p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <a href="{{ route('admin.index') }}"
                            class="anim-pop inline-flex items-center gap-2 rounded-xl border border-[#edd5c4] bg-white px-4 py-2 text-sm font-semibold text-[#7a5c4e] transition hover:bg-[#fff6f0]">
                            Back to dashboard
                        </a>
                        <button type="button"
                            class="js-open-add-category anim-pop inline-flex items-center gap-2 rounded-xl bg-[#f4a06b] px-4 py-2 text-sm font-semibold text-white transition hover:brightness-105">
                            Add Category
                        </button>
                    </div>
                </div>

                @if ($errors->any())
                    <div class="anim-pop mt-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        Please check the form and try again.
                    </div>
                @endif

                <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-3">
                    <template id="add-category-template">
                        <form id="swal-add-category-form" method="POST" action="{{ route('admin.categories.store') }}"
                            class="space-y-4 text-left">
                            @csrf

                            <div>
                                <label for="swal-add-category-name"
                                    class="mb-1 block text-sm font-semibold text-[#5f4b40]">Category Name</label>
                                <input id="swal-add-category-name" name="name" type="text" value="{{ old('name') }}"
                                    required
                                    class="w-full rounded-xl border border-[#ecd9cc] bg-white px-4 py-3 text-sm outline-none"
                                    placeholder="Coffee">
                            </div>

                            <div>
                                <label for="swal-add-category-description"
                                    class="mb-1 block text-sm font-semibold text-[#5f4b40]">Description</label>
                                <textarea id="swal-add-category-description" name="description" rows="4"
                                    class="w-full rounded-xl border border-[#ecd9cc] bg-white px-4 py-3 text-sm outline-none"
                                    placeholder="Category description...">{{ old('description') }}</textarea>
                            </div>

                            <input type="hidden" name="is_active" value="0">

                            <label class="inline-flex items-center gap-2 text-sm text-[#5f4b40]">
                                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', '1') === '1')
                                    class="h-4 w-4 rounded border-[#d8c3b4] text-[#f4a06b]">
                                Active
                            </label>

                            <button type="submit"
                                class="inline-flex w-full items-center justify-center rounded-xl bg-[#2f241f] px-4 py-3 text-sm font-semibold text-white transition hover:bg-[#201813]">
                                Add Category
                            </button>
                        </form>
                    </template>

                    <template id="edit-category-template">
                        <form id="swal-edit-category-form" method="POST" class="space-y-4 text-left">
                            @csrf
                            @method('PUT')

                            <div>
                                <label for="swal-edit-category-name"
                                    class="mb-1 block text-sm font-semibold text-[#5f4b40]">Category Name</label>
                                <input id="swal-edit-category-name" name="name" type="text" required
                                    class="w-full rounded-xl border border-[#ecd9cc] bg-white px-4 py-3 text-sm outline-none"
                                    placeholder="Coffee">
                            </div>

                            <div>
                                <label for="swal-edit-category-description"
                                    class="mb-1 block text-sm font-semibold text-[#5f4b40]">Description</label>
                                <textarea id="swal-edit-category-description" name="description" rows="4"
                                    class="w-full rounded-xl border border-[#ecd9cc] bg-white px-4 py-3 text-sm outline-none"
                                    placeholder="Category description..."></textarea>
                            </div>

                            <input type="hidden" name="is_active" value="0">

                            <label class="inline-flex items-center gap-2 text-sm text-[#5f4b40]">
                                <input id="swal-edit-category-active" type="checkbox" name="is_active" value="1"
                                    class="h-4 w-4 rounded border-[#d8c3b4] text-[#f4a06b]">
                                Active
                            </label>

                            <button type="submit"
                                class="inline-flex w-full items-center justify-center rounded-xl bg-[#2f241f] px-4 py-3 text-sm font-semibold text-white transition hover:bg-[#201813]">
                                Update Category
                            </button>
                        </form>
                    </template>

                    <section
                        class="anim-enter-up anim-delay-300 rounded-3xl border border-[#f0e3da] bg-white p-5 xl:col-span-3">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <h2 class="text-xl font-bold text-[#2f241f]">Categories</h2>
                            <form method="GET" action="{{ route('admin.categories.index') }}"
                                class="relative w-full max-w-sm">
                                <input type="text" name="search" value="{{ $search }}"
                                    placeholder="Search categories..."
                                    class="w-full rounded-xl border border-[#e9d8cc] bg-[#fffaf6] px-4 py-2.5 pr-28 text-sm outline-none transition focus:border-[#f4a06b] focus:ring-2 focus:ring-[#f4a06b]/20">
                                <div class="absolute right-2 top-1/2 flex -translate-y-1/2 items-center gap-1">
                                    @if ($search !== '')
                                        <a href="{{ route('admin.categories.index') }}"
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
                                        <th class="pb-3 font-semibold">Name</th>
                                        <th class="pb-3 font-semibold">Products</th>
                                        <th class="pb-3 font-semibold">Status</th>
                                        <th class="pb-3 font-semibold">Created By</th>
                                        <th class="pb-3 font-semibold">Created</th>
                                        <th class="pb-3 text-right font-semibold">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($categories as $category)
                                        @php
                                            $createdByName = trim(
                                                (string) ($category->creator?->first_name ?? '') .
                                                    ' ' .
                                                    (string) ($category->creator?->last_name ?? ''),
                                            );
                                            $createdByName =
                                                $createdByName !== ''
                                                    ? $createdByName
                                                    : (string) ($category->creator?->name ?? 'System');
                                        @endphp
                                        <tr class="border-b border-slate-100 anim-pop anim-stagger"
                                            style="--stagger: {{ $loop->index + 1 }};">
                                            <td class="py-3.5">
                                                <p class="font-semibold text-[#2f241f]">{{ $category->name }}</p>
                                                <p class="mt-0.5 text-xs text-slate-500">{{ $category->description ?: 'No description' }}</p>
                                            </td>
                                            <td class="py-3.5 text-slate-600">{{ $category->products_count }}</td>
                                            <td class="py-3.5">
                                                @if ($category->is_active)
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
                                            <td class="py-3.5 text-slate-500">{{ optional($category->created_at)->format('M d, Y') }}</td>
                                            <td class="py-3.5 text-right">
                                                <button type="button"
                                                    class="js-edit-category-trigger rounded-lg border border-[#edd5c4] bg-white px-3 py-1.5 text-xs font-semibold text-[#7a5c4e] transition hover:bg-[#fff6f0]"
                                                    data-update-url="{{ route('admin.categories.update', $category) }}"
                                                    data-name="{{ $category->name }}"
                                                    data-description="{{ $category->description }}"
                                                    data-active="{{ $category->is_active ? '1' : '0' }}">
                                                    Edit
                                                </button>
                                                <form method="POST" action="{{ route('admin.categories.destroy', $category) }}"
                                                    class="js-delete-category-form inline-block">
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
                                            <td colspan="6" class="py-8 text-center text-slate-500">No categories found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-5">
                            {{ $categories->links() }}
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
            const addCategoryTrigger = document.querySelector('.js-open-add-category');
            const addCategoryTemplate = document.getElementById('add-category-template');
            const editCategoryTemplate = document.getElementById('edit-category-template');

            if (addCategoryTrigger && addCategoryTemplate) {
                addCategoryTrigger.addEventListener('click', function() {
                    Swal.fire({
                        title: 'Add category',
                        html: addCategoryTemplate.innerHTML,
                        showConfirmButton: false,
                        showCloseButton: true,
                        width: 680,
                        didOpen: function() {
                            const form = document.getElementById('swal-add-category-form');

                            if (!form) return;

                            form.addEventListener('submit', function(event) {
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

            document.querySelectorAll('.js-delete-category-form').forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    event.preventDefault();

                    Swal.fire({
                        title: 'Delete this category?',
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

            document.querySelectorAll('.js-edit-category-trigger').forEach(function(button) {
                button.addEventListener('click', function() {
                    const updateUrl = button.dataset.updateUrl;
                    const currentName = button.dataset.name ?? '';
                    const currentDescription = button.dataset.description ?? '';
                    const currentActive = button.dataset.active === '1';

                    if (!editCategoryTemplate) {
                        return;
                    }

                    Swal.fire({
                        title: 'Edit category',
                        html: editCategoryTemplate.innerHTML,
                        showConfirmButton: false,
                        showCloseButton: true,
                        width: 680,
                        didOpen: function() {
                            const form = document.getElementById('swal-edit-category-form');
                            const nameInput = document.getElementById('swal-edit-category-name');
                            const descriptionInput = document.getElementById(
                                'swal-edit-category-description');
                            const activeInput = document.getElementById('swal-edit-category-active');

                            if (!form || !nameInput || !descriptionInput || !activeInput) {
                                return;
                            }

                            form.action = updateUrl;
                            nameInput.value = currentName;
                            descriptionInput.value = currentDescription;
                            activeInput.checked = currentActive;

                            form.addEventListener('submit', function(event) {
                                if (!form.reportValidity()) {
                                    event.preventDefault();
                                }
                            });
                        },
                    });
                });
            });
        });
    </script>
@endsection
