import '../css/core/_core_bundle.scss'

import WYSIWYG from './core/plugins/wysiwyg.ts'
import { getWysiwygProfile } from './core/components/Wysiwyg/Wysiwyg.ts'
import MediaLibraryPlugin from './core/plugins/mediaLibrary.ts'

const mediaLibrary = new MediaLibraryPlugin(window.ajaxModal)

window.addEventListener('emsReady', function () {
    mediaLibrary.load(document)

    // .media-lib elements can be added dynamically (e.g. collection add, ajax loaded tabs)
    // without necessarily going through the 'emsAddedDomEvent'/ajaxModal flow, so a
    // MutationObserver is used here too, matching the approach used for WYSIWYG below.
    let mediaLibraryTimeout: number | null = null
    const mediaLibraryObserver = new MutationObserver((mutations) => {
        const hasNewElements = mutations.some((m) =>
            Array.from(m.addedNodes).some((n) => n.nodeType === Node.ELEMENT_NODE)
        )

        if (hasNewElements) {
            if (mediaLibraryTimeout) window.clearTimeout(mediaLibraryTimeout)
            mediaLibraryTimeout = window.setTimeout(() => {
                mediaLibrary.load(document)
            }, 100)
        }
    })

    mediaLibraryObserver.observe(document.body, { childList: true, subtree: true })
})

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

document.addEventListener('emsAddedDomEvent', function (e) {
    new window.EmsListeners(e.detail.target)
    mediaLibrary.load(e.detail.target)
})
