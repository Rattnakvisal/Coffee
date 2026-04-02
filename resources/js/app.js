import "./bootstrap";

(() => {
    const sidebarScroll = document.querySelector("[data-sidebar-scroll]");

    if (!sidebarScroll) {
        return;
    }

    if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) {
        return;
    }

    let targetScrollTop = sidebarScroll.scrollTop;
    let animationFrameId = null;

    const clampTarget = () => {
        const maxScrollTop = Math.max(
            0,
            sidebarScroll.scrollHeight - sidebarScroll.clientHeight,
        );
        targetScrollTop = Math.min(Math.max(0, targetScrollTop), maxScrollTop);
    };

    const animate = () => {
        const distance = targetScrollTop - sidebarScroll.scrollTop;

        if (Math.abs(distance) < 0.5) {
            sidebarScroll.scrollTop = targetScrollTop;
            animationFrameId = null;
            return;
        }

        sidebarScroll.scrollTop += distance * 0.22;
        animationFrameId = window.requestAnimationFrame(animate);
    };

    sidebarScroll.addEventListener(
        "wheel",
        (event) => {
            if (window.innerWidth < 1024) {
                return;
            }

            const canScroll =
                sidebarScroll.scrollHeight > sidebarScroll.clientHeight;
            if (!canScroll) {
                return;
            }

            const atTop = sidebarScroll.scrollTop <= 0;
            const atBottom =
                sidebarScroll.scrollTop + sidebarScroll.clientHeight >=
                sidebarScroll.scrollHeight - 1;

            if ((event.deltaY < 0 && atTop) || (event.deltaY > 0 && atBottom)) {
                return;
            }

            event.preventDefault();
            targetScrollTop += event.deltaY;
            clampTarget();

            if (!animationFrameId) {
                animationFrameId = window.requestAnimationFrame(animate);
            }
        },
        { passive: false },
    );

    sidebarScroll.addEventListener(
        "scroll",
        () => {
            if (!animationFrameId) {
                targetScrollTop = sidebarScroll.scrollTop;
            }
        },
        { passive: true },
    );
})();

(() => {
    if (document.getElementById("dashboard-search-input")) {
        import("./admin_index");
    }

    if (document.getElementById("adminReportsTrendChart")) {
        import("./admin_reports");
    }

    if (document.querySelector("[data-cashier-menu]")) {
        import("./cashier_index");
    }

    if (document.querySelector("[data-admin-sidebar]")) {
        import("./sidebar_admin");
    }

    if (document.querySelector("[data-inventory-outgoing-toggle]")) {
        import("./admin_inventory");
    }
    if (document.querySelector("[data-attendance-page]")) {
        import("./attendance");
    }
})();
