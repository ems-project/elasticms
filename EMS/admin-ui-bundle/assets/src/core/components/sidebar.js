'use strict'

const SIDEBAR_COLLAPSED_STORAGE_KEY = 'ems.sidebar.collapsed'

export default class Sidebar {
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

        const isCollapsed = document.documentElement.classList.contains('sidebar-collapsed')
        sidebar.classList.toggle('collapsed', isCollapsed)

        toggle.addEventListener('click', (event) => {
            event.preventDefault()
            event.stopPropagation()
            const collapsed = sidebar.classList.toggle('collapsed')
            document.documentElement.classList.toggle('sidebar-collapsed', collapsed)
            this.saveCollapsedState(collapsed)
        })
    }

    saveCollapsedState(collapsed) {
        try {
            localStorage.setItem(SIDEBAR_COLLAPSED_STORAGE_KEY, collapsed ? '1' : '0')
        } catch {
            return
        }
    }

    activateMenu() {
        let bestMatch = null
        const menuLinks = document.querySelectorAll('#sidebar a.sidebar-link')
        const pathname = window.location.pathname

        for (let i = 0; i < menuLinks.length; ++i) {
            const href = menuLinks[i].getAttribute('href')
            if (
                href &&
                href !== '#' &&
                pathname.startsWith(href) &&
                (bestMatch === null || href.length > bestMatch.getAttribute('href').length)
            ) {
                bestMatch = menuLinks[i]
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
            el = el.parentElement?.closest('.sidebar-item')
        }
    }
}
