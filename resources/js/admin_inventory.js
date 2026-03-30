(function () {
    const toggleButton = document.querySelector("[data-inventory-outgoing-toggle]");
    const filterToggleButton = document.querySelector(
        "[data-inventory-filter-toggle]",
    );
    const filterPanel = document.querySelector("[data-inventory-filter-panel]");
    const filterToggleLabel = document.querySelector(
        "[data-inventory-filter-toggle-label]",
    );
    const modalTemplate = document.getElementById("inventory-outgoing-template");
    const formPanel = document.querySelector("[data-inventory-outgoing-panel]");
    const formAlert = document.querySelector("[data-inventory-form-alert]");
    const tableSection = document.querySelector("[data-inventory-table-section]");

    if (!toggleButton && !filterToggleButton) {
        return;
    }

    let alertTimer = null;

    const showFormAlert = function (message) {
        if (!formAlert) {
            return;
        }

        formAlert.textContent = message;
        formAlert.classList.remove("hidden");
        formAlert.classList.add("is-visible");

        if (alertTimer) {
            window.clearTimeout(alertTimer);
        }

        alertTimer = window.setTimeout(function () {
            formAlert.classList.remove("is-visible");
            formAlert.classList.add("hidden");
        }, 2200);
    };

    const highlightTable = function () {
        if (!tableSection) {
            return;
        }

        tableSection.classList.remove("inventory-detail-highlight");
        window.requestAnimationFrame(function () {
            tableSection.classList.add("inventory-detail-highlight");
        });
    };

    if (tableSection?.getAttribute("data-inventory-has-save-alert") === "true") {
        highlightTable();
    }

    const openOutgoingModal = function () {
        if (typeof window.Swal === "undefined" || !modalTemplate) {
            return false;
        }

        window.Swal.fire({
            title: "Add outgoing",
            html: modalTemplate.innerHTML,
            showConfirmButton: false,
            showCloseButton: true,
            width: 680,
            didOpen: function () {
                const form = document.getElementById("swal-inventory-outgoing-form");

                if (!form) {
                    return;
                }

                const amountInput = form.querySelector('input[name="amount"]');
                if (amountInput) {
                    amountInput.focus();
                }

                form.addEventListener("submit", function (event) {
                    const rawAmount =
                        form.querySelector('input[name="amount"]')?.value ?? "";
                    const amount = Number(rawAmount);

                    if (!Number.isFinite(amount) || amount <= 0) {
                        event.preventDefault();
                        window.Swal.fire({
                            icon: "warning",
                            title: "Amount required",
                            text: "Please enter amount greater than 0.",
                            confirmButtonColor: "#f97316",
                        });
                        return;
                    }

                    if (!form.reportValidity()) {
                        event.preventDefault();
                    }
                });
            },
        });

        return true;
    };

    const syncFilterToggleState = function (isVisible) {
        if (filterPanel) {
            filterPanel.classList.toggle("hidden", !isVisible);
        }

        if (filterToggleButton) {
            filterToggleButton.setAttribute(
                "aria-expanded",
                isVisible ? "true" : "false",
            );
        }

        if (filterToggleLabel) {
            filterToggleLabel.textContent = isVisible ? "Hide Filter" : "Filter";
        }
    };

    if (filterToggleButton && filterPanel) {
        syncFilterToggleState(!filterPanel.classList.contains("hidden"));

        filterToggleButton.addEventListener("click", function () {
            const nextVisible = filterPanel.classList.contains("hidden");
            syncFilterToggleState(nextVisible);

            if (nextVisible) {
                filterPanel.scrollIntoView({
                    behavior: "smooth",
                    block: "nearest",
                });
            }
        });
    }

    if (toggleButton) {
        toggleButton.addEventListener("click", function () {
            const hasModal = openOutgoingModal();
            if (hasModal) {
                highlightTable();
                return;
            }

            if (!formPanel) {
                return;
            }

            const isVisible = !formPanel.classList.contains("hidden");
            const nextVisible = !isVisible;
            formPanel.classList.toggle("hidden", !nextVisible);
            toggleButton.setAttribute("aria-expanded", nextVisible ? "true" : "false");

            if (nextVisible) {
                showFormAlert(
                    "Outgoing form opened. Fill details and save to show it in the table.",
                );
                formPanel.scrollIntoView({ behavior: "smooth", block: "nearest" });
                highlightTable();
                return;
            }

            showFormAlert("Outgoing form closed.");
        });
    }
})();
