(function () {
    const chartPayloadScript = document.getElementById("dashboard-chart-payload");
    const parseJsonPayload = function (value, fallback) {
        if (typeof value !== "string" || value.trim() === "") {
            return fallback;
        }

        try {
            const parsed = JSON.parse(value);
            return parsed && typeof parsed === "object" ? parsed : fallback;
        } catch (error) {
            return fallback;
        }
    };

    const toNumericArray = function (value) {
        if (!Array.isArray(value)) {
            return [];
        }

        return value.map(function (item) {
            const normalized = Number(item);
            return Number.isFinite(normalized) ? normalized : 0;
        });
    };

    const baseChartPayload =
        parseJsonPayload(
            chartPayloadScript ? chartPayloadScript.textContent : "",
            null,
        ) ||
        (window.dashboardChartPayload &&
        typeof window.dashboardChartPayload === "object"
            ? window.dashboardChartPayload
            : {});

    const chartPayload = {
        weekLabels: Array.isArray(baseChartPayload.weekLabels)
            ? baseChartPayload.weekLabels.map(String)
            : [],
        weeklyProducts: toNumericArray(baseChartPayload.weeklyProducts),
        weeklyInventory: toNumericArray(baseChartPayload.weeklyInventory),
        monthLabels: Array.isArray(baseChartPayload.monthLabels)
            ? baseChartPayload.monthLabels.map(String)
            : [],
        monthlyProducts: toNumericArray(baseChartPayload.monthlyProducts),
        categoryLabels: Array.isArray(baseChartPayload.categoryLabels)
            ? baseChartPayload.categoryLabels.map(String)
            : [],
        categoryCounts: toNumericArray(baseChartPayload.categoryCounts),
        roleLabels: Array.isArray(baseChartPayload.roleLabels)
            ? baseChartPayload.roleLabels.map(String)
            : [],
        roleCounts: toNumericArray(baseChartPayload.roleCounts),
    };
    const progressBars = document.querySelectorAll(".dashboard-progress-bar");
    const counterEls = document.querySelectorAll("[data-counter-value]");
    const prefersReducedMotion = window.matchMedia(
        "(prefers-reduced-motion: reduce)",
    ).matches;
    const searchForm = document.querySelector("[data-dashboard-search-form]");
    const searchInput = document.getElementById("dashboard-search-input");
    const searchDropdown = document.getElementById("dashboard-search-dropdown");
    const searchOptionList = document.getElementById(
        "dashboard-search-option-list",
    );
    const searchOptions = Array.from(
        document.querySelectorAll("[data-search-option]"),
    );
    const searchEmpty = document.getElementById("dashboard-search-empty");
    let activeSearchIndex = -1;

    const visibleSearchOptions = function () {
        return searchOptions.filter(function (option) {
            return !option.classList.contains("hidden");
        });
    };

    const syncSearchActiveState = function () {
        const visibleOptions = visibleSearchOptions();

        if (!visibleOptions.length) {
            activeSearchIndex = -1;
        } else if (activeSearchIndex >= visibleOptions.length) {
            activeSearchIndex = 0;
        }

        searchOptions.forEach(function (option) {
            option.style.backgroundColor = "";
            option.setAttribute("aria-selected", "false");
        });

        if (activeSearchIndex < 0) {
            return;
        }

        const activeOption = visibleOptions[activeSearchIndex];
        if (!activeOption) {
            return;
        }

        activeOption.style.backgroundColor = "#fff3ea";
        activeOption.setAttribute("aria-selected", "true");
        activeOption.scrollIntoView({ block: "nearest" });
    };

    const formatCounter = function (element, value) {
        const type = element.getAttribute("data-counter-type");
        const decimals = Number(
            element.getAttribute("data-counter-decimals") || 0,
        );

        if (type === "currency") {
            element.textContent =
                "$" +
                value.toLocaleString(undefined, {
                    minimumFractionDigits: decimals,
                    maximumFractionDigits: decimals,
                });
            return;
        }

        element.textContent = Math.round(value).toLocaleString();
    };

    const animateCounters = function () {
        counterEls.forEach(function (element) {
            const target = Number(
                element.getAttribute("data-counter-value") || 0,
            );

            if (prefersReducedMotion) {
                formatCounter(element, target);
                return;
            }

            const observer = new IntersectionObserver(
                function (entries) {
                    entries.forEach(function (entry) {
                        if (!entry.isIntersecting) {
                            return;
                        }

                        const startedAt = performance.now();
                        const duration = 950;

                        const frame = function (now) {
                            const progress = Math.min(
                                (now - startedAt) / duration,
                                1,
                            );
                            const eased = 1 - Math.pow(1 - progress, 3);
                            formatCounter(element, target * eased);

                            if (progress < 1) {
                                window.requestAnimationFrame(frame);
                            }
                        };

                        window.requestAnimationFrame(frame);
                        observer.unobserve(entry.target);
                    });
                },
                {
                    threshold: 0.3,
                },
            );

            observer.observe(element);
        });
    };

    const animateProgressBars = function () {
        progressBars.forEach(function (bar, index) {
            const delay = prefersReducedMotion ? 0 : index * 120;
            window.setTimeout(function () {
                bar.classList.add("is-visible");
            }, delay);
        });
    };

    const createCharts = function () {
        if (typeof window.Chart === "undefined") {
            return;
        }

        window.Chart.defaults.font.family =
            "'Instrument Sans', 'Segoe UI', sans-serif";
        window.Chart.defaults.color = "#6b7280";

        const weeklyCtx = document.getElementById("weeklyOverviewChart");
        const monthlyCtx = document.getElementById("monthlyProductsChart");
        const categoryCtx = document.getElementById("categoryMixChart");
        const roleCtx = document.getElementById("roleDistributionChart");

        if (weeklyCtx) {
            const gradient = weeklyCtx
                .getContext("2d")
                .createLinearGradient(0, 0, 0, 260);
            gradient.addColorStop(0, "rgba(244, 160, 107, 0.35)");
            gradient.addColorStop(1, "rgba(244, 160, 107, 0.02)");

            new window.Chart(weeklyCtx, {
                type: "line",
                data: {
                    labels: chartPayload.weekLabels,
                    datasets: [
                        {
                            label: "Products Added",
                            data: chartPayload.weeklyProducts,
                            borderColor: "#d97f46",
                            backgroundColor: gradient,
                            fill: true,
                            borderWidth: 2.5,
                            tension: 0.36,
                            pointRadius: 3,
                            yAxisID: "y",
                        },
                        {
                            label: "Inventory Added ($)",
                            data: chartPayload.weeklyInventory,
                            borderColor: "#2f241f",
                            borderWidth: 2,
                            tension: 0.32,
                            pointRadius: 2.5,
                            yAxisID: "y1",
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        intersect: false,
                        mode: "index",
                    },
                    plugins: {
                        legend: {
                            position: "bottom",
                        },
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false,
                            },
                        },
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0,
                            },
                        },
                        y1: {
                            beginAtZero: true,
                            position: "right",
                            grid: {
                                drawOnChartArea: false,
                            },
                        },
                    },
                },
            });
        }

        if (monthlyCtx) {
            new window.Chart(monthlyCtx, {
                type: "bar",
                data: {
                    labels: chartPayload.monthLabels,
                    datasets: [
                        {
                            label: "Products",
                            data: chartPayload.monthlyProducts,
                            backgroundColor: "#f4a06b",
                            borderRadius: 10,
                            borderSkipped: false,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false,
                        },
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false,
                            },
                        },
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0,
                            },
                        },
                    },
                },
            });
        }

        if (categoryCtx) {
            new window.Chart(categoryCtx, {
                type: "doughnut",
                data: {
                    labels: chartPayload.categoryLabels,
                    datasets: [
                        {
                            data: chartPayload.categoryCounts,
                            backgroundColor: [
                                "#f4a06b",
                                "#d97f46",
                                "#8f5f3e",
                                "#4e3428",
                                "#f5c9a8",
                                "#fbdabf",
                            ],
                            borderWidth: 0,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: "66%",
                    plugins: {
                        legend: {
                            position: "bottom",
                        },
                    },
                },
            });
        }

        if (roleCtx) {
            new window.Chart(roleCtx, {
                type: "bar",
                data: {
                    labels: chartPayload.roleLabels,
                    datasets: [
                        {
                            label: "Users",
                            data: chartPayload.roleCounts,
                            backgroundColor: [
                                "#2f241f",
                                "#f4a06b",
                                "#d97f46",
                                "#b76b3f",
                            ],
                            borderRadius: 10,
                            borderSkipped: false,
                        },
                    ],
                },
                options: {
                    indexAxis: "y",
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false,
                        },
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0,
                            },
                        },
                        y: {
                            grid: {
                                display: false,
                            },
                        },
                    },
                },
            });
        }
    };

    const closeSearchDropdown = function () {
        if (searchDropdown) {
            searchDropdown.classList.add("hidden");
            activeSearchIndex = -1;
            syncSearchActiveState();
        }
    };

    const openSearchDropdown = function () {
        if (searchDropdown) {
            searchDropdown.classList.remove("hidden");
        }
    };

    const filterSearchOptions = function () {
        if (!searchOptionList) return;

        const keyword = (searchInput?.value || "").trim().toLowerCase();
        const showAll = keyword === "";
        const maxShown = 10;
        let visibleCount = 0;

        searchOptions.forEach(function (option, index) {
            const haystack = option.getAttribute("data-search-text") || "";
            const keywordMatched = showAll || haystack.includes(keyword);
            const isVisible =
                keywordMatched && (!showAll || index < maxShown);
            option.classList.toggle("hidden", !isVisible);

            if (isVisible) {
                visibleCount += 1;
            }
        });

        if (searchEmpty) {
            searchEmpty.classList.toggle("hidden", visibleCount > 0);
        }

        activeSearchIndex = visibleCount > 0 ? 0 : -1;
        syncSearchActiveState();
    };

    if (searchInput && searchDropdown) {
        searchInput.addEventListener("focus", function () {
            filterSearchOptions();
            openSearchDropdown();
        });

        searchInput.addEventListener("input", function () {
            filterSearchOptions();
            openSearchDropdown();
        });

        searchInput.addEventListener("keydown", function (event) {
            const visibleOptions = visibleSearchOptions();

            if (event.key === "Escape") {
                closeSearchDropdown();
                return;
            }

            if (!visibleOptions.length) {
                return;
            }

            if (event.key === "ArrowDown") {
                event.preventDefault();
                activeSearchIndex =
                    activeSearchIndex < 0
                        ? 0
                        : (activeSearchIndex + 1) % visibleOptions.length;
                syncSearchActiveState();
                return;
            }

            if (event.key === "ArrowUp") {
                event.preventDefault();
                activeSearchIndex =
                    activeSearchIndex <= 0
                        ? visibleOptions.length - 1
                        : activeSearchIndex - 1;
                syncSearchActiveState();
                return;
            }

            if (event.key === "Enter" && activeSearchIndex >= 0) {
                event.preventDefault();
                const option = visibleOptions[activeSearchIndex];
                if (!option) {
                    return;
                }

                const selectedValue = option.getAttribute("data-value") || "";
                searchInput.value = selectedValue;
                if (searchForm) {
                    searchForm.submit();
                }
            }
        });
    }

    searchOptions.forEach(function (option) {
        option.addEventListener("click", function () {
            const selectedValue = option.getAttribute("data-value") || "";
            if (searchInput) {
                searchInput.value = selectedValue;
            }
            if (searchForm) {
                searchForm.submit();
            }
        });
    });

    document.addEventListener("click", function (event) {
        if (!searchForm || !searchDropdown) return;

        const target = event.target;
        if (target instanceof Node && searchForm.contains(target)) {
            return;
        }

        closeSearchDropdown();
    });

    document.addEventListener("keydown", function (event) {
        if (event.key === "Escape") {
            closeSearchDropdown();
        }
    });

    animateCounters();
    animateProgressBars();
    createCharts();
})();
