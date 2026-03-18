@extends('layouts.app')

@section('content')
    @php
        $currentUser = auth()->user();
        $initials = collect(explode(' ', $currentUser->name))
            ->filter()
            ->map(fn(string $namePart): string => strtoupper(substr($namePart, 0, 1)))
            ->take(2)
            ->implode('');
    @endphp

    <div class="anim-enter-up w-full min-h-screen overflow-hidden lg:overflow-visible bg-white/85">
        <div class="grid min-h-screen grid-cols-1 lg:grid-cols-12">
            @include('admin.sidebar.sidebar', ['activeAdminMenu' => 'dashboard'])

            <main class="anim-enter-right bg-[#f8f8f8] p-4 pt-20 sm:p-6 sm:pt-20 lg:col-span-9 lg:p-8 lg:pt-8 xl:col-span-10">
                <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
                    <form action="#" method="GET" class="relative w-full max-w-xl">
                        <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="1.9">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="m21 21-4.35-4.35m1.35-5.4a7.5 7.5 0 1 1-15 0 7.5 7.5 0 0 1 15 0Z" />
                            </svg>
                        </span>
                        <input type="text" name="q" placeholder="Search products, orders, reports..."
                            class="w-full rounded-2xl border border-slate-200 bg-white py-3 pl-12 pr-4 text-sm outline-none transition focus:border-[#f4a06b] focus:ring-2 focus:ring-[#f4a06b]/20">
                    </form>

                    <div class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-3 py-2">
                        <span
                            class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-[#2f241f] text-sm font-bold text-white">{{ $initials }}</span>
                        <div>
                            <p class="text-sm font-semibold text-[#2f241f]">{{ $currentUser->name }}</p>
                            <p class="text-xs text-slate-500">{{ str($currentUser->role?->name ?? 'Admin')->headline() }}</p>
                        </div>
                    </div>
                </div>

                <div class="anim-enter-up anim-delay-100 flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p
                            class="inline-flex items-center gap-2 rounded-full bg-[#ffe7d5] px-3 py-1 text-xs font-semibold uppercase tracking-[0.12em] text-[#b16231]">
                            <span class="h-2 w-2 rounded-full bg-[#f4a06b]"></span>
                            Admin
                        </p>
                        <h2 class="mt-3 text-3xl font-black text-[#2f241f]">Dashboard Overview</h2>
                        <p class="mt-1 text-sm text-gray-500">Dynamic data with animated counters and charts.</p>
                    </div>

                    <a href="{{ route('admin.products.index') }}"
                        class="anim-pop anim-delay-200 inline-flex items-center gap-2 rounded-xl bg-[#f4a06b] px-5 py-3 font-semibold text-white shadow-lg shadow-[#e9b08d] transition hover:brightness-105">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Add Product
                    </a>
                </div>

                <div class="mt-8 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <div class="anim-pop anim-delay-200 rounded-3xl bg-white p-5 shadow-sm ring-1 ring-black/5">
                        <p class="text-sm text-gray-500">Inventory Value</p>
                        <h3 class="mt-3 text-3xl font-black text-[#2f241f]" data-counter-value="{{ $stats['inventoryValue'] }}"
                            data-counter-type="currency" data-counter-decimals="2">$0.00</h3>
                        <p
                            class="mt-2 text-xs font-medium {{ $stats['inventoryGrowth']['isPositive'] ? 'text-emerald-600' : 'text-rose-600' }}">
                            {{ $stats['inventoryGrowth']['text'] }}
                        </p>
                    </div>

                    <div class="anim-pop anim-delay-300 rounded-3xl bg-white p-5 shadow-sm ring-1 ring-black/5">
                        <p class="text-sm text-gray-500">Products Today</p>
                        <h3 class="mt-3 text-3xl font-black text-[#2f241f]" data-counter-value="{{ $stats['productsToday'] }}"
                            data-counter-type="number">0</h3>
                        <p
                            class="mt-2 text-xs font-medium {{ $stats['productsGrowth']['isPositive'] ? 'text-emerald-600' : 'text-rose-600' }}">
                            {{ $stats['productsGrowth']['text'] }}
                        </p>
                    </div>

                    <div class="anim-pop anim-delay-400 rounded-3xl bg-white p-5 shadow-sm ring-1 ring-black/5">
                        <p class="text-sm text-gray-500">Active Products</p>
                        <h3 class="mt-3 text-3xl font-black text-[#2f241f]"
                            data-counter-value="{{ $stats['activeProductsCount'] }}" data-counter-type="number">0</h3>
                        <p class="mt-2 text-xs font-medium text-slate-500">
                            {{ number_format($stats['categoriesCount']) }} active categories
                        </p>
                    </div>

                    <div class="anim-pop anim-delay-500 rounded-3xl bg-white p-5 shadow-sm ring-1 ring-black/5">
                        <p class="text-sm text-gray-500">Cashier Accounts</p>
                        <h3 class="mt-3 text-3xl font-black text-[#2f241f]" data-counter-value="{{ $stats['cashiersCount'] }}"
                            data-counter-type="number">0</h3>
                        <p
                            class="mt-2 text-xs font-medium {{ $stats['usersGrowth']['isPositive'] ? 'text-emerald-600' : 'text-rose-600' }}">
                            {{ $stats['usersGrowth']['text'] }} ({{ number_format($stats['adminsCount']) }} admins)
                        </p>
                    </div>
                </div>

                <div class="mt-8 grid grid-cols-1 gap-6 xl:grid-cols-3">
                    <section
                        class="anim-enter-up anim-delay-200 xl:col-span-2 rounded-3xl bg-white p-6 shadow-sm ring-1 ring-black/5">
                        <div class="mb-4 flex items-center justify-between">
                            <h3 class="text-xl font-bold text-[#2f241f]">Weekly Activity</h3>
                            <span class="rounded-full bg-[#fff2e7] px-3 py-1 text-xs font-semibold text-[#be6f3c]">Last 7
                                days</span>
                        </div>
                        <div class="dashboard-chart-wrap">
                            <canvas id="weeklyOverviewChart"></canvas>
                        </div>
                    </section>

                    <section class="anim-enter-up anim-delay-300 rounded-3xl bg-white p-6 shadow-sm ring-1 ring-black/5">
                        <h3 class="mb-4 text-xl font-bold text-[#2f241f]">Category Mix</h3>
                        <div class="dashboard-chart-wrap dashboard-chart-wrap--compact">
                            <canvas id="categoryMixChart"></canvas>
                        </div>
                    </section>
                </div>

                <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-3">
                    <section
                        class="anim-enter-up anim-delay-300 xl:col-span-2 rounded-3xl bg-white p-6 shadow-sm ring-1 ring-black/5">
                        <h3 class="mb-4 text-xl font-bold text-[#2f241f]">Monthly Product Growth</h3>
                        <div class="dashboard-chart-wrap">
                            <canvas id="monthlyProductsChart"></canvas>
                        </div>
                    </section>

                    <section class="anim-enter-up anim-delay-400 rounded-3xl bg-white p-6 shadow-sm ring-1 ring-black/5">
                        <h3 class="mb-4 text-xl font-bold text-[#2f241f]">Team Roles</h3>
                        <div class="dashboard-chart-wrap dashboard-chart-wrap--compact">
                            <canvas id="roleDistributionChart"></canvas>
                        </div>
                    </section>
                </div>

                <div class="mt-8 grid grid-cols-1 gap-6 xl:grid-cols-3">
                    <section
                        class="anim-enter-up anim-delay-300 xl:col-span-2 rounded-3xl bg-white p-6 shadow-sm ring-1 ring-black/5">
                        <div class="mb-4 flex items-center justify-between">
                            <h3 class="text-xl font-bold text-[#2f241f]">Recent Products</h3>
                            <a href="{{ route('admin.products.index') }}"
                                class="text-sm font-medium text-[#d97f46] hover:underline">View all</a>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[620px] text-left">
                                <thead>
                                    <tr class="border-b border-slate-200 text-sm text-gray-500">
                                        <th class="pb-3 font-medium">Product</th>
                                        <th class="pb-3 font-medium">Category</th>
                                        <th class="pb-3 font-medium">Price</th>
                                        <th class="pb-3 font-medium">Status</th>
                                        <th class="pb-3 font-medium">Added</th>
                                    </tr>
                                </thead>
                                <tbody class="text-sm">
                                    @forelse ($recentProducts as $product)
                                        <tr class="border-b border-slate-100 last:border-b-0">
                                            <td class="py-4 font-semibold text-[#2f241f]">{{ $product->name }}</td>
                                            <td class="text-slate-500">{{ $product->category?->name ?? 'Uncategorized' }}</td>
                                            <td class="font-semibold">${{ number_format((float) $product->price, 2) }}</td>
                                            <td>
                                                <span
                                                    class="rounded-full px-3 py-1 text-xs font-semibold {{ $product->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-600' }}">
                                                    {{ $product->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                            <td class="text-slate-500">{{ $product->created_at?->diffForHumans() ?? '-' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="py-10 text-center text-sm text-slate-500">
                                                No products yet. Add your first product to populate the dashboard.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <section class="anim-enter-up anim-delay-400 rounded-3xl bg-white p-6 shadow-sm ring-1 ring-black/5">
                        <h3 class="text-xl font-bold text-[#2f241f]">Top Products (By Price)</h3>
                        <div class="mt-5 space-y-5 text-sm">
                            @forelse ($topProducts as $product)
                                @php
                                    $progress = $topProductsMaxPrice > 0 ? ((float) $product->price / $topProductsMaxPrice) * 100 : 0;
                                @endphp
                                <div>
                                    <div class="mb-2 flex items-center justify-between gap-3">
                                        <span class="truncate pr-2">{{ $product->name }}</span>
                                        <span class="font-semibold">${{ number_format((float) $product->price, 2) }}</span>
                                    </div>
                                    <div class="h-2 rounded-full bg-slate-100">
                                        <div class="dashboard-progress-bar h-2 rounded-full bg-[#f4a06b]"
                                            style="--progress-width: {{ round($progress, 2) }}%;"></div>
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-slate-500">No product data available yet.</p>
                            @endforelse
                        </div>
                    </section>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.8/dist/chart.umd.min.js"></script>
    <script>
        (function() {
            const chartPayload = @json($charts);
            const progressBars = document.querySelectorAll('.dashboard-progress-bar');
            const counterEls = document.querySelectorAll('[data-counter-value]');
            const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            const formatCounter = function(element, value) {
                const type = element.getAttribute('data-counter-type');
                const decimals = Number(element.getAttribute('data-counter-decimals') || 0);

                if (type === 'currency') {
                    element.textContent = '$' + value.toLocaleString(undefined, {
                        minimumFractionDigits: decimals,
                        maximumFractionDigits: decimals,
                    });
                    return;
                }

                element.textContent = Math.round(value).toLocaleString();
            };

            const animateCounters = function() {
                counterEls.forEach(function(element) {
                    const target = Number(element.getAttribute('data-counter-value') || 0);

                    if (prefersReducedMotion) {
                        formatCounter(element, target);
                        return;
                    }

                    const observer = new IntersectionObserver(function(entries) {
                        entries.forEach(function(entry) {
                            if (!entry.isIntersecting) {
                                return;
                            }

                            const startedAt = performance.now();
                            const duration = 950;

                            const frame = function(now) {
                                const progress = Math.min((now - startedAt) / duration, 1);
                                const eased = 1 - Math.pow(1 - progress, 3);
                                formatCounter(element, target * eased);

                                if (progress < 1) {
                                    window.requestAnimationFrame(frame);
                                }
                            };

                            window.requestAnimationFrame(frame);
                            observer.unobserve(entry.target);
                        });
                    }, {
                        threshold: 0.3
                    });

                    observer.observe(element);
                });
            };

            const animateProgressBars = function() {
                progressBars.forEach(function(bar, index) {
                    const delay = prefersReducedMotion ? 0 : index * 120;
                    window.setTimeout(function() {
                        bar.classList.add('is-visible');
                    }, delay);
                });
            };

            const createCharts = function() {
                if (typeof window.Chart === 'undefined') {
                    return;
                }

                window.Chart.defaults.font.family = "'Instrument Sans', 'Segoe UI', sans-serif";
                window.Chart.defaults.color = '#6b7280';

                const weeklyCtx = document.getElementById('weeklyOverviewChart');
                const monthlyCtx = document.getElementById('monthlyProductsChart');
                const categoryCtx = document.getElementById('categoryMixChart');
                const roleCtx = document.getElementById('roleDistributionChart');

                if (weeklyCtx) {
                    const gradient = weeklyCtx.getContext('2d').createLinearGradient(0, 0, 0, 260);
                    gradient.addColorStop(0, 'rgba(244, 160, 107, 0.35)');
                    gradient.addColorStop(1, 'rgba(244, 160, 107, 0.02)');

                    new window.Chart(weeklyCtx, {
                        type: 'line',
                        data: {
                            labels: chartPayload.weekLabels,
                            datasets: [{
                                    label: 'Products Added',
                                    data: chartPayload.weeklyProducts,
                                    borderColor: '#d97f46',
                                    backgroundColor: gradient,
                                    fill: true,
                                    borderWidth: 2.5,
                                    tension: 0.36,
                                    pointRadius: 3,
                                    yAxisID: 'y'
                                },
                                {
                                    label: 'Inventory Added ($)',
                                    data: chartPayload.weeklyInventory,
                                    borderColor: '#2f241f',
                                    borderWidth: 2,
                                    tension: 0.32,
                                    pointRadius: 2.5,
                                    yAxisID: 'y1'
                                },
                            ],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            interaction: {
                                intersect: false,
                                mode: 'index'
                            },
                            plugins: {
                                legend: {
                                    position: 'bottom'
                                },
                            },
                            scales: {
                                x: {
                                    grid: {
                                        display: false
                                    }
                                },
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        precision: 0
                                    }
                                },
                                y1: {
                                    beginAtZero: true,
                                    position: 'right',
                                    grid: {
                                        drawOnChartArea: false
                                    }
                                }
                            },
                        },
                    });
                }

                if (monthlyCtx) {
                    new window.Chart(monthlyCtx, {
                        type: 'bar',
                        data: {
                            labels: chartPayload.monthLabels,
                            datasets: [{
                                label: 'Products',
                                data: chartPayload.monthlyProducts,
                                backgroundColor: '#f4a06b',
                                borderRadius: 10,
                                borderSkipped: false,
                            }],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                }
                            },
                            scales: {
                                x: {
                                    grid: {
                                        display: false
                                    }
                                },
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        precision: 0
                                    }
                                },
                            },
                        },
                    });
                }

                if (categoryCtx) {
                    new window.Chart(categoryCtx, {
                        type: 'doughnut',
                        data: {
                            labels: chartPayload.categoryLabels,
                            datasets: [{
                                data: chartPayload.categoryCounts,
                                backgroundColor: ['#f4a06b', '#d97f46', '#8f5f3e', '#4e3428', '#f5c9a8', '#fbdabf'],
                                borderWidth: 0,
                            }],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: '66%',
                            plugins: {
                                legend: {
                                    position: 'bottom'
                                },
                            },
                        },
                    });
                }

                if (roleCtx) {
                    new window.Chart(roleCtx, {
                        type: 'bar',
                        data: {
                            labels: chartPayload.roleLabels,
                            datasets: [{
                                label: 'Users',
                                data: chartPayload.roleCounts,
                                backgroundColor: ['#2f241f', '#f4a06b', '#d97f46', '#b76b3f'],
                                borderRadius: 10,
                                borderSkipped: false,
                            }],
                        },
                        options: {
                            indexAxis: 'y',
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                }
                            },
                            scales: {
                                x: {
                                    beginAtZero: true,
                                    ticks: {
                                        precision: 0
                                    }
                                },
                                y: {
                                    grid: {
                                        display: false
                                    }
                                },
                            },
                        },
                    });
                }
            };

            animateCounters();
            animateProgressBars();
            createCharts();
        })();
    </script>
@endsection
