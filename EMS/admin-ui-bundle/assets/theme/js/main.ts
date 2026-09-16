import 'datatables.net-bs5/css/dataTables.bootstrap5.min.css';

import $ from 'jquery';
import 'datatables.net-bs5';

import { initSidebar } from './sidebar';
import { initTheme } from './theme';
import { initHeaderScroll } from './header-scroll';
import { initControlSidebar } from './control-sidebar';
import { initLogin } from './login';
import { initOrdersTable } from './orders-table';
import { initUiDemo } from './ui-demo';

document.addEventListener('DOMContentLoaded', () => {
  initSidebar();
  initTheme();
  initHeaderScroll();
  initControlSidebar();
  initLogin();
  initOrdersTable($);
  initUiDemo();
});
