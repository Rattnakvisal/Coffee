(() => {
    const attendancePage = document.querySelector("[data-attendance-page]");

    if (!attendancePage) {
        return;
    }

    const feedback = attendancePage.querySelector("[data-attendance-feedback]");
    const checkedTodayCount = attendancePage.querySelector(
        "[data-checked-today-count]",
    );
    const pendingTodayCount = attendancePage.querySelector(
        "[data-pending-today-count]",
    );
    const attendanceRateLabel = attendancePage.querySelector(
        "[data-attendance-rate-label]",
    );
    const attendanceRateBar = attendancePage.querySelector(
        "[data-attendance-rate-bar]",
    );
    const historyList = attendancePage.querySelector(
        "[data-attendance-history-list]",
    );
    const filterOpenButton = attendancePage.querySelector(
        "[data-attendance-filter-open]",
    );
    const filterCloseButtons = attendancePage.querySelectorAll(
        "[data-attendance-filter-close]",
    );
    const filterPanel = attendancePage.querySelector(
        "[data-attendance-filter-panel]",
    );

    const closeFilterPanel = () => {
        if (!filterPanel) {
            return;
        }

        filterPanel.classList.add("hidden");
    };

    const openFilterPanel = () => {
        if (!filterPanel) {
            return;
        }

        filterPanel.classList.remove("hidden");
    };

    if (filterOpenButton) {
        filterOpenButton.addEventListener("click", openFilterPanel);
    }

    filterCloseButtons.forEach((button) => {
        button.addEventListener("click", closeFilterPanel);
    });

    document.addEventListener("keydown", (event) => {
        if (event.key === "Escape") {
            closeFilterPanel();
        }
    });

    const setFeedback = (message, isError = false) => {
        if (!feedback) {
            return;
        }

        if (!message) {
            feedback.classList.add("hidden");
            feedback.textContent = "";
            feedback.classList.remove(
                "border-emerald-200",
                "bg-emerald-50",
                "text-emerald-700",
                "border-rose-200",
                "bg-rose-50",
                "text-rose-700",
            );
            return;
        }

        feedback.textContent = message;
        feedback.classList.remove("hidden");
        feedback.classList.toggle("border-emerald-200", !isError);
        feedback.classList.toggle("bg-emerald-50", !isError);
        feedback.classList.toggle("text-emerald-700", !isError);
        feedback.classList.toggle("border-rose-200", isError);
        feedback.classList.toggle("bg-rose-50", isError);
        feedback.classList.toggle("text-rose-700", isError);
    };

    const updateStats = (stats) => {
        if (!stats) {
            return;
        }

        if (checkedTodayCount) {
            checkedTodayCount.textContent = Number(
                stats.checked_today_count || 0,
            ).toLocaleString();
        }

        if (pendingTodayCount) {
            pendingTodayCount.textContent = Number(
                stats.pending_today_count || 0,
            ).toLocaleString();
        }

        if (attendanceRateLabel) {
            attendanceRateLabel.textContent = `${Number(stats.attendance_rate || 0)}% checked in`;
        }

        if (attendanceRateBar) {
            attendanceRateBar.style.width = `${Math.min(100, Math.max(0, Number(stats.attendance_rate || 0)))}%`;
        }
    };

    const updateCard = (attendance) => {
        if (!attendance) {
            return;
        }

        const card = attendancePage.querySelector(
            `[data-attendance-card][data-cashier-id="${attendance.cashier_id}"]`,
        );

        if (!card) {
            return;
        }

        const avatarBadge = card.querySelector(
            "[data-attendance-avatar-badge]",
        );
        const statusBadge = card.querySelector(
            "[data-attendance-status-badge]",
        );
        const timeLabel = card.querySelector("[data-attendance-time]");
        const dateLabel = card.querySelector("[data-attendance-date]");
        const submitButton = card.querySelector("[data-attendance-submit]");
        const submitLabel = card.querySelector(
            "[data-attendance-submit-label]",
        );

        card.classList.remove(
            "border-amber-100",
            "bg-amber-50/40",
            "bg-gradient-to-br",
            "from-amber-50",
            "to-white",
        );
        card.classList.add(
            "border-emerald-100",
            "bg-emerald-50/50",
        );

        if (avatarBadge) {
            avatarBadge.classList.remove("bg-amber-100", "text-amber-700");
            avatarBadge.classList.add("bg-emerald-100", "text-emerald-700");
        }

        if (statusBadge) {
            statusBadge.textContent = "Checked";
            statusBadge.classList.remove("bg-amber-100", "text-amber-700");
            statusBadge.classList.add("bg-emerald-100", "text-emerald-700");
        }

        if (timeLabel) {
            timeLabel.textContent = attendance.checked_in_at || "--:--:--";
        }

        if (dateLabel) {
            dateLabel.textContent = attendance.attended_on || "--/--/----";
        }

        if (submitButton) {
            submitButton.disabled = true;
            submitButton.classList.remove(
                "bg-[#2f241f]",
                "hover:bg-[#3c2f29]",
            );
            submitButton.classList.add("bg-emerald-600");
        }

        if (submitLabel) {
            submitLabel.textContent = "Attendance Checked, POS Ready";
        }
    };

    const prependHistoryEntry = (attendance) => {
        if (!historyList || !attendance || !attendance.is_today) {
            return;
        }

        const emptyState = historyList.querySelector(
            "[data-attendance-empty-log]",
        );

        if (emptyState) {
            emptyState.remove();
        }

        const entry = document.createElement("article");
        entry.className =
            "flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-[#efe2d8] bg-[#fffdfb] px-4 py-3 transition hover:bg-[#fff7f1]";

        entry.innerHTML = `
                    <div class="min-w-0">
                        <p class="truncate font-semibold text-[#2f241f]">${attendance.cashier_name || "Cashier"}</p>
                        <p class="text-xs text-slate-500">${attendance.attended_on || "-"} - ${attendance.checked_in_at || "--:--:--"}</p>
                    </div>
                    <span class="rounded-full px-2.5 py-1 text-[11px] font-semibold uppercase tracking-[0.08em] bg-emerald-100 text-emerald-700">Today</span>
                `;

        historyList.prepend(entry);
    };

    document.addEventListener(
        "submit",
        async (event) => {
            const form = event.target.closest(".js-attendance-check-form");

            if (!form || !attendancePage.contains(form)) {
                return;
            }

            event.preventDefault();
            event.stopImmediatePropagation();

            if (form.dataset.submitting === "1") {
                return;
            }

            const submitButton = form.querySelector("[data-attendance-submit]");
            const submitLabel = form.querySelector(
                "[data-attendance-submit-label]",
            );
            const originalLabel = submitLabel ? submitLabel.textContent : "";

            form.dataset.submitting = "1";
            setFeedback("");

            if (submitButton) {
                submitButton.disabled = true;
            }

            if (submitLabel) {
                submitLabel.textContent = "Checking attendance...";
            }

            try {
                const response = await fetch(form.action, {
                    method: "POST",
                    headers: {
                        Accept: "application/json",
                        "X-Requested-With": "XMLHttpRequest",
                    },
                    body: new FormData(form),
                });

                const payload = await response.json();

                if (!response.ok || !payload.ok) {
                    throw new Error(
                        payload.message || "Unable to check attendance.",
                    );
                }

                setFeedback(payload.message || "", false);
                updateStats(payload.stats || null);
                updateCard(payload.attendance || null);

                if (payload.was_recently_created) {
                    prependHistoryEntry(payload.attendance || null);
                }

                if (payload.redirect_url) {
                    window.location.href = payload.redirect_url;
                }
            } catch (error) {
                setFeedback(
                    error.message || "Unable to check attendance.",
                    true,
                );

                if (submitButton) {
                    submitButton.disabled = false;
                }

                if (submitLabel) {
                    submitLabel.textContent =
                        originalLabel || "Check Attendance and Open POS";
                }
            } finally {
                form.dataset.submitting = "0";
            }
        },
        true,
    );
})();
