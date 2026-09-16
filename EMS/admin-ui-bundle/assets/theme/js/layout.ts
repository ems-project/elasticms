const MOBILE_BREAKPOINT = 900;

function isMobile(): boolean {
    return window.innerWidth < MOBILE_BREAKPOINT;
}

function initHeaderScroll(): void {
    const body = document.body;
    let lastScrollY = window.scrollY;

    window.addEventListener(
        'scroll',
        () => {
            const y = window.scrollY;
            if (y > 24 && y > lastScrollY) {
                body.classList.add('header-compact');
            } else if (y < 8) {
                body.classList.remove('header-compact');
            }
            lastScrollY = y;
        },
        { passive: true },
    );
}

function initControlSidebar(): void {
    const body = document.body;
    const toggle = document.getElementById('controlSidebarToggle');
    const close = document.getElementById('controlSidebarClose');
    const overlay = document.getElementById('controlSidebarOverlay');

    if (!toggle || !close || !overlay) {
        return;
    }

    toggle.addEventListener('click', () => {
        body.classList.toggle('control-sidebar-open');
    });
    close.addEventListener('click', () => {
        body.classList.remove('control-sidebar-open');
    });
    overlay.addEventListener('click', () => {
        body.classList.remove('control-sidebar-open');
    });
}

function initSidebar(): void {
    const body = document.body;
    const toggle = document.getElementById('sidebarToggle');
    const overlay = document.getElementById('sidebarOverlay');

    if (!toggle || !overlay) {
        return;
    }

    let collapsed = false;
    try {
        collapsed = localStorage.getItem('adminlte-collapsed') === '1';
    } catch {
        collapsed = false;
    }
    if (collapsed && !isMobile()) {
        body.classList.add('sidebar-collapse');
    }
    document.documentElement.removeAttribute('data-collapsed');

    toggle.addEventListener('click', () => {
        if (isMobile()) {
            body.classList.toggle('sidebar-open');
        } else {
            body.classList.toggle('sidebar-collapse');
            try {
                localStorage.setItem(
                    'adminlte-collapsed',
                    body.classList.contains('sidebar-collapse') ? '1' : '0',
                );
            } catch {
                /* storage unavailable */
            }
        }
    });

    overlay.addEventListener('click', () => {
        body.classList.remove('sidebar-open');
    });

    document.querySelectorAll<HTMLAnchorElement>('.menu-toggle').forEach((link) => {
        link.addEventListener('click', (event) => {
            event.preventDefault();
            const li = link.closest('li');
            if (!li) {
                return;
            }
            const wasOpen = li.classList.contains('menu-open');
            document.querySelectorAll('.sidebar-menu li.menu-open').forEach((open) => {
                if (open !== li) {
                    open.classList.remove('menu-open');
                }
            });
            li.classList.toggle('menu-open', !wasOpen);
        });
    });

    window.addEventListener('resize', () => {
        if (!isMobile()) {
            body.classList.remove('sidebar-open');
        }
    });
}

function initAvatarFallback(): void {
    document.querySelectorAll<HTMLImageElement>('.avatar-img').forEach((img) => {
        if (img.complete && img.naturalWidth === 0) {
            img.style.display = 'none';
        } else {
            img.addEventListener('error', () => (img.style.display = 'none'), { once: true });
        }
    });
}

export function initLayout(): void {
    initHeaderScroll();
    initControlSidebar();
    initSidebar();
    initAvatarFallback();
}