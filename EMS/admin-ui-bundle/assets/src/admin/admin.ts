'use strict'

import Theme from './theme.ts'
import Sidebar from './sidebar.ts'
import DebugToolbar from './debugToolbar.ts'

function initHeaderScroll() {
    let compact: boolean | null = null
    const update = () => {
        const next = window.scrollY > 10
        if (next !== compact) {
            document.documentElement.classList.toggle('header-compact', next)
            compact = next
        }
    }
    window.addEventListener('scroll', update, { passive: true })
    update()
}

async function init() {
    const theme = new Theme()
    const sidebar = new Sidebar()

    if (document.getElementById('dev-panel')) {
        const { default: DevPanel } = await import('./devPanel.ts')
        new DevPanel(theme, sidebar)
    }

    new DebugToolbar()
    initHeaderScroll()
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init)
} else {
    init()
}
