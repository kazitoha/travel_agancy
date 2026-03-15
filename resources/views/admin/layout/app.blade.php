<!doctype html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Dashboard</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'system-ui', '-apple-system', 'Segoe UI', 'Roboto', 'Helvetica Neue', 'Arial', 'sans-serif'],
                    },
                    keyframes: {
                        'pulse-soft': {
                            '0%, 100%': { opacity: '1' },
                            '50%': { opacity: '.7' },
                        }
                    },
                    animation: {
                        'pulse-soft': 'pulse-soft 2s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                    }
                }
            }
        }
    </script>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="min-h-screen bg-slate-50 font-sans text-slate-900">
    @include('admin.layout.mobile-menu')

    <div class="min-h-screen">
        <div class="flex min-h-screen">
            @include('admin.layout.options')

            <div class="flex min-w-0 flex-1 flex-col">
                @include('admin.layout.header')

                <main id="mainContent" class="flex-1 px-4 py-5 pb-24 transition-opacity duration-200 md:px-8 md:py-6 md:pb-6">
                    @yield('admin-content')

                    <div class="mt-8 pb-6 text-center text-xs text-slate-400">
                        © 2026 Travel Agency • Developed by Zentrik Technology Ltd.
                    </div>
                </main>
            </div>
        </div>
    </div>

    @include('admin.layout.mobile-buttom-bar')

    @stack('scripts')

    <script>
        // -------- Dynamic Clock --------
        function updateClock() {
            const dateEl = document.querySelector('[data-date]');
            const timeEl = document.querySelector('[data-time]');
            if (!dateEl || !timeEl) return;

            const now = new Date();

            const dateFormatter = new Intl.DateTimeFormat('en-US', {
                weekday: 'long',
                month: 'long',
                day: 'numeric',
                year: 'numeric'
            });

            const timeFormatter = new Intl.DateTimeFormat('en-US', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: true
            });

            dateEl.textContent = dateFormatter.format(now);
            timeEl.textContent = timeFormatter.format(now);
        }

        updateClock();
        setInterval(updateClock, 1000);

        // -------- Drawer --------
        const overlay = document.getElementById("drawerOverlay");
        const drawer = document.getElementById("mobileDrawer");
        const openBtns = [
            document.getElementById("mobileMenuBtn"),
            document.getElementById("bottomMenuBtn")
        ].filter(Boolean);
        const closeBtn = document.getElementById("drawerClose");

        function openDrawer() {
            overlay?.classList.remove("hidden");
            setTimeout(() => {
                drawer?.classList.remove("-translate-x-full");
            }, 10);
            drawer?.setAttribute("aria-hidden", "false");
            document.body.classList.add("overflow-hidden");
        }

        function closeDrawer() {
            drawer?.classList.add("-translate-x-full");
            setTimeout(() => {
                overlay?.classList.add("hidden");
            }, 300);
            drawer?.setAttribute("aria-hidden", "true");
            document.body.classList.remove("overflow-hidden");
        }

        openBtns.forEach((btn) => btn.addEventListener("click", openDrawer));
        closeBtn?.addEventListener("click", closeDrawer);
        overlay?.addEventListener("click", closeDrawer);

        document.addEventListener("keydown", (e) => {
            if (e.key === "Escape") closeDrawer();
        });

        // -------- Desktop sidebar toggle --------
        const sidebarToggleBtn = document.getElementById("sidebarToggleBtn");
        const sidebarStorageKey = "adminSidebarCollapsed";
        const adminSidebar = document.getElementById("adminSidebar");

        function setSidebarCollapsed(isCollapsed) {
            document.body.classList.toggle("sidebar-collapsed", isCollapsed);

            if (adminSidebar) {
                if (window.innerWidth >= 768) {
                    adminSidebar.classList.toggle("md:w-20", isCollapsed);
                    adminSidebar.classList.toggle("md:w-72", !isCollapsed);
                }
            }

            document.querySelectorAll(".sidebar-brand-text, .sidebar-label, .sidebar-section-title, .sidebar-chevron")
                .forEach(el => el.classList.toggle("hidden", isCollapsed));

            document.querySelectorAll(".sidebar-submenu")
                .forEach(el => el.classList.toggle("hidden", isCollapsed));

            document.querySelectorAll(".sidebar-nav")
                .forEach(el => {
                    el.classList.toggle("px-3", !isCollapsed);
                    el.classList.toggle("px-2", isCollapsed);
                });

            document.querySelectorAll(".sidebar-item")
                .forEach(el => {
                    el.classList.toggle("justify-center", isCollapsed);
                    el.classList.toggle("justify-start", !isCollapsed);
                });

            sidebarToggleBtn?.setAttribute("aria-pressed", isCollapsed ? "true" : "false");

            try {
                localStorage.setItem(sidebarStorageKey, isCollapsed ? "1" : "0");
            } catch (e) {}
        }

        try {
            const saved = localStorage.getItem(sidebarStorageKey);
            if (saved === "1") setSidebarCollapsed(true);
        } catch (e) {}

        sidebarToggleBtn?.addEventListener("click", () => {
            const isCollapsed = document.body.classList.contains("sidebar-collapsed");
            setSidebarCollapsed(!isCollapsed);
        });

        // -------- Active Navigation Link --------
        function updateActiveNavLink() {
            const currentPath = window.location.pathname;

            document.querySelectorAll('a[href^="/"]').forEach(link => {
                link.classList.remove('bg-blue-50', 'text-blue-600', 'border-blue-200');

                if (link.getAttribute('href') === currentPath) {
                    link.classList.add('bg-blue-50', 'text-blue-600', 'border-blue-200');
                }
            });
        }

        updateActiveNavLink();
        window.addEventListener('popstate', updateActiveNavLink);

        // -------- Top profile dropdown --------
        const profileBtn = document.getElementById("profileBtn");
        const profileMenu = document.getElementById("profileMenu");
        const bottomProfileBtn = document.getElementById("bottomProfileBtn");
        const bottomProfileMenu = document.getElementById("bottomProfileMenu");

        function closeProfileMenu() {
            profileMenu?.classList.add("hidden");
            profileBtn?.setAttribute("aria-expanded", "false");
        }

        profileBtn?.addEventListener("click", (e) => {
            e.stopPropagation();
            const isHidden = profileMenu?.classList.contains("hidden");

            if (bottomProfileMenu) bottomProfileMenu.classList.add("hidden");

            if (isHidden) {
                profileMenu?.classList.remove("hidden");
                profileBtn?.setAttribute("aria-expanded", "true");
            } else {
                closeProfileMenu();
            }
        });

        // -------- Bottom profile dropdown --------
        bottomProfileBtn?.addEventListener("click", (e) => {
            e.stopPropagation();
            closeProfileMenu();
            bottomProfileMenu?.classList.toggle("hidden");
        });

        // -------- Click outside to close menus --------
        document.addEventListener("click", () => {
            closeProfileMenu();
            bottomProfileMenu?.classList.add("hidden");
        });

        profileMenu?.addEventListener("click", (e) => e.stopPropagation());
        bottomProfileMenu?.addEventListener("click", (e) => e.stopPropagation());
        drawer?.addEventListener("click", (e) => e.stopPropagation());

        // -------- Smooth page transitions --------
        document.addEventListener('click', (e) => {
            const link = e.target.closest('a[href^="/"]');

            if (link && !e.metaKey && !e.ctrlKey) {
                const mainContent = document.getElementById('mainContent');
                if (mainContent) {
                    mainContent.classList.add('opacity-70', 'pointer-events-none');
                }
            }
        });

        window.addEventListener('load', () => {
            const mainContent = document.getElementById('mainContent');
            if (mainContent) {
                mainContent.classList.remove('opacity-70', 'pointer-events-none');
            }
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('account-form');
            const amountInputs = document.querySelectorAll('.amount-input');

            function formatValue(input) {
                let cursorPosition = input.selectionStart;
                let originalLength = input.value.length;

                let rawValue = input.value.replace(/,/g, '');

                if (!isNaN(rawValue) && rawValue !== "") {
                    let parts = rawValue.split('.');
                    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");

                    let formattedValue = parts.join('.');
                    input.value = formattedValue;

                    if (document.activeElement === input) {
                        let newLength = formattedValue.length;
                        input.setSelectionRange(
                            cursorPosition + (newLength - originalLength),
                            cursorPosition + (newLength - originalLength)
                        );
                    }
                }
            }

            amountInputs.forEach(input => formatValue(input));

            amountInputs.forEach(input => {
                input.addEventListener('input', () => formatValue(input));
            });

            if (form) {
                form.addEventListener('submit', function () {
                    amountInputs.forEach(input => {
                        input.value = input.value.replace(/,/g, '');
                    });
                });
            }
        });
    </script>
</body>
</html>