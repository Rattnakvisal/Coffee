@extends('layouts.app')

@section('content')
@php
    $roles = $roles ?? collect();
    $selectedRole = $selectedRole ?? $roles->first();
    $isAdmin = $selectedRole?->slug === 'admin';
    $roleLabel = $selectedRole ? str($selectedRole->name)->headline() : 'User';
    $roleSubtitle = match ($selectedRole?->slug) {
        'admin' => 'Secure access for inventory, team, and reporting tools.',
        'cashier' => 'Quick access for orders, checkout, and customer flow.',
        default => 'Sign in with your assigned role and continue to your panel.',
    };
    $roleGradient = $isAdmin
        ? 'linear-gradient(140deg, #2f241f, #5b4337)'
        : 'linear-gradient(140deg, #f4a06b, #d46f45)';
    $roleButton = $isAdmin ? '#2f241f' : '#f4a06b';
    $roleButtonHover = $isAdmin ? '#201813' : '#df8855';
@endphp

<main class="mx-auto flex min-h-[calc(100vh-5rem)] w-full max-w-6xl items-center justify-center">
    <section class="anim-enter-up relative w-full overflow-hidden rounded-[36px] border border-white/70 bg-white/80 shadow-2xl shadow-[#c98a5f]/20 backdrop-blur">
        <div class="anim-float pointer-events-none absolute -left-20 top-10 h-56 w-56 rounded-full bg-[#ffd9bf]/70 blur-3xl"></div>
        <div class="anim-float pointer-events-none absolute -bottom-16 right-20 h-56 w-56 rounded-full bg-[#f3c6a7]/70 blur-3xl" style="animation-delay: 0.4s;"></div>

        <div class="relative flex flex-col lg:flex-row">
            <div class="anim-enter-left w-full p-6 sm:p-10 lg:w-[60%]">
                <div class="mx-auto w-full max-w-2xl">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <a href="{{ route('welcome') }}" class="anim-pop inline-flex items-center gap-2 text-sm font-semibold text-[#8b5f45] hover:text-[#2f241f]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                        </svg>
                        Back
                    </a>

                    <div class="anim-enter-up anim-delay-100 inline-flex flex-wrap rounded-full bg-[#f6ece4] p-1 text-sm font-semibold">
                        @foreach ($roles as $roleOption)
                            @php
                                $isCurrent = $selectedRole && $selectedRole->id === $roleOption->id;
                            @endphp
                            <a
                                href="{{ route('login.form', ['role' => $roleOption->slug]) }}"
                                class="{{ $isCurrent ? 'bg-white text-[#2f241f] shadow-sm' : 'text-[#7a5c4e] hover:text-[#2f241f]' }} rounded-full px-4 py-1.5 transition anim-pop anim-stagger"
                                style="--stagger: {{ $loop->index + 1 }};"
                            >
                                {{ str($roleOption->name)->headline() }}
                            </a>
                        @endforeach
                    </div>
                </div>

                <div class="anim-enter-up anim-delay-200 mt-7">
                    <p class="text-xs font-semibold uppercase tracking-[0.14em] text-[#b16231]">{{ $roleLabel }} Access</p>
                    <h2 class="mt-2 text-3xl font-black text-[#2f241f]">Sign in to continue</h2>
                </div>

                @if ($errors->any())
                    <div class="anim-pop mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        Please check your login details and try again.
                    </div>
                @endif

                <form method="POST" action="{{ route('login.submit', ['role' => $selectedRole->slug]) }}" class="anim-enter-up anim-delay-300 mt-6 space-y-4">
                    @csrf

                    <div>
                        <label for="email" class="mb-1 block text-sm font-semibold text-[#5f4b40]">Email</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-[#a47d67]">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5A2.25 2.25 0 0 1 19.5 19.5h-15A2.25 2.25 0 0 1 2.25 17.25V6.75A2.25 2.25 0 0 1 4.5 4.5h15A2.25 2.25 0 0 1 21.75 6.75Zm-19.5.53 9.16 6.107a1.125 1.125 0 0 0 1.18 0l9.16-6.106" />
                                </svg>
                            </span>
                            <input
                                id="email"
                                name="email"
                                type="email"
                                value="{{ old('email') }}"
                                required
                                class="w-full rounded-xl border border-[#ecd9cc] bg-white py-3 pl-12 pr-4 outline-none transition focus:border-[#f4a06b] focus:ring-2 focus:ring-[#f4a06b]/20"
                                placeholder="you@example.com"
                            >
                        </div>
                        @error('email')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="mb-1 block text-sm font-semibold text-[#5f4b40]">Password</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-[#a47d67]">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 0 0-9 0v3.75m-2.25 0h13.5A2.25 2.25 0 0 1 21 12.75v6A2.25 2.25 0 0 1 18.75 21h-13.5A2.25 2.25 0 0 1 3 18.75v-6A2.25 2.25 0 0 1 5.25 10.5Z" />
                                </svg>
                            </span>
                            <input
                                id="password"
                                name="password"
                                type="password"
                                required
                                class="w-full rounded-xl border border-[#ecd9cc] bg-white py-3 pl-12 pr-14 outline-none transition focus:border-[#f4a06b] focus:ring-2 focus:ring-[#f4a06b]/20"
                                placeholder="Enter password"
                            >
                            <button
                                type="button"
                                data-toggle-password
                                data-target="password"
                                class="absolute right-3 top-1/2 -translate-y-1/2 rounded-lg px-2 py-1 text-xs font-semibold text-[#8b5f45] hover:bg-[#f6ece4]"
                            >
                                Show
                            </button>
                        </div>
                        @error('password')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-between text-sm">
                        <label class="inline-flex items-center gap-2 text-[#6e5549]">
                            <input type="checkbox" name="remember" class="h-4 w-4 rounded border-[#d8c3b4] text-[#f4a06b] focus:ring-[#f4a06b]/30">
                            Remember me
                        </label>
                    </div>

                    <button
                        type="submit"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl py-3.5 font-semibold text-white shadow-lg shadow-[#e1b090]/40 transition"
                        style="background-color: {{ $roleButton }};"
                        onmouseover="this.style.backgroundColor='{{ $roleButtonHover }}'"
                        onmouseout="this.style.backgroundColor='{{ $roleButton }}'"
                    >
                        Continue to {{ $roleLabel }}
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                        </svg>
                    </button>
                </form>
                </div>
            </div>

            <aside class="anim-enter-right w-full bg-white/50 p-6 sm:p-10 lg:w-[40%]">
                <div class="anim-float mx-auto h-full max-w-sm rounded-3xl p-7 text-white shadow-xl" style="background: {{ $roleGradient }};">
                    <p class="inline-flex items-center gap-2 rounded-full bg-white/20 px-3 py-1 text-xs font-semibold uppercase tracking-[0.11em]">
                        Role profile
                    </p>
                    <h3 class="mt-4 text-2xl font-black">{{ $roleLabel }}</h3>
                    <p class="mt-2 text-sm text-white/80">{{ $roleSubtitle }}</p>

                    @if ($selectedRole?->description)
                        <p class="mt-6 rounded-2xl bg-white/15 px-4 py-3 text-sm leading-relaxed text-white/90">
                            {{ $selectedRole->description }}
                        </p>
                    @endif
                </div>
            </aside>
        </div>
    </section>
</main>

<script>
    document.addEventListener('click', function (event) {
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
