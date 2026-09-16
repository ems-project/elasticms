import '../styles/app.scss';
import '@fortawesome/fontawesome-free/css/fontawesome.min.css';
import '@fortawesome/fontawesome-free/css/solid.min.css';
import 'datatables.net-bs5/css/dataTables.bootstrap5.min.css';

import 'bootstrap';
import $ from 'jquery';
import 'datatables.net-bs5';

import { initSidebar } from './sidebar';
import { initPageRouter } from './page-router';
import { initTheme } from './theme';
import { initHeaderScroll } from './header-scroll';
import { initControlSidebar } from './control-sidebar';
import { initLogin } from './login';
import { initOrdersTable } from './orders-table';
import { initUiDemo } from './ui-demo';

document.addEventListener('DOMContentLoaded', () => {
  initSidebar();
  initPageRouter();
  initTheme();
  initHeaderScroll();
  initControlSidebar();
  initLogin();
  initOrdersTable($);
  initUiDemo();
});
