@extends('layouts.app')

@section('content')
    @php
        $initialTab =
            session('activeSettingsTab') ??
            ($errors->has('current_password') || $errors->has('password') || $errors->has('password_confirmation')
                ? 'security'
                : 'profile');
        $nameParts = preg_split('/\s+/', trim((string) ($member->name ?? '')), 2) ?: [];
        $profileFirstName = old('first_name', $member->first_name ?? ($nameParts[0] ?? ''));
        $profileLastName = old('last_name', $member->last_name ?? ($nameParts[1] ?? ''));
        $profileInitials = strtoupper(
            substr((string) $profileFirstName, 0, 1) . substr((string) $profileLastName, 0, 1),
        );
        $profileInitials =
            $profileInitials !== '' ? $profileInitials : strtoupper(substr((string) ($member->name ?? 'U'), 0, 2));
        $avatarUrl = $member->avatar_path ? asset('storage/' . $member->avatar_path) : null;
        $removeAvatarOld = old('remove_avatar', '0') === '1';
        $showAvatarImage = $avatarUrl && !$removeAvatarOld;
    @endphp

    <div class="anim-enter-up min-h-screen w-full overflow-hidden bg-white/85 lg:overflow-visible">
        <div class="grid min-h-screen grid-cols-1 lg:grid-cols-12">
            @include('admin.sidebar.sidebar', ['activeAdminMenu' => 'settings'])

            <main
                class="anim-enter-right bg-[#f8f8f8] p-4 pt-20 sm:p-6 sm:pt-20 lg:col-span-9 lg:p-8 lg:pt-8 xl:col-span-10">
                <div class="anim-enter-up anim-delay-100 mb-6 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p
                            class="inline-flex items-center gap-2 rounded-full bg-[#ffe7d5] px-3 py-1 text-xs font-semibold uppercase tracking-[0.12em] text-[#b16231]">
                            <span class="h-2 w-2 rounded-full bg-[#f4a06b]"></span>
                            Admin Panel
                        </p>
                        <h1 class="mt-3 text-3xl font-black text-[#2f241f]">Account settings</h1>
                        <p class="mt-1 text-sm text-slate-500">Manage account profile and security settings.</p>
                    </div>

                    <a href="{{ route('admin.index') }}"
                        class="anim-pop inline-flex items-center gap-2 rounded-xl border border-[#edd5c4] bg-white px-4 py-2 text-sm font-semibold text-[#7a5c4e] transition hover:bg-[#fff6f0]">
                        Back to dashboard
                    </a>
                </div>

                @if ($errors->any())
                    <div class="anim-pop mt-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        Please check the form and try again.
                    </div>
                @endif

                <section class="mt-6 rounded-3xl border border-[#eadfd7] bg-[#f3f3f3] p-4 sm:p-6">
                    <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
                        <aside class="lg:col-span-3">
                            <nav class="rounded-2xl border border-[#eadfd7] bg-white p-2">
                                <button type="button" data-settings-tab="profile" @class([
                                    'flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm transition',
                                    'bg-[#fff1e8] font-semibold text-[#c56d39] ring-1 ring-[#f6d7c2]' =>
                                        $initialTab === 'profile',
                                    'text-[#6f5a4f] hover:bg-[#f8ede6]' => $initialTab !== 'profile',
                                ])>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M15.75 6.75a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Zm-9 13.5a5.25 5.25 0 0 1 10.5 0" />
                                    </svg>
                                    Profile Settings
                                </button>

                                <button type="button" data-settings-tab="security" @class([
                                    'mt-1 flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm transition',
                                    'bg-[#fff1e8] font-semibold text-[#c56d39] ring-1 ring-[#f6d7c2]' =>
                                        $initialTab === 'security',
                                    'text-[#6f5a4f] hover:bg-[#f8ede6]' => $initialTab !== 'security',
                                ])>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M16.5 10.5V6.75a4.5 4.5 0 0 0-9 0v3.75m-2.25 0h13.5A2.25 2.25 0 0 1 21 12.75v6A2.25 2.25 0 0 1 18.75 21h-13.5A2.25 2.25 0 0 1 3 18.75v-6A2.25 2.25 0 0 1 5.25 10.5Z" />
                                    </svg>
                                    Password
                                </button>

                                <button type="button"
                                    class="mt-1 flex w-full cursor-not-allowed items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm text-[#b4a296] opacity-70">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M14.25 18.75A6.75 6.75 0 1 0 7.5 12h6.75m0 0L12 9.75m2.25 2.25L12 14.25" />
                                    </svg>
                                    Notifications
                                </button>

                                <button type="button"
                                    class="mt-1 flex w-full cursor-not-allowed items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm text-[#b4a296] opacity-70">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="m9 12.75 2.25 2.25 3.75-3.75m5.25.75a8.25 8.25 0 1 1-16.5 0 8.25 8.25 0 0 1 16.5 0Z" />
                                    </svg>
                                    Verification
                                </button>

                                <a href="{{ route('admin.attendance.index') }}"
                                    class="mt-1 flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm text-[#6f5a4f] transition hover:bg-[#f8ede6]">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M8.25 3v1.5m7.5-1.5v1.5M3.75 8.25h16.5M5.25 5.25h13.5A1.5 1.5 0 0 1 20.25 6.75v12a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5v-12a1.5 1.5 0 0 1 1.5-1.5Zm3.75 6h6m-6 3h3" />
                                    </svg>
                                    Attendance
                                </a>
                            </nav>
                        </aside>

                        <div class="lg:col-span-9">
                            <section data-settings-panel="profile"
                                class="{{ $initialTab === 'profile' ? '' : 'hidden' }} rounded-2xl border border-[#eadfd7] bg-white p-5 sm:p-6">
                                <form method="POST" action="{{ route('admin.settings.profile.update') }}"
                                    enctype="multipart/form-data" class="space-y-5">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" id="remove_avatar" name="remove_avatar"
                                        value="{{ old('remove_avatar', '0') }}">

                                    <div class="flex flex-wrap items-center gap-4">
                                        <div class="relative">
                                            <img id="profile-avatar-image"
                                                src="{{ $avatarUrl ?: 'data:image/gif;base64,R0lGODlhAQABAAAAACw=' }}"
                                                alt="Profile avatar"
                                                class="{{ $showAvatarImage ? '' : 'hidden' }} h-20 w-20 rounded-full object-cover ring-1 ring-[#ecd9cc]">
                                            <span id="profile-avatar-fallback"
                                                class="{{ $showAvatarImage ? 'hidden' : 'inline-flex' }} h-20 w-20 items-center justify-center rounded-full bg-[#2f241f] text-xl font-bold text-white">
                                                {{ $profileInitials }}
                                            </span>
                                            <label for="avatar"
                                                class="absolute -bottom-1 -right-1 inline-flex h-7 w-7 cursor-pointer items-center justify-center rounded-full bg-[#f4a06b] text-white shadow">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M3 16.5V21h4.5L19.5 9l-4.5-4.5L3 16.5Z" />
                                                </svg>
                                            </label>
                                        </div>

                                        <div class="flex flex-wrap items-center gap-2">
                                            <label for="avatar"
                                                class="inline-flex cursor-pointer items-center justify-center rounded-lg bg-[#2f241f] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#201813]">
                                                Upload New
                                            </label>
                                            <button type="button" id="remove-avatar-button"
                                                class="inline-flex items-center justify-center rounded-lg border border-[#eadfd7] bg-white px-4 py-2 text-sm font-semibold text-[#6f5a4f] transition hover:bg-[#f8ede6]">
                                                Delete avatar
                                            </button>
                                        </div>

                                        <input id="avatar" name="avatar" type="file" accept="image/*" class="hidden">
                                    </div>

                                    @error('avatar')
                                        <p class="-mt-3 text-sm text-red-500">{{ $message }}</p>
                                    @enderror

                                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                        <div>
                                            <label for="first_name"
                                                class="mb-1 block text-sm font-semibold text-[#5f4b40]">First Name</label>
                                            <input id="first_name" name="first_name" type="text"
                                                value="{{ $profileFirstName }}" required
                                                class="w-full rounded-xl border border-[#ecd9cc] bg-white px-4 py-3 text-sm outline-none transition focus:border-[#f4a06b] focus:ring-2 focus:ring-[#f4a06b]/20"
                                                placeholder="First name">
                                            @error('first_name')
                                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div>
                                            <label for="last_name"
                                                class="mb-1 block text-sm font-semibold text-[#5f4b40]">Last Name</label>
                                            <input id="last_name" name="last_name" type="text"
                                                value="{{ $profileLastName }}" required
                                                class="w-full rounded-xl border border-[#ecd9cc] bg-white px-4 py-3 text-sm outline-none transition focus:border-[#f4a06b] focus:ring-2 focus:ring-[#f4a06b]/20"
                                                placeholder="Last name">
                                            @error('last_name')
                                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                        <div>
                                            <label for="email"
                                                class="mb-1 block text-sm font-semibold text-[#5f4b40]">Email</label>
                                            <input id="email" name="email" type="email"
                                                value="{{ old('email', $member->email) }}" required
                                                class="w-full rounded-xl border border-[#ecd9cc] bg-white px-4 py-3 text-sm outline-none transition focus:border-[#f4a06b] focus:ring-2 focus:ring-[#f4a06b]/20"
                                                placeholder="example@gmail.com">
                                            @error('email')
                                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div>
                                            <label for="phone"
                                                class="mb-1 block text-sm font-semibold text-[#5f4b40]">Mobile
                                                Number</label>
                                            <input id="phone" name="phone" type="text"
                                                value="{{ old('phone', $member->phone) }}"
                                                class="w-full rounded-xl border border-[#ecd9cc] bg-white px-4 py-3 text-sm outline-none transition focus:border-[#f4a06b] focus:ring-2 focus:ring-[#f4a06b]/20"
                                                placeholder="+15551234567">
                                            @error('phone')
                                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                        <div>
                                            <label for="gender"
                                                class="mb-1 block text-sm font-semibold text-[#5f4b40]">Gender</label>
                                            <select id="gender" name="gender"
                                                class="w-full rounded-xl border border-[#ecd9cc] bg-white px-4 py-3 text-sm outline-none transition focus:border-[#f4a06b] focus:ring-2 focus:ring-[#f4a06b]/20">
                                                <option value="">Select gender</option>
                                                <option value="male" @selected(old('gender', $member->gender) === 'male')>Male</option>
                                                <option value="female" @selected(old('gender', $member->gender) === 'female')>Female</option>
                                                <option value="other" @selected(old('gender', $member->gender) === 'other')>Other</option>
                                            </select>
                                            @error('gender')
                                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>

                                    <button type="submit"
                                        class="inline-flex items-center justify-center rounded-xl bg-[#2f241f] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#201813]">
                                        Save Profile
                                    </button>
                                </form>
                            </section>

                            <section data-settings-panel="security"
                                class="{{ $initialTab === 'security' ? '' : 'hidden' }} rounded-2xl border border-[#eadfd7] bg-white p-5 sm:p-6">
                                <h2 class="text-xl font-bold text-[#2f241f]">Password</h2>
                                <p class="mt-1 text-sm text-slate-500">Change your password to keep your account secure.
                                </p>

                                <form method="POST" action="{{ route('admin.settings.password.update') }}"
                                    class="mt-6 space-y-4">
                                    @csrf
                                    @method('PUT')

                                    <div>
                                        <label for="current_password"
                                            class="mb-1 block text-sm font-semibold text-[#5f4b40]">Current
                                            Password</label>
                                        <input id="current_password" name="current_password" type="password" required
                                            class="w-full rounded-xl border border-[#ecd9cc] bg-white px-4 py-3 text-sm outline-none transition focus:border-[#f4a06b] focus:ring-2 focus:ring-[#f4a06b]/20"
                                            placeholder="Enter current password">
                                        @error('current_password')
                                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                        <div>
                                            <label for="password"
                                                class="mb-1 block text-sm font-semibold text-[#5f4b40]">New
                                                Password</label>
                                            <input id="password" name="password" type="password" required
                                                class="w-full rounded-xl border border-[#ecd9cc] bg-white px-4 py-3 text-sm outline-none transition focus:border-[#f4a06b] focus:ring-2 focus:ring-[#f4a06b]/20"
                                                placeholder="Minimum 8 characters">
                                            @error('password')
                                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div>
                                            <label for="password_confirmation"
                                                class="mb-1 block text-sm font-semibold text-[#5f4b40]">Confirm
                                                Password</label>
                                            <input id="password_confirmation" name="password_confirmation"
                                                type="password" required
                                                class="w-full rounded-xl border border-[#ecd9cc] bg-white px-4 py-3 text-sm outline-none transition focus:border-[#f4a06b] focus:ring-2 focus:ring-[#f4a06b]/20"
                                                placeholder="Re-enter new password">
                                        </div>
                                    </div>

                                    <button type="submit"
                                        class="inline-flex items-center justify-center rounded-xl bg-[#f4a06b] px-5 py-3 text-sm font-semibold text-white transition hover:brightness-105">
                                        Change Password
                                    </button>
                                </form>
                            </section>
                        </div>
                    </div>
                </section>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tabButtons = Array.from(document.querySelectorAll('[data-settings-tab]'));
            const tabPanels = Array.from(document.querySelectorAll('[data-settings-panel]'));
            const initialTab = @json($initialTab);
            const alertData = @json(session('alert'));
            const avatarInput = document.getElementById('avatar');
            const removeAvatarInput = document.getElementById('remove_avatar');
            const removeAvatarButton = document.getElementById('remove-avatar-button');
            const avatarImage = document.getElementById('profile-avatar-image');
            const avatarFallback = document.getElementById('profile-avatar-fallback');

            const setTab = function(tabName) {
                tabButtons.forEach(function(button) {
                    const isActive = button.getAttribute('data-settings-tab') === tabName;
                    button.classList.toggle('bg-[#fff1e8]', isActive);
                    button.classList.toggle('font-semibold', isActive);
                    button.classList.toggle('text-[#c56d39]', isActive);
                    button.classList.toggle('ring-1', isActive);
                    button.classList.toggle('ring-[#f6d7c2]', isActive);
                    button.classList.toggle('text-[#6f5a4f]', !isActive);
                    button.classList.toggle('hover:bg-[#f8ede6]', !isActive);
                });

                tabPanels.forEach(function(panel) {
                    const isActive = panel.getAttribute('data-settings-panel') === tabName;
                    panel.classList.toggle('hidden', !isActive);
                });

                history.replaceState({
                    tab: tabName
                }, '', '#' + tabName);
            };

            tabButtons.forEach(function(button) {
                button.addEventListener('click', function() {
                    setTab(button.getAttribute('data-settings-tab'));
                });
            });

            const showAvatarFallback = function() {
                if (avatarImage) {
                    avatarImage.classList.add('hidden');
                    avatarImage.setAttribute('src', 'data:image/gif;base64,R0lGODlhAQABAAAAACw=');
                }
                if (avatarFallback) {
                    avatarFallback.classList.remove('hidden');
                    avatarFallback.classList.add('inline-flex');
                }
            };

            if (avatarInput) {
                avatarInput.addEventListener('change', function() {
                    if (!avatarInput.files || avatarInput.files.length === 0) {
                        return;
                    }

                    if (removeAvatarInput) {
                        removeAvatarInput.value = '0';
                    }

                    const file = avatarInput.files[0];
                    const reader = new FileReader();
                    reader.onload = function(event) {
                        if (avatarImage && event.target && typeof event.target.result === 'string') {
                            avatarImage.setAttribute('src', event.target.result);
                            avatarImage.classList.remove('hidden');
                        }
                        if (avatarFallback) {
                            avatarFallback.classList.add('hidden');
                            avatarFallback.classList.remove('inline-flex');
                        }
                    };
                    reader.readAsDataURL(file);
                });
            }

            if (removeAvatarButton) {
                removeAvatarButton.addEventListener('click', function() {
                    if (removeAvatarInput) {
                        removeAvatarInput.value = '1';
                    }
                    if (avatarInput) {
                        avatarInput.value = '';
                    }
                    showAvatarFallback();
                });
            }

            const hashTab = window.location.hash === '#security' ? 'security' : window.location.hash ===
                '#profile' ? 'profile' : null;
            setTab(hashTab || initialTab);

            if (!alertData) {
                return;
            }

            Swal.fire({
                icon: alertData.icon ?? 'success',
                title: alertData.title ?? 'Done',
                text: alertData.text ?? '',
                confirmButtonColor: '#f4a06b',
            });
        });
    </script>
@endsection
