'use strict'

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

        toggle.addEventListener('click', (event) => {
            event.preventDefault()
            sidebar.classList.toggle('collapsed')
        })
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
