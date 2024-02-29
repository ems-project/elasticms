import '../css/iframe.scss'

import { ClassicEditor } from '@ckeditor/ckeditor5-editor-classic'
import { Essentials } from '@ckeditor/ckeditor5-essentials'
import { Autoformat } from '@ckeditor/ckeditor5-autoformat'
import { Bold, Italic } from '@ckeditor/ckeditor5-basic-styles'
import { BlockQuote } from '@ckeditor/ckeditor5-block-quote'
import { Heading } from '@ckeditor/ckeditor5-heading'
import { Link } from '@ckeditor/ckeditor5-link'
import { List } from '@ckeditor/ckeditor5-list'
import { Paragraph } from '@ckeditor/ckeditor5-paragraph'

let loaded = false

function loadWYSIWYG() {
    ClassicEditor
        .create(document.getElementById('wysiwyg-content'), {
            plugins: [
                Essentials,
                Autoformat,
                Bold,
                Italic,
                BlockQuote,
                Heading,
                Link,
                List,
                Paragraph
            ],
            toolbar: [
                'heading',
                'bold',
                'italic',
                'link',
                'bulletedList',
                'numberedList',
                'blockQuote',
                'undo',
                'redo'
            ]
        })
        .then(editor => {
            console.log(editor)
        })
        .catch(error => {
            console.error(error)
        })
}

const resizeFct = function () {
    if (!loaded) {
        return
    }
    window.parent.postMessage('resize', document.body.dataset.targetOrigine)
}
const startReady = function () {
    if (loaded) {
        return
    }
    window.parent.postMessage('ready', document.body.dataset.targetOrigine)
    setTimeout(startReady, 1000)
}

window.addEventListener('message', function (event) {
    if (event.source !== window.parent) {
        console.log('Not a parent\'s message')
        return
    }
    loaded = true
    document.getElementById('wysiwyg-content').insertAdjacentHTML('afterbegin', event.data)
    loadWYSIWYG()
    resizeFct()
})

window.addEventListener('resize', resizeFct)
window.addEventListener('redraw', resizeFct)
window.addEventListener('load', resizeFct)
startReady()