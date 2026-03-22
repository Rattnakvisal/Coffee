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
    const notificationRoot = document.querySelector("[data-admin-notification]");
    const notificationButton = document.querySelector(
        "[data-admin-notification-button]",
    );
    const notificationPanel = document.querySelector(
        "[data-admin-notification-panel]",
    );
    const notificationFetchUrl = notificationRoot
        ? notificationRoot.getAttribute("data-fetch-url") || ""
        : "";
    const notificationMarkReadUrl = notificationRoot
        ? notificationRoot.getAttribute("data-mark-read-url") || ""
        : "";
    const csrfToken =
        document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute("content") || "";
    const notificationDot = document.querySelector(
        "[data-admin-notification-dot]",
    );
    const notificationCount = document.querySelector(
        "[data-admin-notification-count]",
    );
    const notificationHeaderCount = document.querySelector(
        "[data-admin-notification-header-count]",
    );
    const notificationList = document.querySelector(
        "[data-admin-notification-list]",
    );
    const notificationEmpty = document.querySelector(
        "[data-admin-notification-empty]",
    );
    const profileRoot = document.querySelector("[data-admin-profile]");
    const profileButton = document.querySelector("[data-admin-profile-button]");
    const profilePanel = document.querySelector("[data-admin-profile-panel]");
    let notificationSyncInFlight = false;
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
            const hasRoleData =
                chartPayload.roleLabels.length > 0 &&
                chartPayload.roleCounts.length > 0;

            new window.Chart(roleCtx, {
                type: "doughnut",
                data: {
                    labels: hasRoleData ? chartPayload.roleLabels : ["No Data"],
                    datasets: [
                        {
                            data: hasRoleData ? chartPayload.roleCounts : [1],
                            backgroundColor: [
                                "#5f9925",
                                "#4a86d9",
                                "#f0b73e",
                                "#de4b35",
                            ],
                            borderWidth: 0,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: "70%",
                    plugins: {
                        legend: {
                            position: "bottom",
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

    const closeNotificationPanel = function () {
        if (!notificationPanel || !notificationButton) {
            return;
        }

        notificationPanel.classList.add("hidden");
        notificationButton.setAttribute("aria-expanded", "false");
    };

    const openNotificationPanel = function () {
        if (!notificationPanel || !notificationButton) {
            return;
        }

        notificationPanel.classList.remove("hidden");
        notificationButton.setAttribute("aria-expanded", "true");
    };

    const closeProfilePanel = function () {
        if (!profilePanel || !profileButton) {
            return;
        }

        profilePanel.classList.add("hidden");
        profileButton.setAttribute("aria-expanded", "false");
    };

    const openProfilePanel = function () {
        if (!profilePanel || !profileButton) {
            return;
        }

        profilePanel.classList.remove("hidden");
        profileButton.setAttribute("aria-expanded", "true");
    };

    const escapeHtml = function (value) {
        return String(value ?? "")
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#39;");
    };

    const updateNotificationBadge = function (count) {
        const normalizedCount = Number.isFinite(Number(count))
            ? Math.max(0, Number(count))
            : 0;
        const formattedCount = normalizedCount.toLocaleString();

        if (notificationCount) {
            notificationCount.textContent = formattedCount;
        }

        if (notificationHeaderCount) {
            notificationHeaderCount.textContent = formattedCount;
        }

        if (notificationDot) {
            notificationDot.classList.toggle("hidden", normalizedCount <= 0);
        }
    };

    const renderNotificationItems = function (notifications) {
        if (!notificationList || !notificationEmpty) {
            return;
        }

        const rows = Array.isArray(notifications) ? notifications : [];
        const itemsMarkup = rows
            .map(function (notification) {
                const title = escapeHtml(notification.title || "Notification");
                const time = escapeHtml(notification.time || "");
                const message = escapeHtml(notification.message || "");

                return (
                    '<div class="mb-2 rounded-xl border border-[#f2e6dd] bg-[#fffaf6] p-3 last:mb-0">' +
                    '<div class="flex items-start justify-between gap-2">' +
                    '<p class="text-xs font-semibold uppercase tracking-[0.08em] text-[#b16231]">' +
                    title +
                    "</p>" +
                    '<span class="text-[11px] text-slate-400">' +
                    time +
                    "</span>" +
                    "</div>" +
                    '<p class="mt-1 text-sm text-[#4f3b31]">' +
                    message +
                    "</p>" +
                    "</div>"
                );
            })
            .join("");

        Array.from(
            notificationList.querySelectorAll("[data-admin-notification-item]"),
        ).forEach(function (node) {
            node.remove();
        });

        if (itemsMarkup !== "") {
            const wrapper = document.createElement("div");
            wrapper.innerHTML = itemsMarkup;
            Array.from(wrapper.children).forEach(function (child) {
                child.setAttribute("data-admin-notification-item", "1");
                notificationList.insertBefore(child, notificationEmpty);
            });
        }

        notificationEmpty.classList.toggle("hidden", rows.length > 0);
    };

    const syncAdminNotifications = async function () {
        if (!notificationFetchUrl || notificationSyncInFlight) {
            return;
        }

        notificationSyncInFlight = true;

        try {
            const response = await fetch(notificationFetchUrl, {
                headers: {
                    Accept: "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                },
            });

            if (!response.ok) {
                return;
            }

            const payload = await response.json();

            if (!payload || payload.ok !== true) {
                return;
            }

            const rows = Array.isArray(payload.notifications)
                ? payload.notifications
                : [];
            const count = Number(payload.count);
            updateNotificationBadge(
                Number.isFinite(count) ? count : rows.length,
            );
            renderNotificationItems(rows);
        } catch (error) {
            // Silent fail; keep existing server-rendered notifications.
        } finally {
            notificationSyncInFlight = false;
        }
    };

    const markAdminNotificationsAsRead = async function () {
        if (!notificationMarkReadUrl) {
            return;
        }

        try {
            await fetch(notificationMarkReadUrl, {
                method: "POST",
                headers: {
                    Accept: "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                    "X-CSRF-TOKEN": csrfToken,
                },
            });
        } catch (error) {
            // Silent fail; badge is still hidden on the UI side.
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

    if (notificationButton && notificationPanel) {
        notificationButton.addEventListener("click", function (event) {
            event.preventDefault();
            event.stopPropagation();

            const isClosed = notificationPanel.classList.contains("hidden");

            if (isClosed) {
                closeProfilePanel();
                openNotificationPanel();
                updateNotificationBadge(0);
                markAdminNotificationsAsRead();
                return;
            }

            closeNotificationPanel();
        });
    }

    if (profileButton && profilePanel) {
        profileButton.addEventListener("click", function (event) {
            event.preventDefault();
            event.stopPropagation();

            const isClosed = profilePanel.classList.contains("hidden");

            if (isClosed) {
                closeNotificationPanel();
                openProfilePanel();
                return;
            }

            closeProfilePanel();
        });
    }

    document.addEventListener("click", function (event) {
        if (!searchForm || !searchDropdown) return;

        const target = event.target;
        if (target instanceof Node && searchForm.contains(target)) {
            return;
        }

        closeSearchDropdown();
    });

    document.addEventListener("click", function (event) {
        if (!notificationRoot || !notificationPanel) {
            return;
        }

        const target = event.target;
        if (target instanceof Node && notificationRoot.contains(target)) {
            return;
        }

        closeNotificationPanel();
    });

    document.addEventListener("click", function (event) {
        if (!profileRoot || !profilePanel) {
            return;
        }

        const target = event.target;
        if (target instanceof Node && profileRoot.contains(target)) {
            return;
        }

        closeProfilePanel();
    });

    document.addEventListener("keydown", function (event) {
        if (event.key === "Escape") {
            closeSearchDropdown();
            closeNotificationPanel();
            closeProfilePanel();
        }
    });

    animateCounters();
    animateProgressBars();
    createCharts();

    if (notificationFetchUrl) {
        syncAdminNotifications();
        window.setInterval(syncAdminNotifications, 10000);
    }
})();
