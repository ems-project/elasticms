const assetPath = document.body.dataset.assetPath
window.CKEDITOR_BASEPATH = assetPath + 'bundles/emscore/js/ckeditor/';
import '../css/adminlte.scss'
import '../css/fontawsome.scss'
import '../css/plugins.scss'

import '@popperjs/core'
import * as bootstrap from 'bootstrap'
import './admin-lte/AdminLTE'
import './core/core'

window.bootstrap = bootstrap

console.log('Bootstrap 5 UI loaded')
