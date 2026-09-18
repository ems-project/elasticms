'use strict'

const SIDEBAR_COLLAPSED_STORAGE_KEY = 'ems.sidebar.collapsed'
const SIDEBAR_MINI_STORAGE_KEY = 'ems.sidebar.mini'
const SIDEBAR_TEMPORARY_OPEN_CLASS = 'sidebar-temporary-open'

export default class Sidebar {
    constructor() {
        this.activateMenu()
        this.initToggle()
        this.initMiniToggle()
    }

    initToggle() {
        const sidebar = document.getElementById('sidebar')
        const toggle = document.querySelector('.js-sidebar-toggle')

        if (!sidebar || !toggle) {
            return
        }

        const isCollapsed = document.documentElement.classList.contains('sidebar-collapsed')
        sidebar.classList.toggle('collapsed', isCollapsed)
        this.initTemporaryAccess(sidebar, toggle.getAttribute('aria-label') ?? 'Sidebar menu')

        toggle.addEventListener('click', (event) => {
            event.preventDefault()
            event.stopPropagation()
            this.closeTemporarySidebar()
            const collapsed = sidebar.classList.toggle('collapsed')
            document.documentElement.classList.toggle('sidebar-collapsed', collapsed)
            this.saveCollapsedState(collapsed)
        })
    }

    initMiniToggle() {
        const isMini = document.documentElement.classList.contains('sidebar-mini')
        document.documentElement.classList.toggle('sidebar-mini', isMini)

        const toggle = document.getElementById('sidebarMiniToggle')
        if (!(toggle instanceof HTMLInputElement)) {
            return
        }
        toggle.checked = isMini

        toggle.addEventListener('change', () => {
            const mini = toggle.checked
            document.documentElement.classList.toggle('sidebar-mini', mini)
            this.saveMiniState(mini)
        })
    }

    initTemporaryAccess(sidebar: HTMLElement, label: string) {
        const trigger = document.createElement('button')
        trigger.type = 'button'
        trigger.className = 'sidebar-temporary-toggle'
        trigger.setAttribute('aria-label', label)
        this.applyThemeColor(trigger)

        const backdrop = document.createElement('button')
        backdrop.type = 'button'
        backdrop.className = 'sidebar-temporary-backdrop'
        backdrop.setAttribute('aria-label', label)

        document.body.append(trigger, backdrop)

        trigger.addEventListener('click', () => {
            this.openTemporarySidebar(sidebar)
        })
        backdrop.addEventListener('click', () => {
            this.closeTemporarySidebar()
        })
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                this.closeTemporarySidebar()
            }
        })
    }

    applyThemeColor(element: HTMLElement) {
        const themeColor = document.body.getAttribute('data-theme-color')

        if (themeColor) {
            element.classList.add(`bg-${themeColor}`)
        }
    }

    openTemporarySidebar(sidebar: HTMLElement) {
        if (!sidebar.classList.contains('collapsed')) {
            return
        }

        document.documentElement.classList.add(SIDEBAR_TEMPORARY_OPEN_CLASS)
    }

    closeTemporarySidebar() {
        document.documentElement.classList.remove(SIDEBAR_TEMPORARY_OPEN_CLASS)
    }

    saveCollapsedState(collapsed: boolean) {
        try {
            localStorage.setItem(SIDEBAR_COLLAPSED_STORAGE_KEY, collapsed ? '1' : '0')
        } catch {
            return
        }
    }

    saveMiniState(mini: boolean) {
        try {
            localStorage.setItem(SIDEBAR_MINI_STORAGE_KEY, mini ? '1' : '0')
        } catch {
            return
        }
    }

    activateMenu() {
        let bestMatch: Element | null = null
        let bestMatchHrefLength = 0
        const menuLinks = document.querySelectorAll('#sidebar a.sidebar-link')
        const pathname = window.location.pathname

        for (let i = 0; i < menuLinks.length; ++i) {
            const href = menuLinks[i].getAttribute('href')
            if (href && href !== '#' && pathname.startsWith(href) && href.length > bestMatchHrefLength) {
                bestMatch = menuLinks[i]
                bestMatchHrefLength = href.length
            }
        }

        if (bestMatch === null) {
            return
        }

        let el = bestMatch.closest('.sidebar-item')
        while (el) {
            el.classList.add('active')
            const collapse = el.querySelector(':scope > .sidebar-dropdown.collapse')
            if (collapse) {
                collapse.classList.add('show')
            }
            const link = el.querySelector(':scope > a.sidebar-link.collapsed')
            if (link) {
                link.classList.remove('collapsed')
            }
            el = el.parentElement?.closest('.sidebar-item') ?? null
        }
    }
}
