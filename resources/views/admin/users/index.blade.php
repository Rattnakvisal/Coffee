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
                            enctype="multipart/form-data" class="space-y-4 text-left">
                            @csrf

                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                <div>
                                    <label for="swal-add-user-first-name"
                                        class="mb-1 block text-sm font-semibold text-[#5f4b40]">First Name</label>
                                    <input id="swal-add-user-first-name" name="first_name" type="text"
                                        value="{{ old('first_name') }}" required
                                        class="w-full rounded-xl border border-[#ecd9cc] bg-white px-4 py-3 text-sm outline-none"
                                        placeholder="First name">
                                </div>
                                <div>
                                    <label for="swal-add-user-last-name"
                                        class="mb-1 block text-sm font-semibold text-[#5f4b40]">Last Name</label>
                                    <input id="swal-add-user-last-name" name="last_name" type="text"
                                        value="{{ old('last_name') }}" required
                                        class="w-full rounded-xl border border-[#ecd9cc] bg-white px-4 py-3 text-sm outline-none"
                                        placeholder="Last name">
                                </div>
                            </div>

                            <div>
                                <label for="swal-add-user-email"
                                    class="mb-1 block text-sm font-semibold text-[#5f4b40]">Email</label>
                                <input id="swal-add-user-email" name="email" type="email"
                                    value="{{ old('email') }}" required
                                    class="w-full rounded-xl border border-[#ecd9cc] bg-white px-4 py-3 text-sm outline-none"
                                    placeholder="member@example.com">
                            </div>

                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                <div>
                                    <label for="swal-add-user-phone"
                                        class="mb-1 block text-sm font-semibold text-[#5f4b40]">Phone</label>
                                    <input id="swal-add-user-phone" name="phone" type="text"
                                        value="{{ old('phone') }}"
                                        class="w-full rounded-xl border border-[#ecd9cc] bg-white px-4 py-3 text-sm outline-none"
                                        placeholder="+1##########">
                                </div>
                                <div>
                                    <label for="swal-add-user-gender"
                                        class="mb-1 block text-sm font-semibold text-[#5f4b40]">Gender</label>
                                    <select id="swal-add-user-gender" name="gender"
                                        class="w-full rounded-xl border border-[#ecd9cc] bg-white px-4 py-3 text-sm outline-none">
                                        <option value="">Prefer not to say</option>
                                        <option value="male" @selected(old('gender') === 'male')>Male</option>
                                        <option value="female" @selected(old('gender') === 'female')>Female</option>
                                        <option value="other" @selected(old('gender') === 'other')>Other</option>
                                    </select>
                                </div>
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

                            <div>
                                <label for="swal-add-user-avatar"
                                    class="mb-1 block text-sm font-semibold text-[#5f4b40]">Profile Photo</label>
                                <input id="swal-add-user-avatar" name="avatar" type="file" accept="image/*"
                                    class="w-full rounded-xl border border-[#ecd9cc] bg-white px-4 py-3 text-sm outline-none">
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
                            <table class="w-full min-w-[740px] text-left text-sm">
                                <thead>
                                    <tr class="border-b border-slate-200 text-[#7b5e50]">
                                        <th class="pb-3 font-semibold">Profile</th>
                                        <th class="pb-3 font-semibold">Email</th>
                                        <th class="pb-3 font-semibold">Role</th>
                                        <th class="pb-3 font-semibold">Created By</th>
                                        <th class="pb-3 font-semibold">Joined</th>
                                        <th class="pb-3 text-right font-semibold">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($users as $member)
                                        @php
                                            $memberDisplayName = trim((string) ($member->first_name ?? '') . ' ' . (string) ($member->last_name ?? ''));
                                            $memberDisplayName = $memberDisplayName !== '' ? $memberDisplayName : (string) $member->name;
                                            $memberFirstNameForForm = (string) ($member->first_name ?? '');
                                            $memberLastNameForForm = (string) ($member->last_name ?? '');
                                            if ($memberFirstNameForForm === '' && $memberLastNameForForm === '' && trim((string) $member->name) !== '') {
                                                $nameParts = preg_split('/\s+/', trim((string) $member->name), 2);
                                                $memberFirstNameForForm = (string) ($nameParts[0] ?? '');
                                                $memberLastNameForForm = (string) ($nameParts[1] ?? '');
                                            }
                                            $memberInitials = collect(explode(' ', $memberDisplayName))
                                                ->filter()
                                                ->map(fn(string $namePart): string => strtoupper(substr($namePart, 0, 1)))
                                                ->take(2)
                                                ->implode('');
                                            $memberAvatarUrl = $member->avatarUrl();
                                            $createdByName = trim(
                                                (string) ($member->creator?->first_name ?? '') .
                                                    ' ' .
                                                    (string) ($member->creator?->last_name ?? ''),
                                            );
                                            $createdByName =
                                                $createdByName !== ''
                                                    ? $createdByName
                                                    : (string) ($member->creator?->name ?? 'System');
                                        @endphp
                                        <tr class="border-b border-slate-100 anim-pop anim-stagger"
                                            style="--stagger: {{ $loop->index + 1 }};">
                                            <td class="py-3.5">
                                                <div class="flex items-center gap-3">
                                                    @if ($memberAvatarUrl)
                                                        <img src="{{ $memberAvatarUrl }}" alt="{{ $memberDisplayName }}"
                                                            class="h-11 w-11 rounded-xl object-cover ring-1 ring-black/5">
                                                    @else
                                                        <span
                                                            class="inline-flex h-11 w-11 items-center justify-center rounded-xl bg-[#2f241f] text-sm font-bold text-white">{{ $memberInitials }}</span>
                                                    @endif
                                                    <div>
                                                        <p class="font-semibold text-[#2f241f]">{{ $memberDisplayName }}</p>
                                                        <p class="text-xs text-slate-500">{{ $member->phone ?: 'No phone' }}
                                                        </p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="py-3.5 text-slate-600">{{ $member->email }}</td>
                                            <td class="py-3.5">
                                                <span
                                                    class="rounded-full bg-[#ffe7d5] px-3 py-1 text-xs font-semibold uppercase tracking-wide text-[#b16231]">
                                                    {{ str($member->role?->name ?? 'N/A')->headline() }}
                                                </span>
                                            </td>
                                            <td class="py-3.5 text-slate-600">{{ $createdByName }}</td>
                                            <td class="py-3.5 text-slate-500">
                                                {{ optional($member->created_at)->format('M d, Y') }}</td>
                                            <td class="py-3.5">
                                                <div class="flex items-center justify-end gap-2">
                                                    <button type="button"
                                                        class="js-edit-trigger rounded-lg border border-[#edd5c4] bg-white px-3 py-1.5 text-xs font-semibold text-[#7a5c4e] transition hover:bg-[#fff6f0]"
                                                        data-update-url="{{ route('admin.users.update', $member) }}"
                                                        data-first-name="{{ $memberFirstNameForForm }}"
                                                        data-last-name="{{ $memberLastNameForForm }}"
                                                        data-email="{{ $member->email }}"
                                                        data-phone="{{ $member->phone }}"
                                                        data-gender="{{ $member->gender }}"
                                                        data-role-id="{{ $member->role_id }}"
                                                        data-avatar-url="{{ $memberAvatarUrl }}">
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
                                            <td colspan="6" class="py-8 text-center text-slate-500">No members found.
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
            const escapeHtml = function(value) {
                return String(value ?? '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            };

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
                    const currentFirstName = button.dataset.firstName ?? '';
                    const currentLastName = button.dataset.lastName ?? '';
                    const currentEmail = button.dataset.email ?? '';
                    const currentPhone = button.dataset.phone ?? '';
                    const currentGender = button.dataset.gender ?? '';
                    const currentRoleId = button.dataset.roleId ?? '';
                    const currentAvatarUrl = button.dataset.avatarUrl ?? '';

                    const roleSelectOptions = roleOptions.map(function(role) {
                        const selected = role.id === currentRoleId ? 'selected' : '';
                        return '<option value="' + role.id + '" ' + selected + '>' + role
                            .name + '</option>';
                    }).join('');

                    const genderOptions = [{
                            value: '',
                            label: 'Prefer not to say'
                        },
                        {
                            value: 'male',
                            label: 'Male'
                        },
                        {
                            value: 'female',
                            label: 'Female'
                        },
                        {
                            value: 'other',
                            label: 'Other'
                        },
                    ].map(function(option) {
                        const selected = option.value === currentGender ? 'selected' : '';
                        return '<option value="' + option.value + '" ' + selected + '>' + option
                            .label + '</option>';
                    }).join('');

                    const avatarPreview = currentAvatarUrl ?
                        `
                            <img src="${escapeHtml(currentAvatarUrl)}" alt="Current profile photo" class="h-14 w-14 rounded-xl object-cover ring-1 ring-black/5">
                        ` :
                        `
                            <span class="inline-flex h-14 w-14 items-center justify-center rounded-xl bg-[#2f241f] text-sm font-bold text-white">
                                N/A
                            </span>
                        `;

                    Swal.fire({
                        title: 'Edit member',
                        html: `
                            <form id="swal-edit-user-form" method="POST" action="${escapeHtml(updateUrl)}" enctype="multipart/form-data" class="space-y-4 text-left">
                                <input type="hidden" name="_token" value="${escapeHtml(csrfToken)}">
                                <input type="hidden" name="_method" value="PUT">
                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                    <div>
                                        <label for="swal-edit-user-first-name" class="mb-1 block text-sm font-semibold text-[#5f4b40]">First Name</label>
                                        <input id="swal-edit-user-first-name" name="first_name" type="text" required value="${escapeHtml(currentFirstName)}" class="w-full rounded-xl border border-[#ecd9cc] bg-white px-4 py-3 text-sm outline-none">
                                    </div>
                                    <div>
                                        <label for="swal-edit-user-last-name" class="mb-1 block text-sm font-semibold text-[#5f4b40]">Last Name</label>
                                        <input id="swal-edit-user-last-name" name="last_name" type="text" required value="${escapeHtml(currentLastName)}" class="w-full rounded-xl border border-[#ecd9cc] bg-white px-4 py-3 text-sm outline-none">
                                    </div>
                                </div>
                                <div>
                                    <label for="swal-edit-user-email" class="mb-1 block text-sm font-semibold text-[#5f4b40]">Email</label>
                                    <input id="swal-edit-user-email" name="email" type="email" required value="${escapeHtml(currentEmail)}" class="w-full rounded-xl border border-[#ecd9cc] bg-white px-4 py-3 text-sm outline-none">
                                </div>
                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                    <div>
                                        <label for="swal-edit-user-phone" class="mb-1 block text-sm font-semibold text-[#5f4b40]">Phone</label>
                                        <input id="swal-edit-user-phone" name="phone" type="text" value="${escapeHtml(currentPhone)}" class="w-full rounded-xl border border-[#ecd9cc] bg-white px-4 py-3 text-sm outline-none" placeholder="+1##########">
                                    </div>
                                    <div>
                                        <label for="swal-edit-user-gender" class="mb-1 block text-sm font-semibold text-[#5f4b40]">Gender</label>
                                        <select id="swal-edit-user-gender" name="gender" class="w-full rounded-xl border border-[#ecd9cc] bg-white px-4 py-3 text-sm outline-none">
                                            ${genderOptions}
                                        </select>
                                    </div>
                                </div>
                                <div>
                                    <label for="swal-edit-user-role" class="mb-1 block text-sm font-semibold text-[#5f4b40]">Role</label>
                                    <select id="swal-edit-user-role" name="role_id" required class="w-full rounded-xl border border-[#ecd9cc] bg-white px-4 py-3 text-sm outline-none">
                                        ${roleSelectOptions}
                                    </select>
                                </div>
                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                    <div>
                                        <label for="swal-edit-user-password" class="mb-1 block text-sm font-semibold text-[#5f4b40]">New Password (Optional)</label>
                                        <input id="swal-edit-user-password" name="password" type="password" class="w-full rounded-xl border border-[#ecd9cc] bg-white px-4 py-3 text-sm outline-none" placeholder="Leave blank to keep current password">
                                    </div>
                                    <div>
                                        <label for="swal-edit-user-password-confirmation" class="mb-1 block text-sm font-semibold text-[#5f4b40]">Confirm Password</label>
                                        <input id="swal-edit-user-password-confirmation" name="password_confirmation" type="password" class="w-full rounded-xl border border-[#ecd9cc] bg-white px-4 py-3 text-sm outline-none" placeholder="Re-enter password">
                                    </div>
                                </div>
                                <div class="rounded-xl border border-[#ecd9cc] bg-[#fffaf6] p-3">
                                    <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-[#7b5e50]">Current Photo</p>
                                    ${avatarPreview}
                                    <div class="mt-3">
                                        <label for="swal-edit-user-avatar" class="mb-1 block text-sm font-semibold text-[#5f4b40]">Replace Photo</label>
                                        <input id="swal-edit-user-avatar" name="avatar" type="file" accept="image/*" class="w-full rounded-xl border border-[#ecd9cc] bg-white px-4 py-3 text-sm outline-none">
                                    </div>
                                    <label class="mt-3 inline-flex items-center gap-2 text-sm text-[#5f4b40]">
                                        <input type="checkbox" name="remove_avatar" value="1" class="rounded border-[#d8c4b8] text-[#2f241f] focus:ring-[#f4a06b]/40">
                                        Remove current photo
                                    </label>
                                </div>
                                <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-[#2f241f] px-4 py-3 text-sm font-semibold text-white transition hover:bg-[#201813]">
                                    Update Member
                                </button>
                            </form>
                        `,
                        showConfirmButton: false,
                        showCloseButton: true,
                        width: 700,
                        didOpen: function() {
                            const form = document.getElementById('swal-edit-user-form');
                            const password = document.getElementById('swal-edit-user-password');
                            const passwordConfirmation = document.getElementById(
                                'swal-edit-user-password-confirmation');

                            if (!form || !password || !passwordConfirmation) {
                                return;
                            }

                            const validatePassword = function() {
                                if (password.value !== passwordConfirmation.value) {
                                    passwordConfirmation.setCustomValidity(
                                        'Password confirmation does not match.'
                                    );
                                } else if (password.value && password.value.length < 8) {
                                    password.setCustomValidity(
                                        'Password must be at least 8 characters.'
                                    );
                                    passwordConfirmation.setCustomValidity('');
                                } else {
                                    password.setCustomValidity('');
                                    passwordConfirmation.setCustomValidity('');
                                }
                            };

                            password.addEventListener('input', validatePassword);
                            passwordConfirmation.addEventListener('input', validatePassword);

                            form.addEventListener('submit', function(event) {
                                validatePassword();

                                if (!form.reportValidity()) {
                                    event.preventDefault();
                                }
                            });
                        },
                    });
                });
            });

            if (searchForm && searchInput && suggestionsBox) {
                const hideSuggestions = function() {
                    suggestionsBox.classList.add('hidden');
                    suggestionsBox.innerHTML = '';
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
                            const safePhone = escapeHtml(item.phone ?? '');
                            const encodedName = encodeURIComponent(item.name ?? '');

                            return `
                                <button type="button" class="js-suggestion-item block w-full border-b border-[#f5ebe3] px-4 py-2.5 text-left last:border-b-0 hover:bg-[#fff4ec]" data-name="${encodedName}">
                                    <p class="text-sm font-semibold text-[#2f241f]">${safeName}</p>
                                    <p class="text-xs text-slate-500">${safeEmail}${safePhone ? ' • ' + safePhone : ''}</p>
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
