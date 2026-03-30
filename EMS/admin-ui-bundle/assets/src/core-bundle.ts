import WYSIWYG from './core/plugins/wysiwyg.ts'
import { getWysiwygProfile } from './core/components/wysiwyg/WysiwygProfile.ts'

window.addEventListener('emsReady', async function () {
    if ('tiptap' !== getWysiwygProfile().editor) return

    await new WYSIWYG().load(document.body)
})
