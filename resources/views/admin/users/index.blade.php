@extends('layouts.app')

@section('content')
    <div
        class="anim-enter-up mx-auto w-full max-w-[1500px] overflow-hidden rounded-[32px] border border-white/60 bg-white/85 shadow-2xl shadow-[#bc7f54]/20">
        <div class="grid min-h-[85vh] grid-cols-1 lg:grid-cols-12">
            <aside class="anim-enter-left lg:col-span-3 xl:col-span-2 bg-[#2f241f] p-6 text-white">
                <div class="flex items-center gap-3">
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-[#f4a06b] text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.9">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M8.25 7.5h9a2.25 2.25 0 0 1 2.25 2.25V12a3 3 0 0 1-3 3H8.25m0-7.5v7.5m0-7.5H6A2.25 2.25 0 0 0 3.75 9.75V12A3 3 0 0 0 6.75 15h1.5m0 0V18m4.5-3v3m4.5-3v3" />
                        </svg>
                    </span>
                    <div>
                        <p class="text-lg font-black">Purr's Coffee</p>
                        <p class="text-xs text-white/60">Admin Workspace</p>
                    </div>
                </div>

                <nav class="mt-8 space-y-2">
                    <a href="{{ route('admin.index') }}"
                        class="flex items-center gap-3 rounded-xl px-4 py-3 text-white/80 transition hover:bg-white/10 hover:text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.9">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="m2.25 12 8.954-8.955a1.125 1.125 0 0 1 1.59 0L21.75 12M4.5 9.75V19.5A2.25 2.25 0 0 0 6.75 21.75h3.75v-6h3v6h3.75a2.25 2.25 0 0 0 2.25-2.25V9.75" />
                        </svg>
                        Dashboard
                    </a>
                    <a href="#"
                        class="flex items-center gap-3 rounded-xl px-4 py-3 text-white/80 transition hover:bg-white/10 hover:text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.9">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M6.75 7.5h10.5m-10.5 4.5h10.5m-10.5 4.5h6.75M3.75 5.25A1.5 1.5 0 0 1 5.25 3.75h13.5a1.5 1.5 0 0 1 1.5 1.5v13.5a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5V5.25Z" />
                        </svg>
                        Products
                    </a>
                    <a href="#"
                        class="flex items-center gap-3 rounded-xl px-4 py-3 text-white/80 transition hover:bg-white/10 hover:text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.9">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 7.5h18m-18 6h18m-18 6h18" />
                        </svg>
                        Categories
                    </a>
                    <a href="#"
                        class="flex items-center gap-3 rounded-xl px-4 py-3 text-white/80 transition hover:bg-white/10 hover:text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.9">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M2.25 8.25h19.5M6.75 2.25v3m10.5-3v3m-12 16.5h13.5A2.25 2.25 0 0 0 21 19.5v-12A2.25 2.25 0 0 0 18.75 5.25H5.25A2.25 2.25 0 0 0 3 7.5v12a2.25 2.25 0 0 0 2.25 2.25Z" />
                        </svg>
                        Orders
                    </a>
                    <a href="#"
                        class="flex items-center gap-3 rounded-xl px-4 py-3 text-white/80 transition hover:bg-white/10 hover:text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.9">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3 3v18h18M7.5 14.25 10.5 11l3 2.25 4.5-6" />
                        </svg>
                        Reports
                    </a>
                    <a href="{{ route('admin.users.index') }}"
                        class="flex items-center gap-3 rounded-xl bg-[#f4a06b] px-4 py-3 font-medium text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.9">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M18 18.75a3.75 3.75 0 1 0-7.5 0m7.5 0v.75h1.5a2.25 2.25 0 0 0 2.25-2.25v-.824a2.25 2.25 0 0 0-.663-1.588l-1.02-1.021a2.25 2.25 0 0 1-.659-1.591V8.25A6.75 6.75 0 0 0 6 8.25v4.976c0 .597-.237 1.169-.659 1.591l-1.02 1.02a2.25 2.25 0 0 0-.663 1.59v.824A2.25 2.25 0 0 0 5.908 20.5h1.5v-.75m10.592-1.5a6 6 0 0 0-12 0" />
                        </svg>
                        Users
                    </a>
                    <a href="#"
                        class="flex items-center gap-3 rounded-xl px-4 py-3 text-white/80 transition hover:bg-white/10 hover:text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.9">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.592c.55 0 1.02.398 1.11.94l.213 1.278a1.125 1.125 0 0 0 .846.894l1.251.313c.534.133.878.657.813 1.203l-.153 1.288a1.125 1.125 0 0 0 .323.939l.925.926c.39.39.39 1.024 0 1.414l-.925.926a1.125 1.125 0 0 0-.323.938l.153 1.29c.065.545-.279 1.07-.813 1.202l-1.251.313a1.125 1.125 0 0 0-.846.894l-.213 1.278c-.09.542-.56.94-1.11.94h-2.592c-.55 0-1.02-.398-1.11-.94l-.213-1.278a1.125 1.125 0 0 0-.846-.894l-1.251-.313a1.125 1.125 0 0 1-.813-1.203l.153-1.288a1.125 1.125 0 0 0-.323-.939l-.925-.926a1 1 0 0 1 0-1.414l.925-.926a1.125 1.125 0 0 0 .323-.938l-.153-1.29a1.125 1.125 0 0 1 .813-1.202l1.251-.313a1.125 1.125 0 0 0 .846-.894l.213-1.278Z" />
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
                        <button type="submit"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-white/25 px-4 py-2 text-sm font-medium text-white/80 transition hover:bg-white/10 hover:text-white">
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

            <main class="anim-enter-right lg:col-span-9 xl:col-span-10 bg-[#f8f8f8] p-6 lg:p-8">
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

                    <a href="{{ route('admin.index') }}"
                        class="anim-pop anim-delay-200 inline-flex items-center gap-2 rounded-xl border border-[#edd5c4] bg-white px-4 py-2 text-sm font-semibold text-[#7a5c4e] transition hover:bg-[#fff6f0]">
                        Back to dashboard
                    </a>
                </div>

                @if ($errors->any())
                    <div class="anim-pop mt-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        Please check the form and try again.
                    </div>
                @endif

                <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-3">
                    <section class="anim-enter-up anim-delay-200 rounded-3xl border border-[#f0e3da] bg-[#fffaf6] p-5 xl:col-span-1">
                        <h2 class="text-xl font-bold text-[#2f241f]">Add Member</h2>
                        <p class="mt-1 text-sm text-[#7a5c4e]">Create a new account and assign role.</p>

                        <form method="POST" action="{{ route('admin.users.store') }}" class="mt-5 space-y-4 anim-enter-up anim-delay-300">
                            @csrf

                            <div>
                                <label for="name" class="mb-1 block text-sm font-semibold text-[#5f4b40]">Full
                                    Name</label>
                                <input id="name" name="name" type="text" value="{{ old('name') }}" required
                                    class="w-full rounded-xl border border-[#ecd9cc] bg-white px-4 py-3 text-sm outline-none transition focus:border-[#f4a06b] focus:ring-2 focus:ring-[#f4a06b]/20"
                                    placeholder="Member name">
                                @error('name')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="email"
                                    class="mb-1 block text-sm font-semibold text-[#5f4b40]">Email</label>
                                <input id="email" name="email" type="email" value="{{ old('email') }}" required
                                    class="w-full rounded-xl border border-[#ecd9cc] bg-white px-4 py-3 text-sm outline-none transition focus:border-[#f4a06b] focus:ring-2 focus:ring-[#f4a06b]/20"
                                    placeholder="member@example.com">
                                @error('email')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="role_id" class="mb-1 block text-sm font-semibold text-[#5f4b40]">Role</label>
                                <select id="role_id" name="role_id" required
                                    class="w-full rounded-xl border border-[#ecd9cc] bg-white px-4 py-3 text-sm outline-none transition focus:border-[#f4a06b] focus:ring-2 focus:ring-[#f4a06b]/20">
                                    <option value="">Select role</option>
                                    @foreach ($roles as $role)
                                        <option value="{{ $role->id }}" @selected((string) old('role_id') === (string) $role->id)>
                                            {{ str($role->name)->headline() }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('role_id')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="password"
                                    class="mb-1 block text-sm font-semibold text-[#5f4b40]">Password</label>
                                <input id="password" name="password" type="password" required
                                    class="w-full rounded-xl border border-[#ecd9cc] bg-white px-4 py-3 text-sm outline-none transition focus:border-[#f4a06b] focus:ring-2 focus:ring-[#f4a06b]/20"
                                    placeholder="Minimum 8 characters">
                                @error('password')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="password_confirmation"
                                    class="mb-1 block text-sm font-semibold text-[#5f4b40]">Confirm Password</label>
                                <input id="password_confirmation" name="password_confirmation" type="password" required
                                    class="w-full rounded-xl border border-[#ecd9cc] bg-white px-4 py-3 text-sm outline-none transition focus:border-[#f4a06b] focus:ring-2 focus:ring-[#f4a06b]/20"
                                    placeholder="Re-enter password">
                            </div>

                            <button type="submit"
                                class="inline-flex w-full items-center justify-center rounded-xl bg-[#2f241f] px-4 py-3 text-sm font-semibold text-white transition hover:bg-[#201813]">
                                Add Member
                            </button>
                        </form>
                    </section>

                    <section class="anim-enter-up anim-delay-300 rounded-3xl border border-[#f0e3da] bg-white p-5 xl:col-span-2">
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
                            <div class="space-y-3 text-left">
                                <div>
                                    <label class="mb-1 block text-xs font-semibold text-slate-600">Full Name</label>
                                    <input id="swal-name" class="swal2-input !m-0 !w-full !rounded-lg !border-slate-300 !px-3 !py-2 text-sm" value="${currentName}">
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-semibold text-slate-600">Email</label>
                                    <input id="swal-email" type="email" class="swal2-input !m-0 !w-full !rounded-lg !border-slate-300 !px-3 !py-2 text-sm" value="${currentEmail}">
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-semibold text-slate-600">Role</label>
                                    <select id="swal-role" class="swal2-input !m-0 !w-full !rounded-lg !border-slate-300 !px-3 !py-2 text-sm">
                                        ${roleSelectOptions}
                                    </select>
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-semibold text-slate-600">New Password (Optional)</label>
                                    <input id="swal-password" type="password" class="swal2-input !m-0 !w-full !rounded-lg !border-slate-300 !px-3 !py-2 text-sm" placeholder="Leave blank to keep current password">
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-semibold text-slate-600">Confirm Password</label>
                                    <input id="swal-password-confirmation" type="password" class="swal2-input !m-0 !w-full !rounded-lg !border-slate-300 !px-3 !py-2 text-sm">
                                </div>
                            </div>
                        `,
                        showCancelButton: true,
                        confirmButtonText: 'Update',
                        cancelButtonText: 'Cancel',
                        confirmButtonColor: '#f4a06b',
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

                        suggestionsBox.querySelectorAll('.js-suggestion-item').forEach(function(itemButton) {
                            itemButton.addEventListener('click', function() {
                                searchInput.value = decodeURIComponent(itemButton.dataset.name ?? '');
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
