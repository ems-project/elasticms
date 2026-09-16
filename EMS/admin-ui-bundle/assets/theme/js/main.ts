import 'datatables.net-bs5/css/dataTables.bootstrap5.min.css';

import $ from 'jquery';
import 'datatables.net-bs5';

import { initTheme } from './theme';
import { initLayout } from './layout';
import { initLogin } from './login';
import { initOrdersTable } from './orders-table';
import { initUiDemo } from './ui-demo';

document.addEventListener('DOMContentLoaded', () => {
  initTheme();
  initLayout();
  initLogin();
  initOrdersTable($);
  initUiDemo();
});
