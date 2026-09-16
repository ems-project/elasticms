export function initHeaderScroll(): void {
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
