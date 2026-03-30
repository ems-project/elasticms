import WYSIWYG from './core/plugins/wysiwyg.ts'

window.addEventListener('emsReady', async function () {
    const wysiwyg = new WYSIWYG()
    if ('tiptap' !== wysiwyg.profile?.editor) return

    await wysiwyg.load(document.body)
})
