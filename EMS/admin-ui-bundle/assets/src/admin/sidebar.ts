'use strict'

const MOBILE_QUERY = '(max-width: 991.98px)'

export const SIDEBAR_COLLAPSED_CHANGE_EVENT = 'ems:sidebar-collapsed-change'

export default class Sidebar {
    sidebar: HTMLElement | null = null

    constructor() {
        this.activateMenu()
        this.initToggle()
    }

    initToggle() {
        const sidebar = document.getElementById('sidebar')
        const toggle = document.querySelector('.js-sidebar-toggle')

        if (!sidebar || !toggle) {
            return
        }
        this.sidebar = sidebar

        const isCollapsed = document.documentElement.hasAttribute('data-sidebar-collapsed')
        sidebar.classList.toggle('collapsed', isCollapsed)
        this.initBackdrop(sidebar, toggle.getAttribute('aria-label') ?? 'Sidebar menu')

        toggle.addEventListener('click', (event) => {
            event.preventDefault()
            event.stopPropagation()
            this.setCollapsed(sidebar.classList.toggle('collapsed'))
        })
    }

    setCollapsed(collapsed: boolean) {
        document.documentElement.toggleAttribute('data-sidebar-collapsed', collapsed)
        document.dispatchEvent(
            new CustomEvent(SIDEBAR_COLLAPSED_CHANGE_EVENT, { detail: { collapsed } })
        )
    }

    initBackdrop(sidebar: HTMLElement, label: string) {
        const backdrop = document.createElement('button')
        backdrop.type = 'button'
        backdrop.className = 'sidebar-temporary-backdrop'
        backdrop.setAttribute('aria-label', label)
        document.body.append(backdrop)

        backdrop.addEventListener('click', () => this.closeOverlay(sidebar))
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                this.closeOverlay(sidebar)
            }
        })
    }

    closeOverlay(sidebar: HTMLElement) {
        if (window.matchMedia(MOBILE_QUERY).matches && sidebar.classList.contains('collapsed')) {
            sidebar.classList.remove('collapsed')
            this.setCollapsed(false)
        }
    }

    activateMenu() {
        let bestMatch: Element | null = null
        let bestMatchHrefLength = 0
        const menuLinks = document.querySelectorAll('#sidebar a.sidebar-link')
        const pathname = window.location.pathname

        for (let i = 0; i < menuLinks.length; ++i) {
            const href = menuLinks[i].getAttribute('href')
            if (
                href &&
                href !== '#' &&
                pathname.startsWith(href) &&
                href.length > bestMatchHrefLength
            ) {
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
