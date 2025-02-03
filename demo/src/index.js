'use strict';
window.$ = $;
window.jQuery = $;

import './css/styles.scss';
import 'bootstrap';

import adminMenu from '@elasticms/admin-menu';
import back2top from "./js/back2top";
import ajaxSearch from "./js/ajax-search";
import toc from "./js/toc";
import externalLink from "./js/external-link";
import form from "./js/form";
import {NavBar} from "./js/navbar";
import multilevelNavbar from "./js/multilevel-navbar";
import Cookies from 'js-cookie';
import cookiesBanner from "./js/cookiesBanner";
import newsFilters from "./js/news-filters";
import searchRelease from "./js/search-release";


const translations = JSON.parse(document.body.getAttribute('data-translations'));

adminMenu('esm_demo_admin', '<i class="ems-icon"></i><span class="sr-only">'+ (translations.back_to_ems === undefined ? 'Back to ems' : translations.back_to_ems) + '</span>');
back2top();
ajaxSearch();
toc();
externalLink();
form();
multilevelNavbar();
searchRelease(translations);

const navBar = new NavBar();
// navBar.activateBestItem();

$(document).ready(function() {
    cookiesBanner(Cookies);
    newsFilters();
});

console.log('Demo website loaded!');
