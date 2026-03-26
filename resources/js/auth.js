document.addEventListener("DOMContentLoaded", function () {
    const roleProfilesData = document.getElementById("role-profiles");
    const roleProfiles = roleProfilesData
        ? JSON.parse(roleProfilesData.textContent || "{}")
        : {};
    const roleButtons = Array.from(
        document.querySelectorAll("[data-role-toggle]"),
    );
    const form = document.getElementById("login-form");
    const actionTemplate = form
        ? form.getAttribute("data-action-template")
        : "";
    const viewTemplate = form ? form.getAttribute("data-view-template") : "";
    const accessLabel = document.getElementById("role-access-label");
    const submitLabel = document.getElementById("login-submit-label");
    const submitButton = document.getElementById("login-submit-button");
    const submitArrow = document.getElementById("login-submit-arrow");
    const submitSpinner = document.getElementById("login-submit-spinner");
    const profileCard = document.getElementById("role-profile-card");
    const profileTitle = document.getElementById("role-profile-title");
    const profileSubtitle = document.getElementById("role-profile-subtitle");
    const profileDescription = document.getElementById(
        "role-profile-description",
    );
    let isSubmitting = false;

    const setRoleState = function (roleSlug) {
        const profile = roleProfiles[roleSlug];
        if (!profile) return;

        roleButtons.forEach(function (button) {
            const isActive =
                button.getAttribute("data-role-toggle") === roleSlug;
            button.setAttribute("data-active", isActive ? "true" : "false");
            button.classList.toggle("bg-white", isActive);
            button.classList.toggle("text-[#2f241f]", isActive);
            button.classList.toggle("shadow-sm", isActive);
            button.classList.toggle("text-[#7a5c4e]", !isActive);
            button.classList.toggle("hover:text-[#2f241f]", !isActive);
        });

        if (form && actionTemplate) {
            form.setAttribute(
                "action",
                actionTemplate.replace("__ROLE__", roleSlug),
            );
        }

        if (viewTemplate) {
            history.replaceState(
                {
                    role: roleSlug,
                },
                "",
                viewTemplate.replace("__ROLE__", roleSlug),
            );
        }

        if (accessLabel) {
            accessLabel.textContent = profile.label + " Access";
        }

        if (submitLabel) {
            submitLabel.textContent = "Continue to " + profile.label;
        }

        if (submitButton) {
            submitButton.style.backgroundColor = profile.button;
            submitButton.setAttribute("data-bg", profile.button);
            submitButton.setAttribute("data-bg-hover", profile.buttonHover);
        }

        if (profileCard) {
            profileCard.style.background = profile.gradient;
        }

        if (profileTitle) {
            profileTitle.textContent = profile.label;
        }

        if (profileSubtitle) {
            profileSubtitle.textContent = profile.subtitle;
        }

        if (profileDescription) {
            if (profile.description) {
                profileDescription.classList.remove("hidden");
                profileDescription.textContent = profile.description;
            } else {
                profileDescription.classList.add("hidden");
                profileDescription.textContent = "";
            }
        }
    };

    roleButtons.forEach(function (button) {
        button.addEventListener("click", function () {
            setRoleState(button.getAttribute("data-role-toggle"));
        });
    });

    const activeButton =
        roleButtons.find(function (button) {
            return button.getAttribute("data-active") === "true";
        }) || roleButtons[0];

    if (activeButton) {
        setRoleState(activeButton.getAttribute("data-role-toggle"));
    }

    if (submitButton) {
        submitButton.addEventListener("mouseenter", function () {
            const hoverColor = submitButton.getAttribute("data-bg-hover");
            if (hoverColor) {
                submitButton.style.backgroundColor = hoverColor;
            }
        });

        submitButton.addEventListener("mouseleave", function () {
            const baseColor = submitButton.getAttribute("data-bg");
            if (baseColor) {
                submitButton.style.backgroundColor = baseColor;
            }
        });
    }

    if (form) {
        form.addEventListener("submit", function (event) {
            if (isSubmitting) {
                event.preventDefault();
                return;
            }

            isSubmitting = true;

            if (submitButton) {
                submitButton.disabled = true;
                submitButton.setAttribute("aria-busy", "true");
                submitButton.classList.add("cursor-not-allowed", "opacity-90");
            }

            roleButtons.forEach(function (button) {
                button.disabled = true;
                button.classList.add("opacity-70");
            });

            if (submitLabel) {
                submitLabel.textContent = "Signing in...";
            }

            if (submitArrow) {
                submitArrow.classList.add("hidden");
            }

            if (submitSpinner) {
                submitSpinner.classList.remove("hidden");
            }
        });
    }
});

document.addEventListener("click", function (event) {
    const button = event.target.closest("[data-toggle-password]");
    if (!button) return;

    const inputId = button.getAttribute("data-target");
    const input = document.getElementById(inputId);
    if (!input) return;

    const isPassword = input.getAttribute("type") === "password";
    input.setAttribute("type", isPassword ? "text" : "password");
    button.textContent = isPassword ? "Hide" : "Show";
});
