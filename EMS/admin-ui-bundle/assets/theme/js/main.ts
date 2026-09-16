import 'datatables.net-bs5/css/dataTables.bootstrap5.min.css';

import $ from 'jquery';
import 'datatables.net-bs5';

import { initLayout } from './layout';
import { initTheme } from './theme';
import { initLogin } from './login';
import { initOrdersTable } from './orders-table';
import { initUiDemo } from './ui-demo';



document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll<HTMLImageElement>('.avatar-img').forEach(img => {
    if (img.complete && img.naturalWidth === 0) {
      img.style.display = 'none';
    } else {
      img.addEventListener('error', () => img.style.display = 'none', { once: true });
    }
  });

  initLayout();
  initTheme();
  initLogin();
  initOrdersTable($);
  initUiDemo();
});
