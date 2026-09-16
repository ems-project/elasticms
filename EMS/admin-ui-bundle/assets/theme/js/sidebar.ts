const MOBILE_BREAKPOINT = 900;

export function isMobile(): boolean {
  return window.innerWidth < MOBILE_BREAKPOINT;
}

export function initSidebar(): void {
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
