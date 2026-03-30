(function () {
    const payloadScript = document.getElementById("admin-report-payload");
    const counterEls = document.querySelectorAll("[data-counter-value]");
    const progressBars = document.querySelectorAll(".dashboard-progress-bar");
    const prefersReducedMotion = window.matchMedia(
        "(prefers-reduced-motion: reduce)",
    ).matches;

    const palette = {
        deep: "#2f241f",
        primary: "#f4a06b",
        secondary: "#d97f46",
        accent: "#8f5f3e",
        mint: "#ffe7d5",
        soft: "#fff4ec",
        text: "#6b7280",
    };

    const parsePayload = function () {
        if (!payloadScript) {
            return {};
        }

        try {
            const parsed = JSON.parse(payloadScript.textContent || "{}");
            return parsed && typeof parsed === "object" ? parsed : {};
        } catch (error) {
            return {};
        }
    };

    const toStringArray = function (value) {
        if (!Array.isArray(value)) {
            return [];
        }

        return value.map(function (item) {
            return String(item ?? "");
        });
    };

    const toNumberArray = function (value) {
        if (!Array.isArray(value)) {
            return [];
        }

        return value.map(function (item) {
            const normalized = Number(item);
            return Number.isFinite(normalized) ? normalized : 0;
        });
    };

    const payload = parsePayload();
    const trendData = payload.trend || {};
    const paymentsData = payload.payments || {};
    const topItemsData = payload.topItems || {};
    const statusesData = payload.statuses || {};
    const categoriesData = payload.categories || {};
    const comparisonData = payload.comparison || {};

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

        if (decimals > 0) {
            element.textContent = value.toLocaleString(undefined, {
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
                        const duration = 900;

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
                    threshold: 0.25,
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

    const currencyFormatter = new Intl.NumberFormat(undefined, {
        style: "currency",
        currency: "USD",
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });

    const numberFormatter = new Intl.NumberFormat(undefined);

    const createCharts = function () {
        if (typeof window.Chart === "undefined") {
            return;
        }

        window.Chart.defaults.font.family =
            "'Instrument Sans', 'Segoe UI', sans-serif";
        window.Chart.defaults.color = palette.text;

        const trendCtx = document.getElementById("adminReportsTrendChart");
        const paymentCtx = document.getElementById("adminReportsPaymentChart");
        const topItemsCtx = document.getElementById(
            "adminReportsTopItemsChart",
        );
        const statusCtx = document.getElementById("adminReportsStatusChart");
        const categoryCtx = document.getElementById(
            "adminReportsCategoryChart",
        );
        const comparisonCtx = document.getElementById(
            "adminReportsComparisonChart",
        );

        if (trendCtx) {
            const trendContext = trendCtx.getContext("2d");
            const revenueGradient = trendContext.createLinearGradient(
                0,
                0,
                0,
                300,
            );
            revenueGradient.addColorStop(0, "rgba(244, 160, 107, 0.82)");
            revenueGradient.addColorStop(1, "rgba(244, 160, 107, 0.18)");

            new window.Chart(trendCtx, {
                type: "bar",
                data: {
                    labels: toStringArray(trendData.labels),
                    datasets: [
                        {
                            label: "Revenue",
                            data: toNumberArray(trendData.revenue),
                            backgroundColor: revenueGradient,
                            borderRadius: 10,
                            borderSkipped: false,
                            yAxisID: "y",
                            order: 2,
                        },
                        {
                            label: "Orders",
                            data: toNumberArray(trendData.orders),
                            borderColor: palette.deep,
                            backgroundColor: "rgba(47, 36, 31, 0.14)",
                            pointBackgroundColor: palette.deep,
                            pointRadius: 3,
                            borderWidth: 2,
                            tension: 0.35,
                            type: "line",
                            yAxisID: "y1",
                            order: 1,
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
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    if (context.dataset.label === "Revenue") {
                                        return (
                                            "Revenue: " +
                                            currencyFormatter.format(
                                                Number(context.raw || 0),
                                            )
                                        );
                                    }

                                    return (
                                        "Orders: " +
                                        numberFormatter.format(
                                            Number(context.raw || 0),
                                        )
                                    );
                                },
                            },
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
                                callback: function (value) {
                                    return "$" + Number(value).toLocaleString();
                                },
                            },
                        },
                        y1: {
                            beginAtZero: true,
                            position: "right",
                            grid: {
                                drawOnChartArea: false,
                            },
                            ticks: {
                                precision: 0,
                            },
                        },
                    },
                },
            });
        }

        if (comparisonCtx) {
            new window.Chart(comparisonCtx, {
                type: "bar",
                data: {
                    labels: toStringArray(comparisonData.labels),
                    datasets: [
                        {
                            label: "Orders",
                            data: toNumberArray(comparisonData.orders),
                            backgroundColor: palette.primary,
                            borderRadius: 10,
                            borderSkipped: false,
                            yAxisID: "y",
                        },
                        {
                            label: "Items",
                            data: toNumberArray(comparisonData.items),
                            backgroundColor: palette.accent,
                            borderRadius: 10,
                            borderSkipped: false,
                            yAxisID: "y",
                        },
                        {
                            label: "Revenue",
                            data: toNumberArray(comparisonData.revenue),
                            type: "line",
                            borderColor: palette.deep,
                            backgroundColor: "rgba(47, 36, 31, 0.18)",
                            borderWidth: 2,
                            pointRadius: 3,
                            tension: 0.35,
                            yAxisID: "y1",
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: "bottom",
                        },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    const label = context.dataset.label || "";
                                    if (label === "Revenue") {
                                        return (
                                            label +
                                            ": " +
                                            currencyFormatter.format(
                                                Number(context.raw || 0),
                                            )
                                        );
                                    }

                                    return (
                                        label +
                                        ": " +
                                        numberFormatter.format(
                                            Number(context.raw || 0),
                                        )
                                    );
                                },
                            },
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
                            ticks: {
                                callback: function (value) {
                                    return "$" + Number(value).toLocaleString();
                                },
                            },
                        },
                    },
                },
            });
        }

        if (paymentCtx) {
            const paymentLabels = toStringArray(paymentsData.labels);
            const paymentRevenue = toNumberArray(paymentsData.revenue);
            const hasPaymentData = paymentLabels.length > 0;

            new window.Chart(paymentCtx, {
                type: "doughnut",
                data: {
                    labels: hasPaymentData ? paymentLabels : ["No Data"],
                    datasets: [
                        {
                            data: hasPaymentData ? paymentRevenue : [1],
                            backgroundColor: hasPaymentData
                                ? [
                                      "#f4a06b",
                                      "#d97f46",
                                      "#8f5f3e",
                                      "#2f241f",
                                      "#f6d3bf",
                                  ]
                                : ["#f3e2d6"],
                            borderWidth: 0,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: "68%",
                    plugins: {
                        legend: {
                            position: "bottom",
                        },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    if (!hasPaymentData) {
                                        return "No payment data";
                                    }

                                    return (
                                        context.label +
                                        ": " +
                                        currencyFormatter.format(
                                            Number(context.raw || 0),
                                        )
                                    );
                                },
                            },
                        },
                    },
                },
            });
        }

        if (statusCtx) {
            const statusLabels = toStringArray(statusesData.labels);
            const statusOrders = toNumberArray(statusesData.orders);
            const hasStatusData = statusLabels.length > 0;

            new window.Chart(statusCtx, {
                type: "doughnut",
                data: {
                    labels: hasStatusData ? statusLabels : ["No Data"],
                    datasets: [
                        {
                            data: hasStatusData ? statusOrders : [1],
                            backgroundColor: hasStatusData
                                ? [
                                      "#2f241f",
                                      "#f4a06b",
                                      "#d97f46",
                                      "#8f5f3e",
                                      "#f6d3bf",
                                  ]
                                : ["#f3e2d6"],
                            borderWidth: 0,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: "64%",
                    plugins: {
                        legend: {
                            position: "bottom",
                        },
                    },
                },
            });
        }

        if (categoryCtx) {
            const categoryLabels = toStringArray(categoriesData.labels);
            const categoryRevenue = toNumberArray(categoriesData.revenue);
            const hasCategoryData = categoryLabels.length > 0;

            new window.Chart(categoryCtx, {
                type: "pie",
                data: {
                    labels: hasCategoryData ? categoryLabels : ["No Data"],
                    datasets: [
                        {
                            data: hasCategoryData ? categoryRevenue : [1],
                            backgroundColor: hasCategoryData
                                ? [
                                      "#2f241f",
                                      "#8f5f3e",
                                      "#d97f46",
                                      "#f4a06b",
                                      "#f6d3bf",
                                      "#fff2e7",
                                  ]
                                : ["#f3e2d6"],
                            borderWidth: 1,
                            borderColor: "rgba(255, 255, 255, 0.6)",
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: "bottom",
                        },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    if (!hasCategoryData) {
                                        return "No category data";
                                    }

                                    return (
                                        context.label +
                                        ": " +
                                        currencyFormatter.format(
                                            Number(context.raw || 0),
                                        )
                                    );
                                },
                            },
                        },
                    },
                },
            });
        }

        if (topItemsCtx) {
            const labels = toStringArray(topItemsData.labels);
            const qtyValues = toNumberArray(topItemsData.qty);
            const revenueValues = toNumberArray(topItemsData.revenue);

            new window.Chart(topItemsCtx, {
                type: "bar",
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: "Qty Sold",
                            data: qtyValues,
                            backgroundColor: palette.primary,
                            borderRadius: 10,
                            borderSkipped: false,
                            xAxisID: "x",
                        },
                        {
                            label: "Revenue",
                            data: revenueValues,
                            backgroundColor: "rgba(244, 160, 107, 0.28)",
                            borderColor: palette.deep,
                            borderWidth: 1,
                            borderRadius: 10,
                            borderSkipped: false,
                            xAxisID: "x1",
                        },
                    ],
                },
                options: {
                    indexAxis: "y",
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: "bottom",
                        },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    if (context.dataset.label === "Revenue") {
                                        return (
                                            "Revenue: " +
                                            currencyFormatter.format(
                                                Number(context.raw || 0),
                                            )
                                        );
                                    }

                                    return (
                                        "Qty Sold: " +
                                        numberFormatter.format(
                                            Number(context.raw || 0),
                                        )
                                    );
                                },
                            },
                        },
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0,
                            },
                            grid: {
                                color: "rgba(47, 36, 31, 0.08)",
                            },
                        },
                        x1: {
                            display: false,
                            beginAtZero: true,
                            grid: {
                                drawOnChartArea: false,
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

    const printButton = document.querySelector("[data-report-print]");
    if (printButton) {
        printButton.addEventListener("click", function () {
            window.print();
        });
    }

    const filterToggleButton = document.querySelector(
        "[data-report-filter-toggle]",
    );
    const filterPanel = document.querySelector("[data-report-filter-panel]");
    const filterToggleLabel = document.querySelector(
        "[data-report-filter-toggle-label]",
    );
    if (filterToggleButton && filterPanel) {
        const setFilterVisibility = function (isVisible) {
            filterPanel.classList.toggle("hidden", !isVisible);
            filterToggleButton.setAttribute(
                "aria-expanded",
                isVisible ? "true" : "false",
            );
            if (filterToggleLabel) {
                filterToggleLabel.textContent = isVisible
                    ? "Hide Filter"
                    : "Filter";
            }
        };

        let isFilterVisible =
            filterToggleButton.getAttribute("aria-expanded") === "true";
        setFilterVisibility(isFilterVisible);

        filterToggleButton.addEventListener("click", function () {
            isFilterVisible = !isFilterVisible;
            setFilterVisibility(isFilterVisible);

            if (isFilterVisible) {
                filterPanel.scrollIntoView({
                    behavior: "smooth",
                    block: "nearest",
                });
            }
        });
    }

    animateCounters();
    animateProgressBars();
    createCharts();
})();
