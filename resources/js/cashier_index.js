(function () {
    const root = document.documentElement;
    const overlay = document.querySelector("[data-cashier-overlay]");
    const menu = document.querySelector("[data-cashier-menu]");
    let cart = document.querySelector("[data-cashier-cart]");
    const openMenuButton = document.querySelector("[data-cashier-open-menu]");
    const openCartButton = document.querySelector("[data-cashier-open-cart]");
    const productCartForms = document.querySelectorAll(".js-product-cart-form");
    const searchForm = document.querySelector("[data-cashier-search-form]");
    const searchSuggestionsRaw = searchForm
        ? searchForm.getAttribute("data-cashier-search-suggestions") || "[]"
        : "[]";
    let searchSuggestions = [];
    try {
        searchSuggestions = JSON.parse(searchSuggestionsRaw);
    } catch (error) {
        searchSuggestions = [];
    }
    const searchInput = searchForm
        ? searchForm.querySelector("[data-cashier-search-input]")
        : null;
    const searchDropdown = searchForm
        ? searchForm.querySelector("[data-cashier-search-dropdown]")
        : null;
    const searchResults = searchForm
        ? searchForm.querySelector("[data-cashier-search-results]")
        : null;
    const searchEmpty = searchForm
        ? searchForm.querySelector("[data-cashier-search-empty]")
        : null;
    const productCards = document.querySelectorAll(
        "[data-cashier-product-card]",
    );
    const searchNoResults = document.querySelector(
        "[data-cashier-search-no-results]",
    );
    let applyProductSearchFilter = function () {};
    const historyFilterOpenButton = document.querySelector(
        "[data-history-filter-open]",
    );
    const historyFilterCloseButtons = document.querySelectorAll(
        "[data-history-filter-close]",
    );
    const historyFilterPanel = document.querySelector(
        "[data-history-filter-panel]",
    );

    const loadingOverlay = document.getElementById("coffee-add-loading");
    const loadingText = loadingOverlay
        ? loadingOverlay.querySelector("p")
        : null;
    const desktopMediaQuery = window.matchMedia("(min-width: 1024px)");

    const showLoading = function (text) {
        if (!loadingOverlay) return;
        if (loadingText && text) {
            loadingText.textContent = text;
        }

        loadingOverlay.classList.remove("hidden");
        loadingOverlay.classList.add("flex");
    };

    const hideLoading = function () {
        if (!loadingOverlay) return;
        loadingOverlay.classList.add("hidden");
        loadingOverlay.classList.remove("flex");
    };

    const closeHistoryFilter = function () {
        if (!historyFilterPanel) return;
        historyFilterPanel.classList.add("hidden");
    };

    const openHistoryFilter = function () {
        if (!historyFilterPanel) return;
        historyFilterPanel.classList.remove("hidden");
    };

    if (historyFilterOpenButton) {
        historyFilterOpenButton.addEventListener("click", openHistoryFilter);
    }

    historyFilterCloseButtons.forEach((button) => {
        button.addEventListener("click", closeHistoryFilter);
    });

    document.addEventListener("keydown", (event) => {
        if (event.key === "Escape") {
            closeHistoryFilter();
        }
    });

    const setPlaceOrderFeedback = function (form, message, isError) {
        if (!form) return;

        const feedback = form.querySelector("[data-place-order-feedback]");
        if (!feedback) return;

        if (!message) {
            feedback.classList.add("hidden");
            feedback.textContent = "";
            feedback.classList.remove("text-emerald-700", "text-red-600");
            return;
        }

        feedback.textContent = message;
        feedback.classList.remove("hidden");
        feedback.classList.toggle("text-emerald-700", !isError);
        feedback.classList.toggle("text-red-600", !!isError);
    };

    const initPlaceOrderForm = function (scope) {
        const rootElement = scope && scope.querySelector ? scope : document;
        const placeOrderForm = rootElement.querySelector(
            ".js-place-order-form",
        );
        if (!placeOrderForm) return;

        const paymentMethodField = placeOrderForm.querySelector(
            "[data-payment-method]",
        );
        const amountReceivedField = placeOrderForm.querySelector(
            "[data-amount-received]",
        );
        const paymentHint = placeOrderForm.querySelector("[data-payment-hint]");
        const orderTotal = Math.max(
            0,
            Number(placeOrderForm.dataset.orderTotal) || 0,
        );

        if (!paymentMethodField || !amountReceivedField) return;

        const syncPaymentInputs = function () {
            const paymentMethod = paymentMethodField.value;
            const totalString = orderTotal.toFixed(2);
            const isCashPayment = paymentMethod === "cash";

            amountReceivedField.readOnly = !isCashPayment;
            amountReceivedField.min = totalString;

            if (!isCashPayment) {
                amountReceivedField.value = totalString;
            } else if ((Number(amountReceivedField.value) || 0) < orderTotal) {
                amountReceivedField.value = totalString;
            }

            if (paymentHint) {
                paymentHint.textContent = isCashPayment
                    ? "For cash payment, received amount should be >= total."
                    : "For card or QR payment, amount is set to exact total.";
            }
        };

        if (placeOrderForm.dataset.paymentBound !== "1") {
            paymentMethodField.addEventListener("change", syncPaymentInputs);
            placeOrderForm.dataset.paymentBound = "1";
        }

        syncPaymentInputs();
    };

    const replaceCartHtml = function (html) {
        if (!cart || !html) return;

        const wasOpen = !cart.classList.contains("translate-x-full");
        const wrapper = document.createElement("div");
        wrapper.innerHTML = html.trim();

        const nextCart = wrapper.firstElementChild;
        if (!nextCart) return;

        if (wasOpen || desktopMediaQuery.matches) {
            nextCart.classList.remove("translate-x-full");
        }

        cart.replaceWith(nextCart);
        cart = nextCart;
        initPlaceOrderForm(nextCart);
    };

    const submitCartFormAjax = async function (form, submitButton) {
        if (!form || form.dataset.submitting === "1") return;

        form.dataset.submitting = "1";
        if (submitButton) {
            submitButton.disabled = true;
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

            if (!response.ok) {
                const failure = new Error(
                    payload && payload.message
                        ? payload.message
                        : "Request failed",
                );
                failure.payload = payload;
                failure.status = response.status;
                throw failure;
            }

            if (payload && payload.ok && payload.cart_html) {
                replaceCartHtml(payload.cart_html);
            }

            return payload;
        } catch (error) {
            if (error && error.status === 422 && error.payload) {
                return error.payload;
            }

            form.submit();
            return null;
        } finally {
            form.dataset.submitting = "0";
            if (submitButton) {
                submitButton.disabled = false;
            }
        }
    };

    initPlaceOrderForm(document);

    if (
        searchForm &&
        searchInput &&
        searchDropdown &&
        searchResults &&
        searchEmpty &&
        Array.isArray(searchSuggestions)
    ) {
        const suggestionPool = searchSuggestions
            .map(function (value) {
                return String(value || "").trim();
            })
            .filter(function (value) {
                return value !== "";
            });

        let filteredSuggestions = [];
        let activeSuggestionIndex = -1;

        const closeSearchDropdown = function () {
            searchDropdown.classList.add("hidden");
            searchResults.innerHTML = "";
            searchEmpty.classList.add("hidden");
            filteredSuggestions = [];
            activeSuggestionIndex = -1;
        };

        const openSearchDropdown = function () {
            searchDropdown.classList.remove("hidden");
        };

        const setActiveSuggestion = function (nextIndex) {
            const optionButtons = searchResults.querySelectorAll(
                "[data-suggestion-index]",
            );
            optionButtons.forEach(function (button, index) {
                const isActive = index === nextIndex;
                button.classList.toggle("is-active", isActive);
                button.setAttribute(
                    "aria-selected",
                    isActive ? "true" : "false",
                );

                if (isActive) {
                    button.scrollIntoView({
                        block: "nearest",
                    });
                }
            });
        };

        const selectSuggestion = function (value) {
            searchInput.value = value;
            closeSearchDropdown();
            applyProductSearchFilter();
            searchInput.focus();
        };

        const renderSearchSuggestions = function (queryValue) {
            const query = String(queryValue || "")
                .trim()
                .toLowerCase();
            const maxItems = 8;

            if (query === "") {
                closeSearchDropdown();
                return;
            }

            filteredSuggestions = suggestionPool
                .filter(function (suggestion) {
                    return suggestion.toLowerCase().includes(query);
                })
                .slice(0, maxItems);

            searchResults.innerHTML = "";
            activeSuggestionIndex = -1;

            if (!filteredSuggestions.length) {
                searchEmpty.classList.remove("hidden");
                openSearchDropdown();
                return;
            }

            searchEmpty.classList.add("hidden");
            const fragment = document.createDocumentFragment();

            filteredSuggestions.forEach(function (suggestion, index) {
                const item = document.createElement("li");
                const button = document.createElement("button");
                button.type = "button";
                button.className = "coffee-search-option";
                button.textContent = suggestion;
                button.dataset.suggestionIndex = String(index);
                button.dataset.suggestionValue = suggestion;
                button.setAttribute("role", "option");
                button.setAttribute("aria-selected", "false");
                item.appendChild(button);
                fragment.appendChild(item);
            });

            searchResults.appendChild(fragment);
            openSearchDropdown();
        };

        searchInput.addEventListener("focus", function () {
            renderSearchSuggestions(searchInput.value);
        });

        searchInput.addEventListener("input", function () {
            renderSearchSuggestions(searchInput.value);
        });

        searchInput.addEventListener("keydown", function (event) {
            if (event.key === "Escape") {
                closeSearchDropdown();
                return;
            }

            if (!filteredSuggestions.length) {
                return;
            }

            if (event.key === "ArrowDown") {
                event.preventDefault();
                activeSuggestionIndex =
                    (activeSuggestionIndex + 1) % filteredSuggestions.length;
                setActiveSuggestion(activeSuggestionIndex);
                return;
            }

            if (event.key === "ArrowUp") {
                event.preventDefault();
                activeSuggestionIndex =
                    activeSuggestionIndex <= 0
                        ? filteredSuggestions.length - 1
                        : activeSuggestionIndex - 1;
                setActiveSuggestion(activeSuggestionIndex);
                return;
            }

            if (event.key === "Enter" && activeSuggestionIndex >= 0) {
                event.preventDefault();
                selectSuggestion(filteredSuggestions[activeSuggestionIndex]);
            }
        });

        searchResults.addEventListener("mousedown", function (event) {
            const targetButton = event.target.closest(
                "[data-suggestion-value]",
            );
            if (!targetButton) return;
            event.preventDefault();
        });

        searchResults.addEventListener("click", function (event) {
            const targetButton = event.target.closest(
                "[data-suggestion-value]",
            );
            if (!targetButton) return;
            selectSuggestion(targetButton.dataset.suggestionValue || "");
        });

        searchForm.addEventListener("submit", function () {
            closeSearchDropdown();
        });

        document.addEventListener("click", function (event) {
            if (searchForm.contains(event.target)) {
                return;
            }

            closeSearchDropdown();
        });
    }

    if (searchInput && productCards.length) {
        const filterProductCards = function () {
            const query = String(searchInput.value || "")
                .trim()
                .toLowerCase();
            let visibleCount = 0;

            productCards.forEach(function (card) {
                const searchText = String(card.dataset.searchText || "");
                const isVisible = query === "" || searchText.includes(query);

                card.classList.toggle("hidden", !isVisible);
                if (isVisible) {
                    visibleCount += 1;
                }
            });

            if (searchNoResults) {
                searchNoResults.classList.toggle(
                    "hidden",
                    query === "" || visibleCount > 0,
                );
            }
        };

        applyProductSearchFilter = filterProductCards;
        searchInput.addEventListener("input", filterProductCards);
        filterProductCards();
    }

    productCartForms.forEach(function (form) {
        const qtyInput = form.querySelector(".js-product-qty-input");
        const qtyLabel = form.querySelector(".js-product-qty-label");
        const decreaseButton = form.querySelector(".js-product-qty-decrease");
        const increaseButton = form.querySelector(".js-product-qty-increase");
        const priceLabel = form
            .closest(".rounded-3xl")
            ?.querySelector(".js-size-price-label");
        const basePriceLabel = form
            .closest(".rounded-3xl")
            ?.querySelector(".js-size-base-price-label");
        const sizeInput = form.querySelector(".js-size-input");
        const sizeLabel = form.querySelector(".js-size-label");
        const sizeButtons = form.querySelectorAll(".js-size-option");
        const sugarRange = form.querySelector(".js-sugar-range");
        const sugarLabel = form.querySelector(".js-sugar-label");
        const hasActiveSizesAttr = form.hasAttribute("data-active-sizes");
        const activeSizesRaw = form.dataset.activeSizes || "[]";
        let activeSizes = [];

        try {
            const parsedActiveSizes = JSON.parse(activeSizesRaw);
            if (Array.isArray(parsedActiveSizes)) {
                const sanitizedActiveSizes = parsedActiveSizes
                    .map(function (value) {
                        return String(value || "").toLowerCase();
                    })
                    .filter(function (value) {
                        return ["small", "medium", "large"].includes(value);
                    });

                if (sanitizedActiveSizes.length) {
                    activeSizes = sanitizedActiveSizes;
                }
            }
        } catch (error) {
            activeSizes = [];
        }

        if (!hasActiveSizesAttr && !activeSizes.length) {
            activeSizes = ["small", "medium", "large"];
        }

        const baseSizePrices = {
            small: Number(form.dataset.basePriceSmall || 0),
            medium: Number(form.dataset.basePriceMedium || 0),
            large: Number(form.dataset.basePriceLarge || 0),
        };
        const sizePrices = {
            small: Number(form.dataset.priceSmall || 0),
            medium: Number(form.dataset.priceMedium || 0),
            large: Number(form.dataset.priceLarge || 0),
        };

        if (!qtyInput || !qtyLabel || !decreaseButton || !increaseButton)
            return;

        const formatPrice = function (value) {
            return (
                "$" +
                Number(value || 0).toLocaleString(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2,
                })
            );
        };

        const syncQty = function (value) {
            const nextQty = Math.min(99, Math.max(1, Number(value) || 1));
            qtyInput.value = String(nextQty);
            qtyLabel.textContent = String(nextQty);
        };

        const syncSize = function (value) {
            if (!sizeInput || !sizeLabel || !sizeButtons.length) return;
            if (!activeSizes.length) {
                sizeInput.value = "";
                sizeLabel.textContent = "Unavailable";

                sizeButtons.forEach(function (button) {
                    button.classList.remove("is-active");
                    button.classList.add("is-disabled");
                    button.disabled = true;
                    button.setAttribute("aria-disabled", "true");
                    button.setAttribute("aria-pressed", "false");
                });

                return;
            }

            const requestedSize = String(value || "").toLowerCase();
            const normalized = activeSizes.includes(requestedSize)
                ? requestedSize
                : activeSizes[0];

            sizeInput.value = normalized;
            sizeLabel.textContent =
                normalized.charAt(0).toUpperCase() + normalized.slice(1);

            sizeButtons.forEach(function (button) {
                const buttonSize = String(button.dataset.size || "").toLowerCase();
                const isEnabled = activeSizes.includes(buttonSize);
                const isActive = isEnabled && buttonSize === normalized;

                button.classList.toggle("is-disabled", !isEnabled);
                button.disabled = !isEnabled;
                button.setAttribute(
                    "aria-disabled",
                    isEnabled ? "false" : "true",
                );
                button.classList.toggle("is-active", isActive);
                button.setAttribute(
                    "aria-pressed",
                    isActive ? "true" : "false",
                );
            });

            if (priceLabel) {
                priceLabel.textContent = formatPrice(sizePrices[normalized]);
            }

            if (basePriceLabel) {
                const basePrice = baseSizePrices[normalized];
                const finalPrice = sizePrices[normalized];
                const hasDiscount = basePrice > finalPrice;

                basePriceLabel.textContent = formatPrice(basePrice);
                basePriceLabel.classList.toggle("hidden", !hasDiscount);
            }
        };

        const syncSugar = function (value) {
            if (!sugarRange || !sugarLabel) return;

            const normalized = Math.min(100, Math.max(0, Number(value) || 0));
            sugarRange.value = String(normalized);
            sugarLabel.textContent = String(normalized);
            sugarRange.style.background =
                "linear-gradient(90deg, #f4a06b 0%, #f4a06b " +
                normalized +
                "%, #f6e2d4 " +
                normalized +
                "%, #f6e2d4 100%)";
        };

        decreaseButton.addEventListener("click", function () {
            syncQty((Number(qtyInput.value) || 1) - 1);
        });

        increaseButton.addEventListener("click", function () {
            syncQty((Number(qtyInput.value) || 1) + 1);
        });

        sizeButtons.forEach(function (button) {
            button.addEventListener("click", function () {
                if (button.disabled) return;
                syncSize(button.dataset.size);
            });
        });

        if (sugarRange) {
            sugarRange.addEventListener("input", function () {
                syncSugar(sugarRange.value);
            });
        }

        if (sizeInput) {
            syncSize(sizeInput.value);
        }

        if (sugarRange) {
            syncSugar(sugarRange.value);
        }

        form.addEventListener("submit", function (event) {
            event.preventDefault();

            const addButton = form.querySelector(".js-add-to-cart-btn");
            const originalText = addButton ? addButton.textContent : null;

            if (addButton) {
                addButton.textContent = "Adding...";
            }

            submitCartFormAjax(form, addButton).finally(function () {
                if (addButton && originalText) {
                    addButton.textContent = originalText;
                }
            });
        });
    });

    if (!overlay || !menu || !cart) return;

    const closeAll = function () {
        menu.classList.add("-translate-x-full");
        cart.classList.add("translate-x-full");
        overlay.classList.add("hidden");
        root.classList.remove("overflow-hidden");
    };

    const openMenu = function () {
        menu.classList.remove("-translate-x-full");
        cart.classList.add("translate-x-full");
        overlay.classList.remove("hidden");
        root.classList.add("overflow-hidden");
    };

    const openCart = function () {
        cart.classList.remove("translate-x-full");
        menu.classList.add("-translate-x-full");
        overlay.classList.remove("hidden");
        root.classList.add("overflow-hidden");
    };

    if (openMenuButton) {
        openMenuButton.addEventListener("click", openMenu);
    }

    if (openCartButton) {
        openCartButton.addEventListener("click", openCart);
    }

    document.addEventListener("submit", function (event) {
        const targetForm = event.target.closest(".js-cart-item-form");
        if (targetForm) {
            event.preventDefault();
            const submitButton = targetForm.querySelector(
                'button[type="submit"]',
            );
            submitCartFormAjax(targetForm, submitButton);
            return;
        }

        const placeOrderForm = event.target.closest(".js-place-order-form");
        if (!placeOrderForm) return;
        event.preventDefault();

        const placeOrderButton =
            placeOrderForm.querySelector("[data-place-order]");
        const originalLabel = placeOrderButton
            ? placeOrderButton.textContent
            : "";

        setPlaceOrderFeedback(placeOrderForm, "", false);

        if (placeOrderButton) {
            placeOrderButton.textContent = "Placing order...";
        }

        showLoading("Processing payment...");

        submitCartFormAjax(placeOrderForm, placeOrderButton)
            .then(function (payload) {
                if (!payload || !payload.ok) {
                    hideLoading();
                    setPlaceOrderFeedback(
                        placeOrderForm,
                        payload && payload.message
                            ? payload.message
                            : "Unable to place order.",
                        true,
                    );
                    return;
                }

                const orderNumber = payload.order_number
                    ? String(payload.order_number)
                    : "";
                const changeAmount = Number(payload.change_amount || 0);
                const successMessage =
                    orderNumber !== ""
                        ? "Order " + orderNumber + " placed successfully."
                        : "Order placed successfully.";
                const thankYouMessage =
                    orderNumber !== ""
                        ? "Thank you! Your order " +
                          orderNumber +
                          " has been placed successfully."
                        : "Thank you! Your order has been placed successfully.";
                const loadingMessage =
                    changeAmount > 0
                        ? successMessage +
                          " Change: $" +
                          changeAmount.toFixed(2)
                        : successMessage;

                showLoading(loadingMessage);

                window.setTimeout(function () {
                    hideLoading();
                    setPlaceOrderFeedback(
                        placeOrderForm,
                        thankYouMessage,
                        false,
                    );
                    window.alert(thankYouMessage);
                }, 700);
            })
            .finally(function () {
                if (placeOrderButton) {
                    placeOrderButton.textContent =
                        originalLabel || "Place an order";
                }
            });
    });

    document.addEventListener("click", function (event) {
        const closeTrigger = event.target.closest("[data-cashier-close]");
        if (closeTrigger) {
            closeAll();
        }
    });

    overlay.addEventListener("click", closeAll);

    menu.querySelectorAll("a").forEach(function (link) {
        link.addEventListener("click", closeAll);
    });

    document.addEventListener("keydown", function (event) {
        if (event.key === "Escape") {
            closeAll();
        }
    });

    const handleDesktopChange = function (event) {
        if (event.matches) {
            overlay.classList.add("hidden");
            root.classList.remove("overflow-hidden");
        } else {
            closeAll();
        }
    };

    handleDesktopChange(desktopMediaQuery);

    if (desktopMediaQuery.addEventListener) {
        desktopMediaQuery.addEventListener("change", handleDesktopChange);
    } else if (desktopMediaQuery.addListener) {
        desktopMediaQuery.addListener(handleDesktopChange);
    }
})();
