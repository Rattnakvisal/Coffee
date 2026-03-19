(function () {
    const html = document.documentElement;
    const body = document.body;
    const sidebar = document.querySelector("[data-admin-sidebar]");
    const overlay = document.querySelector("[data-admin-sidebar-overlay]");
    const openButtons = document.querySelectorAll("[data-admin-sidebar-open]");
    const closeButtons = document.querySelectorAll(
        "[data-admin-sidebar-close]",
    );
    const desktopMedia = window.matchMedia("(min-width: 1024px)");

    if (!sidebar || !overlay) return;

    function lockScroll() {
        html.classList.add("overflow-hidden");
        body.classList.add("overflow-hidden");
    }

    function unlockScroll() {
        html.classList.remove("overflow-hidden");
        body.classList.remove("overflow-hidden");
    }

    function openSidebar() {
        if (desktopMedia.matches) return;

        sidebar.classList.remove("-translate-x-full");
        overlay.classList.remove("hidden");
        requestAnimationFrame(() => {
            overlay.classList.remove("opacity-0");
            overlay.classList.add("opacity-100");
        });

        lockScroll();
    }

    function closeSidebar() {
        if (desktopMedia.matches) return;

        sidebar.classList.add("-translate-x-full");
        overlay.classList.remove("opacity-100");
        overlay.classList.add("opacity-0");

        setTimeout(() => {
            if (!sidebar.classList.contains("-translate-x-full")) return;
            overlay.classList.add("hidden");
        }, 300);

        unlockScroll();
    }

    function syncSidebar() {
        if (desktopMedia.matches) {
            sidebar.classList.remove("-translate-x-full");
            overlay.classList.add("hidden", "opacity-0");
            overlay.classList.remove("opacity-100");
            unlockScroll();
        } else {
            sidebar.classList.add("-translate-x-full");
            overlay.classList.add("hidden", "opacity-0");
            overlay.classList.remove("opacity-100");
            unlockScroll();
        }
    }

    openButtons.forEach((button) => {
        button.addEventListener("click", openSidebar);
    });

    closeButtons.forEach((button) => {
        button.addEventListener("click", closeSidebar);
    });

    overlay.addEventListener("click", closeSidebar);

    document.addEventListener("keydown", function (event) {
        if (event.key === "Escape") {
            closeSidebar();
        }
    });

    syncSidebar();

    if (desktopMedia.addEventListener) {
        desktopMedia.addEventListener("change", syncSidebar);
    } else {
        desktopMedia.addListener(syncSidebar);
    }
})();
