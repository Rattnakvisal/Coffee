<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Coffee POS' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/css/index.css'])
</head>

<body class="min-h-screen bg-gradient-to-br from-[#ffe3cf] via-[#fdeedf] to-[#f7dfcd] text-[#2f241f]">
    @php
        $slotContent = trim((string) ($slot ?? ''));
        $hasContent = $slotContent !== '' || View::hasSection('content');
        $roles = collect($roles ?? []);
    @endphp

    <div class="min-h-screen p-6 lg:p-10">
        @if ($hasContent)
            {{ $slot ?? '' }}
            @yield('content')
        @else
            <main class="mx-auto flex min-h-[calc(100vh-5rem)] w-full max-w-6xl items-center justify-center">
                <section
                    class="anim-enter-up w-full rounded-[36px] border border-white/70 bg-white/70 p-8 shadow-2xl shadow-[#c98a5f]/20 backdrop-blur lg:p-12">
                    <div class="max-w-2xl anim-enter-left">
                        <p
                            class="anim-pop inline-flex items-center gap-2 rounded-full bg-[#fff1e5] px-4 py-1 text-sm font-semibold text-[#ad5e2f]">
                            <span class="h-2 w-2 rounded-full bg-[#f4a06b]"></span>
                            Coffee POS Workspace
                        </p>
                        <h1 class="anim-enter-up anim-delay-100 mt-5 text-4xl font-black leading-tight text-[#2f241f] sm:text-5xl">
                            Choose where you want to start
                        </h1>
                        <p class="anim-enter-up anim-delay-200 mt-4 text-base text-[#6a5145] sm:text-lg">
                            Jump into the right panel quickly with a clear role-based entry point.
                        </p>
                    </div>

                    @if ($roles->isEmpty())
                        <div
                            class="anim-enter-up anim-delay-300 mt-8 rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-700">
                            No active roles found. Run migrations and seeders to create admin and cashier roles.
                        </div>
                    @else
                        <div class="mt-10 grid gap-5 md:grid-cols-2">
                            @foreach ($roles as $role)
                                @php
                                    $styles = match ($role->slug) {
                                        'admin' => [
                                            'card' => 'bg-[#fff8f3]',
                                            'iconBg' => 'bg-[#2f241f]',
                                            'title' => 'Admin Dashboard',
                                            'subtitle' => 'Manage products, orders, reports, and your team in one place.
                                            ',
                                        ],
                                        'cashier' => [
                                            'card' => 'bg-white',
                                            'iconBg' => 'bg-[#f4a06b]',
                                            'title' => 'Cashier Panel',
                                            'subtitle' =>
                                                'Handle customer orders quickly with menu, cart, and checkout tools.',
                                        ],
                                        default => [
                                            'card' => 'bg-white',
                                            'iconBg' => 'bg-[#7a5c4e]',
                                            'title' => str($role->name)->headline() . ' Panel',
                                            'subtitle' => $role->description ?: 'Sign in with this role.',
                                        ],
                                    };
                                @endphp

                                <a href="{{ route('login.form', ['role' => $role->slug]) }}"
                                    class="group relative block overflow-hidden rounded-3xl border border-[#f3d6c3] {{ $styles['card'] }} p-6 shadow-lg shadow-[#d9a178]/10 transition hover:-translate-y-1 hover:shadow-2xl anim-pop anim-stagger"
                                    style="--stagger: {{ $loop->index + 2 }};">
                                    <div class="flex items-start justify-between">
                                        <span
                                            class="flex h-14 w-14 items-center justify-center rounded-2xl {{ $styles['iconBg'] }} text-white">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M12 3 4.5 6v6.75c0 4.5 3.068 8.693 7.5 9.75 4.432-1.057 7.5-5.25 7.5-9.75V6L12 3Z" />
                                            </svg>
                                        </span>
                                        <span
                                            class="rounded-full bg-[#ffe7d5] px-3 py-1 text-xs font-bold uppercase tracking-[0.12em] text-[#b16231]">
                                            {{ str($role->name)->headline() }}
                                        </span>
                                    </div>

                                    <h2 class="mt-5 text-2xl font-bold text-[#2f241f]">{{ $styles['title'] }}</h2>
                                    <p class="mt-2 text-sm text-[#6a5145]">{{ $styles['subtitle'] }}</p>

                                    <span
                                        class="mt-5 inline-flex items-center gap-2 text-sm font-semibold text-[#b16231]">
                                        Open {{ str($role->name)->headline() }}
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                            class="h-4 w-4 transition group-hover:translate-x-1" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                                        </svg>
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </section>
            </main>
        @endif
    </div>
</body>

</html>
