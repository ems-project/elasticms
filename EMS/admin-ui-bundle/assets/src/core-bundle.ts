import '../css/core/_core_bundle.scss'

import WYSIWYG from './core/plugins/wysiwyg.ts'
import { getWysiwygProfile } from './core/components/wysiwyg/wysiwyg.ts'

window.addEventListener('emsReady', async function () {
    if ('tiptap' !== getWysiwygProfile().editor) return

    const wysiwyg = new WYSIWYG()
    await wysiwyg.load(document.body)

    let timeout: number | null = null

    const observer = new MutationObserver((mutations) => {
        const hasNewElements = mutations.some((m) =>
            Array.from(m.addedNodes).some((n) => n.nodeType === Node.ELEMENT_NODE)
        )

        if (hasNewElements) {
            if (timeout) window.clearTimeout(timeout)
            timeout = window.setTimeout(async () => {
                await wysiwyg.load(document.body)
            }, 100)
        }
    })

    observer.observe(document.body, { childList: true, subtree: true })
})
