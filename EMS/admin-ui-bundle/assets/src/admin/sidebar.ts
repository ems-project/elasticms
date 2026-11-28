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
        const menuLinks = document.querySelectorAll<HTMLAnchorElement>('#sidebar a.sidebar-link')
        const pathname = window.location.pathname

        let bestMatch: Element | null = null
        let bestMatchHrefLength = 0

        for (const link of menuLinks) {
            const href = link.getAttribute('href')
            if (!href || href === '#') {
                continue
            }
            const matches = pathname === href || pathname.startsWith(href.endsWith('/') ? href : href + '/')
            if (matches && href.length > bestMatchHrefLength) {
                bestMatch = link
                bestMatchHrefLength = href.length
            }
        }

        const start =
            bestMatch?.closest('.sidebar-item') ??
            document.querySelector('#sidebar .sidebar-item.active')

        let el: Element | null = start ?? null
        while (el) {
            el.classList.add('active')
            el.querySelector(':scope > .sidebar-dropdown.collapse')?.classList.add('show')
            el.querySelector(':scope > a.sidebar-link.collapsed')?.classList.remove('collapsed')
            const parentDropdown = el.parentElement?.closest('.sidebar-dropdown')
            parentDropdown?.classList.add('show')
            el = el.parentElement?.closest('.sidebar-item') ?? null
        }
    }
}
