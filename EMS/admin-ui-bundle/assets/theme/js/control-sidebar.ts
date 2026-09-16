export function initControlSidebar(): void {
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
