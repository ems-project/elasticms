import WYSIWYG from './core/plugins/wysiwyg.ts'
import { fa4Icons } from './core/components/tiptap/types.ts'

window.addEventListener('emsReady', async function () {
    const wysiwyg = new WYSIWYG()
    if ('tiptap' !== wysiwyg.profile?.editor) return

    await wysiwyg.load(document.body, { icons: fa4Icons })
})