import { isMobile } from './sidebar';

const PAGE_TITLES: Record<string, string> = {
  dashboard: 'Dashboard',
  forms: 'Formulieren',
  ui: 'UI-elementen',
};

export function initPageRouter(): void {
  const body = document.body;
  const pageTitle = document.getElementById('pageTitle');
  const pageCrumb = document.getElementById('pageCrumb');

  document.querySelectorAll<HTMLAnchorElement>('.nav-page').forEach((link) => {
    link.addEventListener('click', (event) => {
      event.preventDefault();
      const page = link.dataset.page;
      if (!page) {
        return;
      }

      document.querySelectorAll('.page-section').forEach((section) => {
        section.classList.toggle('active', section.id === `page-${page}`);
      });

      document.querySelectorAll<HTMLElement>('[data-page-item]').forEach((li) => {
        li.classList.toggle('active', li.dataset.pageItem === page);
      });

      const title = PAGE_TITLES[page] ?? page;
      if (pageTitle) {
        pageTitle.textContent = title;
      }
      if (pageCrumb) {
        pageCrumb.textContent = title;
      }

      if (isMobile()) {
        body.classList.remove('sidebar-open');
      }
      window.scrollTo(0, 0);
    });
  });
}
