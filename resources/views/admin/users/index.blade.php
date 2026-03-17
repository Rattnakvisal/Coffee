@extends('layouts.app')

@section('content')
    <div class="anim-enter-up w-full min-h-screen overflow-hidden lg:overflow-visible bg-white/85">
        <div class="grid min-h-screen grid-cols-1 lg:grid-cols-12">
            @include('admin.sidebar.sidebar', ['activeAdminMenu' => 'users'])
            <main class="anim-enter-right bg-[#f8f8f8] p-4 pt-20 sm:p-6 sm:pt-20 lg:col-span-9 lg:p-8 lg:pt-8 xl:col-span-10">
                <div class="anim-enter-up anim-delay-100 mb-6 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p
                            class="inline-flex items-center gap-2 rounded-full bg-[#ffe7d5] px-3 py-1 text-xs font-semibold uppercase tracking-[0.12em] text-[#b16231]">
                            <span class="h-2 w-2 rounded-full bg-[#f4a06b]"></span>
                            Admin Panel
                        </p>
                        <h1 class="mt-3 text-3xl font-black text-[#2f241f]">User Management</h1>
                        <p class="mt-1 text-sm text-slate-500">Create and manage team members for admin and cashier roles.
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <a href="{{ route('admin.index') }}"
                            class="anim-pop anim-delay-200 inline-flex items-center gap-2 rounded-xl border border-[#edd5c4] bg-white px-4 py-2 text-sm font-semibold text-[#7a5c4e] transition hover:bg-[#fff6f0]">
                            Back to dashboard
                        </a>
                        <button type="button"
                            class="js-open-add-user anim-pop inline-flex items-center gap-2 rounded-xl bg-[#f4a06b] px-4 py-2 text-sm font-semibold text-white transition hover:brightness-105">
                            Add Member
                        </button>
                    </div>
                </div>

                @if ($errors->any())
                    <div class="anim-pop mt-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        Please check the form and try again.
                    </div>
                @endif

                <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-3">
                    <template id="add-user-template">
                        <form id="swal-add-user-form" method="POST" action="{{ route('admin.users.store') }}"
                            class="space-y-4 text-left">
                            @csrf

                            <div>
                                <label for="swal-add-user-name"
                                    class="mb-1 block text-sm font-semibold text-[#5f4b40]">Full Name</label>
                                <input id="swal-add-user-name" name="name" type="text" value="{{ old('name') }}"
                                    required
                                    class="w-full rounded-xl border border-[#ecd9cc] bg-white px-4 py-3 text-sm outline-none"
                                    placeholder="Member name">
                            </div>

                            <div>
                                <label for="swal-add-user-email"
                                    class="mb-1 block text-sm font-semibold text-[#5f4b40]">Email</label>
                                <input id="swal-add-user-email" name="email" type="email"
                                    value="{{ old('email') }}" required
                                    class="w-full rounded-xl border border-[#ecd9cc] bg-white px-4 py-3 text-sm outline-none"
                                    placeholder="member@example.com">
                            </div>

                            <div>
                                <label for="swal-add-user-role"
                                    class="mb-1 block text-sm font-semibold text-[#5f4b40]">Role</label>
                                <select id="swal-add-user-role" name="role_id" required
                                    class="w-full rounded-xl border border-[#ecd9cc] bg-white px-4 py-3 text-sm outline-none">
                                    <option value="">Select role</option>
                                    @foreach ($roles as $role)
                                        <option value="{{ $role->id }}" @selected((string) old('role_id') === (string) $role->id)>
                                            {{ str($role->name)->headline() }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="swal-add-user-password"
                                    class="mb-1 block text-sm font-semibold text-[#5f4b40]">Password</label>
                                <input id="swal-add-user-password" name="password" type="password" required
                                    class="w-full rounded-xl border border-[#ecd9cc] bg-white px-4 py-3 text-sm outline-none"
                                    placeholder="Minimum 8 characters">
                            </div>

                            <div>
                                <label for="swal-add-user-password-confirmation"
                                    class="mb-1 block text-sm font-semibold text-[#5f4b40]">Confirm Password</label>
                                <input id="swal-add-user-password-confirmation" name="password_confirmation"
                                    type="password" required
                                    class="w-full rounded-xl border border-[#ecd9cc] bg-white px-4 py-3 text-sm outline-none"
                                    placeholder="Re-enter password">
                            </div>

                            <button type="submit"
                                class="inline-flex w-full items-center justify-center rounded-xl bg-[#2f241f] px-4 py-3 text-sm font-semibold text-white transition hover:bg-[#201813]">
                                Add Member
                            </button>
                        </form>
                    </template>

                    <section
                        class="anim-enter-up anim-delay-300 rounded-3xl border border-[#f0e3da] bg-white p-5 xl:col-span-3">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <h2 class="text-xl font-bold text-[#2f241f]">Team Members</h2>
                            <form id="user-search-form" method="GET" action="{{ route('admin.users.index') }}"
                                class="relative w-full max-w-sm">
                                <input id="user-search-input" type="text" name="search" value="{{ $search }}"
                                    autocomplete="off" placeholder="Search by name or email..."
                                    class="w-full rounded-xl border border-[#e9d8cc] bg-[#fffaf6] px-4 py-2.5 pr-40 text-sm outline-none transition focus:border-[#f4a06b] focus:ring-2 focus:ring-[#f4a06b]/20">
                                <div class="absolute right-2 top-1/2 flex -translate-y-1/2 items-center gap-1">
                                    @if ($search !== '')
                                        <a href="{{ route('admin.users.index') }}"
                                            class="rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-semibold text-slate-600 transition hover:bg-slate-100">Clear</a>
                                    @endif
                                    <button type="submit"
                                        class="rounded-lg bg-[#f4a06b] px-3 py-1.5 text-xs font-semibold text-white">Search</button>
                                </div>
                                <div id="user-search-suggestions"
                                    class="absolute z-20 mt-2 hidden w-full overflow-hidden rounded-xl border border-[#ecd9cc] bg-white shadow-lg">
                                </div>
                            </form>
                        </div>

                        <div class="mt-5 overflow-x-auto">
                            <table class="w-full min-w-[640px] text-left text-sm">
                                <thead>
                                    <tr class="border-b border-slate-200 text-[#7b5e50]">
                                        <th class="pb-3 font-semibold">Name</th>
                                        <th class="pb-3 font-semibold">Email</th>
                                        <th class="pb-3 font-semibold">Role</th>
                                        <th class="pb-3 font-semibold">Joined</th>
                                        <th class="pb-3 text-right font-semibold">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($users as $member)
                                        <tr class="border-b border-slate-100 anim-pop anim-stagger"
                                            style="--stagger: {{ $loop->index + 1 }};">
                                            <td class="py-3.5 font-semibold text-[#2f241f]">{{ $member->name }}</td>
                                            <td class="py-3.5 text-slate-600">{{ $member->email }}</td>
                                            <td class="py-3.5">
                                                <span
                                                    class="rounded-full bg-[#ffe7d5] px-3 py-1 text-xs font-semibold uppercase tracking-wide text-[#b16231]">
                                                    {{ str($member->role?->name ?? 'N/A')->headline() }}
                                                </span>
                                            </td>
                                            <td class="py-3.5 text-slate-500">
                                                {{ optional($member->created_at)->format('M d, Y') }}</td>
                                            <td class="py-3.5">
                                                <div class="flex items-center justify-end gap-2">
                                                    <button type="button"
                                                        class="js-edit-trigger rounded-lg border border-[#edd5c4] bg-white px-3 py-1.5 text-xs font-semibold text-[#7a5c4e] transition hover:bg-[#fff6f0]"
                                                        data-update-url="{{ route('admin.users.update', $member) }}"
                                                        data-name="{{ $member->name }}"
                                                        data-email="{{ $member->email }}"
                                                        data-role-id="{{ $member->role_id }}">
                                                        Edit
                                                    </button>

                                                    @if (auth()->id() !== $member->id)
                                                        <form method="POST"
                                                            action="{{ route('admin.users.destroy', $member) }}"
                                                            class="js-delete-form">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit"
                                                                class="rounded-lg border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-600 transition hover:bg-red-100">
                                                                Delete
                                                            </button>
                                                        </form>
                                                    @else
                                                        <span
                                                            class="rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-500">Current</span>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr class="anim-enter-up">
                                            <td colspan="5" class="py-8 text-center text-slate-500">No members found.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-5">
                            {{ $users->links() }}
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
            const roleOptions = @json(
                $roles->map(fn($role) => [
                            'id' => (string) $role->id,
                            'name' => (string) str($role->name)->headline(),
                        ])->values());
            const csrfToken = @json(csrf_token());
            const suggestionsUrl = @json(route('admin.users.suggestions'));
            const searchForm = document.getElementById('user-search-form');
            const searchInput = document.getElementById('user-search-input');
            const suggestionsBox = document.getElementById('user-search-suggestions');
            const addUserTrigger = document.querySelector('.js-open-add-user');
            const addUserTemplate = document.getElementById('add-user-template');

            if (addUserTrigger && addUserTemplate) {
                addUserTrigger.addEventListener('click', function() {
                    Swal.fire({
                        title: 'Add member',
                        html: addUserTemplate.innerHTML,
                        showConfirmButton: false,
                        showCloseButton: true,
                        width: 680,
                        didOpen: function() {
                            const form = document.getElementById('swal-add-user-form');

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

            document.querySelectorAll('.js-delete-form').forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    event.preventDefault();

                    Swal.fire({
                        title: 'Delete this member?',
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

            document.querySelectorAll('.js-edit-trigger').forEach(function(button) {
                button.addEventListener('click', function() {
                    const updateUrl = button.dataset.updateUrl;
                    const currentName = button.dataset.name ?? '';
                    const currentEmail = button.dataset.email ?? '';
                    const currentRoleId = button.dataset.roleId ?? '';

                    const roleSelectOptions = roleOptions.map(function(role) {
                        const selected = role.id === currentRoleId ? 'selected' : '';
                        return '<option value="' + role.id + '" ' + selected + '>' + role
                            .name + '</option>';
                    }).join('');

                    Swal.fire({
                        title: 'Edit member',
                        html: `
                            <div class="space-y-4 text-left">
                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-[#5f4b40]">Full Name</label>
                                    <input id="swal-name" class="w-full rounded-xl border border-[#ecd9cc] bg-white px-4 py-3 text-sm outline-none" value="${currentName}">
                                </div>
                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-[#5f4b40]">Email</label>
                                    <input id="swal-email" type="email" class="w-full rounded-xl border border-[#ecd9cc] bg-white px-4 py-3 text-sm outline-none" value="${currentEmail}">
                                </div>
                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-[#5f4b40]">Role</label>
                                    <select id="swal-role" class="w-full rounded-xl border border-[#ecd9cc] bg-white px-4 py-3 text-sm outline-none">
                                        ${roleSelectOptions}
                                    </select>
                                </div>
                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-[#5f4b40]">New Password (Optional)</label>
                                    <input id="swal-password" type="password" class="w-full rounded-xl border border-[#ecd9cc] bg-white px-4 py-3 text-sm outline-none" placeholder="Leave blank to keep current password">
                                </div>
                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-[#5f4b40]">Confirm Password</label>
                                    <input id="swal-password-confirmation" type="password" class="w-full rounded-xl border border-[#ecd9cc] bg-white px-4 py-3 text-sm outline-none">
                                </div>
                            </div>
                        `,
                        showCancelButton: true,
                        confirmButtonText: 'Update',
                        cancelButtonText: 'Cancel',
                        confirmButtonColor: '#2f241f',
                        preConfirm: function() {
                            const name = document.getElementById('swal-name').value
                                .trim();
                            const email = document.getElementById('swal-email').value
                                .trim();
                            const roleId = document.getElementById('swal-role').value;
                            const password = document.getElementById('swal-password')
                                .value;
                            const passwordConfirmation = document.getElementById(
                                'swal-password-confirmation').value;

                            if (!name || !email || !roleId) {
                                Swal.showValidationMessage(
                                    'Name, email, and role are required.');
                                return false;
                            }

                            if (password && password.length < 8) {
                                Swal.showValidationMessage(
                                    'Password must be at least 8 characters.');
                                return false;
                            }

                            if (password !== passwordConfirmation) {
                                Swal.showValidationMessage(
                                    'Password confirmation does not match.');
                                return false;
                            }

                            return {
                                name: name,
                                email: email,
                                role_id: roleId,
                                password: password,
                                password_confirmation: passwordConfirmation,
                            };
                        },
                    }).then(function(result) {
                        if (!result.isConfirmed || !result.value) {
                            return;
                        }

                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = updateUrl;
                        form.style.display = 'none';

                        const fields = {
                            _token: csrfToken,
                            _method: 'PUT',
                            name: result.value.name,
                            email: result.value.email,
                            role_id: result.value.role_id,
                            password: result.value.password,
                            password_confirmation: result.value.password_confirmation,
                        };

                        Object.keys(fields).forEach(function(key) {
                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = key;
                            input.value = fields[key] ?? '';
                            form.appendChild(input);
                        });

                        document.body.appendChild(form);
                        form.submit();
                    });
                });
            });

            if (searchForm && searchInput && suggestionsBox) {
                const hideSuggestions = function() {
                    suggestionsBox.classList.add('hidden');
                    suggestionsBox.innerHTML = '';
                };

                const escapeHtml = function(value) {
                    return String(value ?? '')
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;')
                        .replace(/"/g, '&quot;')
                        .replace(/'/g, '&#039;');
                };

                const fetchSuggestions = function(query) {
                    const url = new URL(suggestionsUrl, window.location.origin);
                    url.searchParams.set('q', query);

                    fetch(url.toString(), {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    }).then(function(response) {
                        if (!response.ok) {
                            throw new Error('Failed to fetch suggestions');
                        }

                        return response.json();
                    }).then(function(payload) {
                        const items = payload.data ?? [];

                        if (!items.length) {
                            hideSuggestions();
                            return;
                        }

                        suggestionsBox.innerHTML = items.map(function(item) {
                            const safeName = escapeHtml(item.name ?? '');
                            const safeEmail = escapeHtml(item.email ?? '');
                            const encodedName = encodeURIComponent(item.name ?? '');

                            return `
                                <button type="button" class="js-suggestion-item block w-full border-b border-[#f5ebe3] px-4 py-2.5 text-left last:border-b-0 hover:bg-[#fff4ec]" data-name="${encodedName}">
                                    <p class="text-sm font-semibold text-[#2f241f]">${safeName}</p>
                                    <p class="text-xs text-slate-500">${safeEmail}</p>
                                </button>
                            `;
                        }).join('');

                        suggestionsBox.classList.remove('hidden');

                        suggestionsBox.querySelectorAll('.js-suggestion-item').forEach(function(
                            itemButton) {
                            itemButton.addEventListener('click', function() {
                                searchInput.value = decodeURIComponent(itemButton
                                    .dataset.name ?? '');
                                hideSuggestions();
                                searchForm.submit();
                            });
                        });
                    }).catch(function() {
                        hideSuggestions();
                    });
                };

                let searchDebounceTimer = null;

                searchInput.addEventListener('input', function() {
                    const query = searchInput.value.trim();

                    if (searchDebounceTimer) {
                        clearTimeout(searchDebounceTimer);
                    }

                    if (query.length < 1) {
                        hideSuggestions();
                        return;
                    }

                    searchDebounceTimer = setTimeout(function() {
                        fetchSuggestions(query);
                    }, 220);
                });

                searchInput.addEventListener('focus', function() {
                    const query = searchInput.value.trim();

                    if (query.length > 0) {
                        fetchSuggestions(query);
                    }
                });

                document.addEventListener('click', function(event) {
                    if (!event.target.closest('#user-search-form')) {
                        hideSuggestions();
                    }
                });
            }
        });
    </script>
@endsection
