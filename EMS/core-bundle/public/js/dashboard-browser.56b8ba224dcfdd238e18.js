/*
 * ATTENTION: The "eval" devtool has been used (maybe by default in mode: "development").
 * This devtool is neither made for production nor for readable output files.
 * It uses "eval()" calls to create a separate source file in the browser devtools.
 * If you are trying to read the output file, select a different devtool (https://webpack.js.org/configuration/devtool/)
 * or disable the default devtool with "devtool: false".
 * If you are looking for production-ready output files, see mode: "production" (https://webpack.js.org/configuration/mode/).
 */
/******/ (function() { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./assets/js/module/dashboardBrowser.js":
/*!**********************************************!*\
  !*** ./assets/js/module/dashboardBrowser.js ***!
  \**********************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _css_modules_dashboard_browser_scss__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./../../css/modules/dashboard-browser.scss */ \"./assets/css/modules/dashboard-browser.scss\");\n\ndocument.addEventListener('DOMContentLoaded', function (e) {\n  var params = new URL(window.location.href).searchParams;\n  var browserDiv = document.getElementById('dashboard-browser');\n  if (null === browserDiv) return;\n  var dashboardType = browserDiv.dataset.type;\n  document.addEventListener('click', function (event) {\n    if (event.target.tagName.toLowerCase() !== 'a') return;\n    event.preventDefault();\n    var url = new URL(event.target.href);\n    var text = event.target.innerText;\n    var emsId = event.target.dataset.emsId;\n    window.opener.CKEDITOR.tools.callFunction(params.get('CKEditorFuncNum'), url, function () {\n      var dialog = this.getDialog();\n\n      switch (dashboardType) {\n        case 'browser_image':\n          dialog.getContentElement('info', 'src').setValue(url.pathname + url.search);\n          break;\n\n        case 'browser_object':\n          dialog.getContentElement('info', 'localPage').setValue({\n            'id': emsId ? emsId.replace('ems://object:', '') : url.toString(),\n            'text': text\n          });\n          break;\n\n        case 'browser_file':\n          var fileLink = dialog.getContentElement('info', 'fileLink');\n          fileLink.setValue(text);\n          fileLink.getInputElement().$.setAttribute('data-link', emsId !== null && emsId !== void 0 ? emsId : url.toString());\n          break;\n      }\n    });\n    window.close();\n  });\n});\n\n//# sourceURL=webpack:///./assets/js/module/dashboardBrowser.js?");

/***/ }),

/***/ "./assets/css/modules/dashboard-browser.scss":
/*!***************************************************!*\
  !*** ./assets/css/modules/dashboard-browser.scss ***!
  \***************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

eval("__webpack_require__.r(__webpack_exports__);\n// extracted by mini-css-extract-plugin\n\n\n//# sourceURL=webpack:///./assets/css/modules/dashboard-browser.scss?");

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	!function() {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = function(exports) {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	}();
/******/ 	
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module can't be inlined because the eval devtool is used.
/******/ 	var __webpack_exports__ = __webpack_require__("./assets/js/module/dashboardBrowser.js");
/******/ 	
/******/ })()
;