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
                            'admin' => 'Control inventory, team members, reporting, and system settings.',
                            'cashier' => 'Run quick checkout, manage orders, and track daily attendance.',
                            default => 'Sign in with your assigned role and continue to your panel.',
                        },
                        'description' => $role->description,
                        'gradient' => $isAdmin
                            ? 'linear-gradient(155deg, #1e293b 0%, #0f172a 45%, #334155 100%)'
                            : 'linear-gradient(155deg, #92400e 0%, #b45309 45%, #f59e0b 100%)',
                        'button' => $isAdmin ? '#0f172a' : '#b45309',
                        'buttonHover' => $isAdmin ? '#020617' : '#92400e',
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
                    'gradient' => 'linear-gradient(155deg, #1e293b 0%, #0f172a 45%, #334155 100%)',
                    'button' => '#0f172a',
                    'buttonHover' => '#020617',
                ];
    @endphp

    <main class="relative isolate flex min-h-screen items-center justify-center overflow-hidden bg-[#f4f1eb] px-4 py-4 sm:px-6 lg:px-10"
        style="font-family: 'Avenir Next', 'Trebuchet MS', 'Segoe UI', sans-serif;">
        <div class="pointer-events-none absolute -left-28 top-8 h-72 w-72 rounded-full bg-[#f59e0b]/25 blur-3xl anim-float">
        </div>
        <div class="pointer-events-none absolute -right-24 bottom-10 h-72 w-72 rounded-full bg-[#1d4ed8]/20 blur-3xl anim-float"
            style="animation-delay: 0.35s;"></div>

        <section
            class="anim-enter-up relative mx-auto grid w-full max-w-6xl overflow-hidden rounded-[34px] border border-[#dfd5ca] bg-[#fffdf9] shadow-[0_30px_80px_-35px_rgba(15,23,42,0.45)] lg:grid-cols-[0.92fr_1.08fr]">
            <aside id="role-profile-card" class="relative overflow-hidden p-7 text-white sm:p-10"
                style="background: {{ $selectedProfile['gradient'] }};">
                <div
                    class="pointer-events-none absolute -right-10 -top-10 h-36 w-36 rounded-full border border-white/25 bg-white/10">
                </div>
                <div
                    class="pointer-events-none absolute -bottom-14 left-1/2 h-48 w-48 -translate-x-1/2 rounded-full border border-white/10 bg-white/5">
                </div>

                <div class="relative z-10">
                    <p
                        class="inline-flex rounded-full border border-white/25 bg-white/15 px-3.5 py-1.5 text-xs font-bold tracking-[0.12em]">
                        PURR'S COFFEE
                    </p>
                    <h1 class="mt-5 text-3xl font-black leading-tight sm:text-[2.15rem]">
                        Workspace Access
                    </h1>
                    <p id="role-profile-subtitle" class="mt-3 max-w-sm text-sm text-white/85">
                        {{ $selectedProfile['subtitle'] }}
                    </p>
                    <p id="role-profile-description"
                        class="{{ filled($selectedProfile['description']) ? '' : 'hidden' }} mt-5 rounded-2xl border border-white/20 bg-white/10 px-4 py-3 text-sm text-white/90">
                        {{ $selectedProfile['description'] }}
                    </p>

                    <div class="mt-8 rounded-2xl border border-white/20 bg-white/10 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.14em] text-white/80">Selected Role</p>
                        <p id="role-profile-title" class="mt-1 text-2xl font-black">{{ $selectedProfile['label'] }}</p>
                    </div>

                    <a href="{{ route('welcome') }}"
                        class="mt-6 inline-flex items-center justify-center rounded-xl border border-white/30 bg-white/10 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-white/20">
                        Back to Cashier
                    </a>
                </div>
            </aside>

            <div class="anim-enter-right p-6 sm:p-10">
                <div class="mx-auto w-full max-w-xl">
                    <div
                        class="anim-enter-up anim-delay-100 flex flex-wrap gap-2 rounded-2xl bg-[#efe8dd] p-1.5 text-sm font-semibold">
                        @foreach ($roles as $roleOption)
                            @php
                                $isCurrent = $selectedRole && $selectedRole->id === $roleOption->id;
                            @endphp
                            <button type="button" data-role-toggle="{{ $roleOption->slug }}"
                                data-active="{{ $isCurrent ? 'true' : 'false' }}"
                                class="{{ $isCurrent ? 'bg-white text-[#2f241f] shadow-sm' : 'text-[#7a5c4e] hover:text-[#2f241f]' }} flex-1 rounded-xl px-4 py-2 text-center transition anim-pop anim-stagger"
                                style="--stagger: {{ $loop->index + 1 }};">
                                {{ str($roleOption->name)->headline() }}
                            </button>
                        @endforeach
                    </div>

                    <div class="anim-enter-up anim-delay-200 mt-7">
                        <p id="role-access-label" class="text-xs font-bold uppercase tracking-[0.14em] text-[#b45309]">
                            {{ $selectedProfile['label'] }} Access
                        </p>
                        <h2 class="mt-2 text-3xl font-black leading-tight text-[#111827] sm:text-4xl">Workspace Access</h2>
                        <p class="mt-2 text-sm text-[#6b7280]">
                            Enter your credentials to access your dashboard.
                        </p>
                    </div>

                    @if ($errors->any())
                        <div
                            class="anim-pop mt-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                            Please check your login details and try again.
                        </div>
                    @endif

                    @if (session('status'))
                        <div
                            class="anim-pop mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form id="login-form" method="POST"
                        action="{{ route('login.submit', ['role' => $selectedRole->slug]) }}"
                        data-action-template="{{ route('login.submit', ['role' => '__ROLE__']) }}"
                        data-view-template="{{ route('login.form', ['role' => '__ROLE__']) }}"
                        class="anim-enter-up anim-delay-300 mt-6 space-y-4">
                        @csrf

                        <div>
                            <label for="email" class="mb-1.5 block text-sm font-semibold text-[#374151]">Email</label>
                            <div class="relative">
                                <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-[#9a7f69]">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M21.75 6.75v10.5A2.25 2.25 0 0 1 19.5 19.5h-15A2.25 2.25 0 0 1 2.25 17.25V6.75A2.25 2.25 0 0 1 4.5 4.5h15A2.25 2.25 0 0 1 21.75 6.75Zm-19.5.53 9.16 6.107a1.125 1.125 0 0 0 1.18 0l9.16-6.106" />
                                    </svg>
                                </span>
                                <input id="email" name="email" type="email" value="{{ old('email') }}" required
                                    class="w-full rounded-xl border border-[#dccdbf] bg-white px-4 py-3 pl-12 text-[#111827] outline-none transition focus:border-[#b45309] focus:ring-2 focus:ring-[#b45309]/20"
                                    placeholder="you@example.com">
                            </div>
                            @error('email')
                                <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password" class="mb-1.5 block text-sm font-semibold text-[#374151]">Password</label>
                            <div class="relative">
                                <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-[#9a7f69]">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M16.5 10.5V6.75a4.5 4.5 0 0 0-9 0v3.75m-2.25 0h13.5A2.25 2.25 0 0 1 21 12.75v6A2.25 2.25 0 0 1 18.75 21h-13.5A2.25 2.25 0 0 1 3 18.75v-6A2.25 2.25 0 0 1 5.25 10.5Z" />
                                    </svg>
                                </span>
                                <input id="password" name="password" type="password" required
                                    class="w-full rounded-xl border border-[#dccdbf] bg-white px-4 py-3 pl-12 pr-16 text-[#111827] outline-none transition focus:border-[#b45309] focus:ring-2 focus:ring-[#b45309]/20"
                                    placeholder="Enter password">
                                <button type="button" data-toggle-password data-target="password"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 rounded-md px-2.5 py-1 text-xs font-bold text-[#7c2d12] transition hover:bg-[#fff1e8]">
                                    Show
                                </button>
                            </div>
                            @error('password')
                                <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center justify-between text-sm">
                            <label class="inline-flex items-center gap-2 text-[#4b5563]">
                                <input type="checkbox" name="remember"
                                    class="h-4 w-4 rounded border-[#c8b39f] text-[#b45309] focus:ring-[#b45309]/30">
                                Remember me
                            </label>
                        </div>

                        <button id="login-submit-button" type="submit"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl py-3.5 text-base font-bold text-white shadow-lg shadow-slate-900/20 transition"
                            style="background-color: {{ $selectedProfile['button'] }};"
                            data-bg="{{ $selectedProfile['button'] }}"
                            data-bg-hover="{{ $selectedProfile['buttonHover'] }}">
                            <span id="login-submit-label">Continue to {{ $selectedProfile['label'] }}</span>
                            <span id="login-submit-spinner"
                                class="hidden h-4 w-4 animate-spin rounded-full border-2 border-white/35 border-t-white"
                                aria-hidden="true"></span>
                            <svg id="login-submit-arrow" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </section>
    </main>

    <script id="role-profiles" type="application/json">@json($roleProfiles)</script>
    @vite('resources/js/auth.js')
@endsection
