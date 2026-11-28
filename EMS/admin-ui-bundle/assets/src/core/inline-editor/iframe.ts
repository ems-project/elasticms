import '@css/core/components/_inline_editor_iframe.scss'
import { Iframe } from './iframe/iframe'

function setup() {
    const isIframe = window.self !== window.top

    document.addEventListener('DOMContentLoaded', () => {
        if (isIframe) {
            const frame = window.frameElement as HTMLIFrameElement | null
            new Iframe({ prefix: frame?.dataset.prefix ?? '' })
        } else {
            const btn = document.getElementById('go2editor')
            if (btn) btn.style.display = 'flex'
        }
    })
}

setup()
