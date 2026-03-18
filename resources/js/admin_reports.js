(function () {
    const payloadScript = document.getElementById("admin-report-payload");
    const counterEls = document.querySelectorAll("[data-counter-value]");
    const progressBars = document.querySelectorAll(".dashboard-progress-bar");
    const prefersReducedMotion = window.matchMedia(
        "(prefers-reduced-motion: reduce)",
    ).matches;

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

    const createCharts = function () {
        if (typeof window.Chart === "undefined") {
            return;
        }

        window.Chart.defaults.font.family =
            "'Instrument Sans', 'Segoe UI', sans-serif";
        window.Chart.defaults.color = "#6b7280";

        const trendCtx = document.getElementById("adminReportsTrendChart");
        const paymentCtx = document.getElementById("adminReportsPaymentChart");
        const topItemsCtx = document.getElementById("adminReportsTopItemsChart");
        const statusCtx = document.getElementById("adminReportsStatusChart");
        const categoryCtx = document.getElementById("adminReportsCategoryChart");
        const comparisonCtx = document.getElementById(
            "adminReportsComparisonChart",
        );

        if (trendCtx) {
            const gradient = trendCtx
                .getContext("2d")
                .createLinearGradient(0, 0, 0, 260);
            gradient.addColorStop(0, "rgba(244, 160, 107, 0.35)");
            gradient.addColorStop(1, "rgba(244, 160, 107, 0.02)");

            new window.Chart(trendCtx, {
                type: "line",
                data: {
                    labels: toStringArray(trendData.labels),
                    datasets: [
                        {
                            label: "Revenue ($)",
                            data: toNumberArray(trendData.revenue),
                            borderColor: "#d97f46",
                            backgroundColor: gradient,
                            fill: true,
                            borderWidth: 2.5,
                            tension: 0.35,
                            pointRadius: 3,
                            yAxisID: "y",
                        },
                        {
                            label: "Orders",
                            data: toNumberArray(trendData.orders),
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
                            label: "Revenue",
                            data: toNumberArray(comparisonData.revenue),
                            backgroundColor: "#f4a06b",
                            borderRadius: 12,
                            borderSkipped: false,
                            yAxisID: "y",
                        },
                        {
                            label: "Orders",
                            data: toNumberArray(comparisonData.orders),
                            backgroundColor: "#2f241f",
                            borderRadius: 12,
                            borderSkipped: false,
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
                                ? ["#f4a06b", "#d97f46", "#8f5f3e", "#2f241f"]
                                : ["#f3e2d6"],
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
                                ? ["#2f241f", "#f4a06b", "#d97f46", "#8f5f3e"]
                                : ["#f3e2d6"],
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

        if (categoryCtx) {
            new window.Chart(categoryCtx, {
                type: "bar",
                data: {
                    labels: toStringArray(categoriesData.labels),
                    datasets: [
                        {
                            label: "Revenue",
                            data: toNumberArray(categoriesData.revenue),
                            backgroundColor: "#f4a06b",
                            borderRadius: 10,
                            borderSkipped: false,
                            yAxisID: "y",
                        },
                        {
                            label: "Items Sold",
                            data: toNumberArray(categoriesData.qty),
                            backgroundColor: "#2f241f",
                            borderRadius: 10,
                            borderSkipped: false,
                            yAxisID: "y1",
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
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                        },
                        y: {
                            grid: {
                                display: false,
                            },
                        },
                        y1: {
                            display: false,
                        },
                    },
                },
            });
        }

        if (topItemsCtx) {
            new window.Chart(topItemsCtx, {
                type: "bar",
                data: {
                    labels: toStringArray(topItemsData.labels),
                    datasets: [
                        {
                            label: "Qty Sold",
                            data: toNumberArray(topItemsData.qty),
                            backgroundColor: "#2f241f",
                            borderRadius: 10,
                            borderSkipped: false,
                            yAxisID: "y",
                        },
                        {
                            label: "Revenue",
                            data: toNumberArray(topItemsData.revenue),
                            backgroundColor: "#f4a06b",
                            borderRadius: 10,
                            borderSkipped: false,
                            yAxisID: "y1",
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
                        y1: {
                            display: false,
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
