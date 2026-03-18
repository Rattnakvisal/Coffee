@extends('layouts.app')
@section('content')
    @php
        $roles = $roles ?? collect();
        $selectedRole = $selectedRole ?? $roles->first();
        $roleProfiles = $roles
            ->mapWithKeys(function ($role): array {
                $isAdmin = $role->slug === 'admin';

                return [
                    $role->slug => [
                        'label' => str($role->name)->headline(),
                        'subtitle' => match ($role->slug) {
                            'admin' => 'Secure access for inventory, team, and reporting tools.',
                            'cashier' => 'Quick access for orders, checkout, and customer flow.',
                            default => 'Sign in with your assigned role and continue to your panel.',
                        },
                        'description' => $role->description,
                        'gradient' => $isAdmin
                            ? 'linear-gradient(140deg, #2f241f, #5b4337)'
                            : 'linear-gradient(140deg, #f4a06b, #d46f45)',
                        'button' => $isAdmin ? '#2f241f' : '#f4a06b',
                        'buttonHover' => $isAdmin ? '#201813' : '#df8855',
                    ],
                ];
            })
            ->all();

        $selectedProfile =
            $selectedRole && isset($roleProfiles[$selectedRole->slug])
                ? $roleProfiles[$selectedRole->slug]
                : [
                    'label' => $selectedRole ? str($selectedRole->name)->headline() : 'User',
                    'subtitle' => 'Sign in with your assigned role and continue to your panel.',
                    'description' => $selectedRole?->description,
                    'gradient' => 'linear-gradient(140deg, #2f241f, #5b4337)',
                    'button' => '#2f241f',
                    'buttonHover' => '#201813',
                ];
    @endphp

    <main class="mx-auto flex min-h-[calc(100vh-5rem)] w-full max-w-6xl items-center justify-center px-3 py-4 sm:px-5">
        <section
            class="anim-enter-up relative w-full overflow-hidden rounded-[28px] border border-white/70 bg-white/80 shadow-2xl shadow-[#c98a5f]/20 backdrop-blur sm:rounded-[36px]">
            <div
                class="anim-float pointer-events-none absolute -left-20 top-10 h-56 w-56 rounded-full bg-[#ffd9bf]/70 blur-3xl">
            </div>
            <div class="anim-float pointer-events-none absolute -bottom-16 right-20 h-56 w-56 rounded-full bg-[#f3c6a7]/70 blur-3xl"
                style="animation-delay: 0.4s;"></div>

            <div class="relative flex flex-col lg:flex-row">
                <div class="anim-enter-left w-full p-4 sm:p-8 lg:w-[60%] lg:p-10">
                    <div class="mx-auto w-full max-w-2xl">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div
                                class="anim-enter-up anim-delay-100 flex w-full flex-wrap gap-2 rounded-2xl bg-[#f6ece4] p-1.5 text-sm font-semibold sm:w-auto sm:gap-0 sm:rounded-full sm:p-1">
                                @foreach ($roles as $roleOption)
                                    @php
                                        $isCurrent = $selectedRole && $selectedRole->id === $roleOption->id;
                                    @endphp
                                    <button type="button" data-role-toggle="{{ $roleOption->slug }}"
                                        data-active="{{ $isCurrent ? 'true' : 'false' }}"
                                        class="{{ $isCurrent ? 'bg-white text-[#2f241f] shadow-sm' : 'text-[#7a5c4e] hover:text-[#2f241f]' }} flex-1 rounded-xl px-4 py-2 text-center transition anim-pop anim-stagger sm:flex-none sm:rounded-full sm:py-1.5"
                                        style="--stagger: {{ $loop->index + 1 }};">
                                        {{ str($roleOption->name)->headline() }}
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        <div class="anim-enter-up anim-delay-200 mt-7">
                            <p id="role-access-label"
                                class="text-xs font-semibold uppercase tracking-[0.14em] text-[#b16231]">
                                {{ $selectedProfile['label'] }}
                                Access</p>
                            <h2 class="mt-2 text-2xl font-black text-[#2f241f] sm:text-3xl">Sign in to continue</h2>
                        </div>

                        @if ($errors->any())
                            <div
                                class="anim-pop mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                                Please check your login details and try again.
                            </div>
                        @endif

                        <form id="login-form" method="POST"
                            action="{{ route('login.submit', ['role' => $selectedRole->slug]) }}"
                            data-action-template="{{ route('login.submit', ['role' => '__ROLE__']) }}"
                            data-view-template="{{ route('login.form', ['role' => '__ROLE__']) }}"
                            class="anim-enter-up anim-delay-300 mt-6 space-y-4">
                            @csrf

                            <div>
                                <label for="email" class="mb-1 block text-sm font-semibold text-[#5f4b40]">Email</label>
                                <div class="relative">
                                    <span
                                        class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-[#a47d67]">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M21.75 6.75v10.5A2.25 2.25 0 0 1 19.5 19.5h-15A2.25 2.25 0 0 1 2.25 17.25V6.75A2.25 2.25 0 0 1 4.5 4.5h15A2.25 2.25 0 0 1 21.75 6.75Zm-19.5.53 9.16 6.107a1.125 1.125 0 0 0 1.18 0l9.16-6.106" />
                                        </svg>
                                    </span>
                                    <input id="email" name="email" type="email" value="{{ old('email') }}" required
                                        class="w-full rounded-xl border border-[#ecd9cc] bg-white py-3 pl-12 pr-4 outline-none transition focus:border-[#f4a06b] focus:ring-2 focus:ring-[#f4a06b]/20"
                                        placeholder="you@example.com">
                                </div>
                                @error('email')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="password"
                                    class="mb-1 block text-sm font-semibold text-[#5f4b40]">Password</label>
                                <div class="relative">
                                    <span
                                        class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-[#a47d67]">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M16.5 10.5V6.75a4.5 4.5 0 0 0-9 0v3.75m-2.25 0h13.5A2.25 2.25 0 0 1 21 12.75v6A2.25 2.25 0 0 1 18.75 21h-13.5A2.25 2.25 0 0 1 3 18.75v-6A2.25 2.25 0 0 1 5.25 10.5Z" />
                                        </svg>
                                    </span>
                                    <input id="password" name="password" type="password" required
                                        class="w-full rounded-xl border border-[#ecd9cc] bg-white py-3 pl-12 pr-14 outline-none transition focus:border-[#f4a06b] focus:ring-2 focus:ring-[#f4a06b]/20"
                                        placeholder="Enter password">
                                    <button type="button" data-toggle-password data-target="password"
                                        class="absolute right-3 top-1/2 -translate-y-1/2 rounded-lg px-2 py-1 text-xs font-semibold text-[#8b5f45] hover:bg-[#f6ece4]">
                                        Show
                                    </button>
                                </div>
                                @error('password')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="flex items-center justify-between text-sm">
                                <label class="inline-flex items-center gap-2 text-[#6e5549]">
                                    <input type="checkbox" name="remember"
                                        class="h-4 w-4 rounded border-[#d8c3b4] text-[#f4a06b] focus:ring-[#f4a06b]/30">
                                    Remember me
                                </label>
                            </div>

                            <button id="login-submit-button" type="submit"
                                class="inline-flex w-full items-center justify-center gap-2 rounded-xl py-3.5 font-semibold text-white shadow-lg shadow-[#e1b090]/40 transition"
                                style="background-color: {{ $selectedProfile['button'] }};"
                                data-bg="{{ $selectedProfile['button'] }}"
                                data-bg-hover="{{ $selectedProfile['buttonHover'] }}">
                                <span id="login-submit-label">Continue to {{ $selectedProfile['label'] }}</span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>

                <aside class="anim-enter-right w-full bg-white/50 p-4 sm:p-8 lg:w-[40%] lg:p-10">
                    <div id="role-profile-card"
                        class="anim-float mx-auto h-full max-w-sm rounded-3xl p-6 text-white shadow-xl sm:p-7"
                        style="background: {{ $selectedProfile['gradient'] }};">
                        <p
                            class="inline-flex items-center gap-2 rounded-full bg-white/20 px-3 py-1 text-xs font-semibold uppercase tracking-[0.11em]">
                            Role profile
                        </p>
                        <h3 id="role-profile-title" class="mt-4 text-2xl font-black">{{ $selectedProfile['label'] }}
                        </h3>
                        <p id="role-profile-subtitle" class="mt-2 text-sm text-white/80">
                            {{ $selectedProfile['subtitle'] }}
                        </p>
                        <p id="role-profile-description"
                            class="{{ filled($selectedProfile['description']) ? '' : 'hidden' }} mt-6 rounded-2xl bg-white/15 px-4 py-3 text-sm leading-relaxed text-white/90">
                            {{ $selectedProfile['description'] }}
                        </p>
                    </div>
                </aside>
            </div>
        </section>
    </main>

    <script id="role-profiles" type="application/json">@json($roleProfiles)</script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const roleProfilesData = document.getElementById('role-profiles');
            const roleProfiles = roleProfilesData ? JSON.parse(roleProfilesData.textContent || '{}') : {};
            const roleButtons = Array.from(document.querySelectorAll('[data-role-toggle]'));
            const form = document.getElementById('login-form');
            const actionTemplate = form ? form.getAttribute('data-action-template') : '';
            const viewTemplate = form ? form.getAttribute('data-view-template') : '';
            const accessLabel = document.getElementById('role-access-label');
            const submitLabel = document.getElementById('login-submit-label');
            const submitButton = document.getElementById('login-submit-button');
            const profileCard = document.getElementById('role-profile-card');
            const profileTitle = document.getElementById('role-profile-title');
            const profileSubtitle = document.getElementById('role-profile-subtitle');
            const profileDescription = document.getElementById('role-profile-description');

            const setRoleState = function(roleSlug) {
                const profile = roleProfiles[roleSlug];
                if (!profile) return;

                roleButtons.forEach(function(button) {
                    const isActive = button.getAttribute('data-role-toggle') === roleSlug;
                    button.setAttribute('data-active', isActive ? 'true' : 'false');
                    button.classList.toggle('bg-white', isActive);
                    button.classList.toggle('text-[#2f241f]', isActive);
                    button.classList.toggle('shadow-sm', isActive);
                    button.classList.toggle('text-[#7a5c4e]', !isActive);
                    button.classList.toggle('hover:text-[#2f241f]', !isActive);
                });

                if (form && actionTemplate) {
                    form.setAttribute('action', actionTemplate.replace('__ROLE__', roleSlug));
                }

                if (viewTemplate) {
                    history.replaceState({
                        role: roleSlug
                    }, '', viewTemplate.replace('__ROLE__', roleSlug));
                }

                if (accessLabel) {
                    accessLabel.textContent = profile.label + ' Access';
                }

                if (submitLabel) {
                    submitLabel.textContent = 'Continue to ' + profile.label;
                }

                if (submitButton) {
                    submitButton.style.backgroundColor = profile.button;
                    submitButton.setAttribute('data-bg', profile.button);
                    submitButton.setAttribute('data-bg-hover', profile.buttonHover);
                }

                if (profileCard) {
                    profileCard.style.background = profile.gradient;
                }

                if (profileTitle) {
                    profileTitle.textContent = profile.label;
                }

                if (profileSubtitle) {
                    profileSubtitle.textContent = profile.subtitle;
                }

                if (profileDescription) {
                    if (profile.description) {
                        profileDescription.classList.remove('hidden');
                        profileDescription.textContent = profile.description;
                    } else {
                        profileDescription.classList.add('hidden');
                        profileDescription.textContent = '';
                    }
                }
            };

            roleButtons.forEach(function(button) {
                button.addEventListener('click', function() {
                    setRoleState(button.getAttribute('data-role-toggle'));
                });
            });

            const activeButton =
                roleButtons.find(function(button) {
                    return button.getAttribute('data-active') === 'true';
                }) || roleButtons[0];

            if (activeButton) {
                setRoleState(activeButton.getAttribute('data-role-toggle'));
            }

            if (submitButton) {
                submitButton.addEventListener('mouseenter', function() {
                    const hoverColor = submitButton.getAttribute('data-bg-hover');
                    if (hoverColor) {
                        submitButton.style.backgroundColor = hoverColor;
                    }
                });

                submitButton.addEventListener('mouseleave', function() {
                    const baseColor = submitButton.getAttribute('data-bg');
                    if (baseColor) {
                        submitButton.style.backgroundColor = baseColor;
                    }
                });
            }
        });

        document.addEventListener('click', function(event) {
            const button = event.target.closest('[data-toggle-password]');
            if (!button) return;

            const inputId = button.getAttribute('data-target');
            const input = document.getElementById(inputId);
            if (!input) return;

            const isPassword = input.getAttribute('type') === 'password';
            input.setAttribute('type', isPassword ? 'text' : 'password');
            button.textContent = isPassword ? 'Hide' : 'Show';
        });
    </script>
@endsection
