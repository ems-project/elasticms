'use strict'

const SIDEBAR_COLLAPSED_STORAGE_KEY = 'ems.sidebar.collapsed'
const SIDEBAR_TEMPORARY_OPEN_CLASS = 'sidebar-temporary-open'

export default class Sidebar {
    sidebar: HTMLElement | null = null

    constructor() {
        this.activateMenu()
        this.initToggle()
        this.initCollapseCheckbox()
    }

    initToggle() {
        const sidebar = document.getElementById('sidebar')
        const toggle = document.querySelector('.js-sidebar-toggle')

        if (!sidebar || !toggle) {
            return
        }
        this.sidebar = sidebar

        const isCollapsed = document.documentElement.classList.contains('sidebar-collapsed')
        sidebar.classList.toggle('collapsed', isCollapsed)
        this.initTemporaryAccess(sidebar, toggle.getAttribute('aria-label') ?? 'Sidebar menu')

        toggle.addEventListener('click', (event) => {
            event.preventDefault()
            event.stopPropagation()
            this.closeTemporarySidebar()
            this.setCollapsed(sidebar.classList.toggle('collapsed'))
        })
    }

    setCollapsed(collapsed: boolean) {
        document.documentElement.classList.toggle('sidebar-collapsed', collapsed)
        this.saveCollapsedState(collapsed)
        const checkbox = document.getElementById('devSidebarCollapsed')
        if (checkbox instanceof HTMLInputElement) {
            checkbox.checked = collapsed
        }
    }

    initCollapseCheckbox() {
        const checkbox = document.getElementById('devSidebarCollapsed')
        if (!(checkbox instanceof HTMLInputElement) || !this.sidebar) {
            return
        }
        checkbox.checked = this.sidebar.classList.contains('collapsed')

        checkbox.addEventListener('change', () => {
            if (!this.sidebar) {
                return
            }
            this.sidebar.classList.toggle('collapsed', checkbox.checked)
            this.setCollapsed(checkbox.checked)
        })
    }

    initTemporaryAccess(sidebar: HTMLElement, label: string) {
        const trigger = document.createElement('button')
        trigger.type = 'button'
        trigger.className = 'sidebar-temporary-toggle'
        trigger.setAttribute('aria-label', label)

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
