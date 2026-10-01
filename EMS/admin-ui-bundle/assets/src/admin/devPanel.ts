'use strict'

import Theme, { ThemeMode } from './theme.ts'
import Sidebar, { SIDEBAR_COLLAPSED_CHANGE_EVENT } from './sidebar.ts'

const STORAGE = {
    mode: 'ems.dev.mode',
    color: 'ems.dev.themeColor',
    collapsed: 'ems.dev.sidebarCollapsed',
    badge: 'ems.dev.badge'
}

const read = (key: string): string | null => {
    try {
        return localStorage.getItem(key)
    } catch {
        return null
    }
}

const write = (key: string, value: string) => {
    try {
        localStorage.setItem(key, value)
    } catch {
        return
    }
}

const clear = () => {
    try {
        Object.values(STORAGE).forEach((key) => localStorage.removeItem(key))
    } catch {
        return
    }
}

export default class DevPanel {
    constructor(
        private theme: Theme,
        private sidebarComponent: Sidebar
    ) {
        this.applyOverrides()
        this.initModeButtons()
        this.initColorSwatches()
        this.initCollapseCheckbox()
        this.initBadgeToggle()
        this.initResetButton()
    }

    applyOverrides() {
        const mode = read(STORAGE.mode)
        if (mode === 'light' || mode === 'dark') {
            this.theme.setMode(mode)
        }

        const color = read(STORAGE.color)
        if (color) {
            this.theme.setColor(color)
        }

        const collapsed = read(STORAGE.collapsed)
        if (collapsed !== null) {
            this.sidebarComponent.sidebar?.classList.toggle('collapsed', collapsed === '1')
            this.sidebarComponent.setCollapsed(collapsed === '1')
        }
    }

    initModeButtons() {
        const buttons = document.querySelectorAll<HTMLButtonElement>('.mode-btn')
        this.updateModeButtons(this.theme.mode)

        buttons.forEach((button) => {
            button.addEventListener('click', () => {
                const mode = button.dataset.mode
                if (mode === 'light' || mode === 'dark') {
                    this.theme.setMode(mode)
                    write(STORAGE.mode, mode)
                    this.updateModeButtons(mode)
                }
            })
        })
    }

    updateModeButtons(mode: ThemeMode) {
        document.querySelectorAll<HTMLButtonElement>('.mode-btn').forEach((button) => {
            button.classList.toggle('active', button.dataset.mode === mode)
        })
    }

    initColorSwatches() {
        const swatches = document.querySelectorAll<HTMLButtonElement>('.skin-swatch')
        const current = this.theme.color
        if (current) {
            this.updateColorSwatches(current)
        }

        swatches.forEach((swatch) => {
            swatch.addEventListener('click', () => {
                const color = swatch.dataset.skin
                if (color) {
                    this.theme.setColor(color)
                    write(STORAGE.color, color)
                    this.updateColorSwatches(color)
                }
                swatch.blur()
            })
        })
    }

    updateColorSwatches(color: string) {
        document.querySelectorAll<HTMLButtonElement>('.skin-swatch').forEach((swatch) => {
            swatch.classList.toggle('active', swatch.dataset.skin === color)
        })
    }

    initCollapseCheckbox() {
        const checkbox = document.getElementById('devSidebarCollapsed')
        const sidebar = this.sidebarComponent.sidebar
        if (!(checkbox instanceof HTMLInputElement) || !sidebar) {
            return
        }
        checkbox.checked = sidebar.classList.contains('collapsed')

        checkbox.addEventListener('change', () => {
            sidebar.classList.toggle('collapsed', checkbox.checked)
            this.sidebarComponent.setCollapsed(checkbox.checked)
            write(STORAGE.collapsed, checkbox.checked ? '1' : '0')
        })

        document.addEventListener(SIDEBAR_COLLAPSED_CHANGE_EVENT, (event) => {
            checkbox.checked = (event as CustomEvent<{ collapsed: boolean }>).detail.collapsed
        })
    }

    initBadgeToggle() {
        const checkbox = document.getElementById('devShowBadge')
        const badge = document.getElementById('devBadge')
        if (!(checkbox instanceof HTMLInputElement) || !badge) {
            return
        }

        const enabled = read(STORAGE.badge) === '1'
        checkbox.checked = enabled
        badge.style.display = enabled ? '' : 'none'

        checkbox.addEventListener('change', () => {
            badge.style.display = checkbox.checked ? '' : 'none'
            write(STORAGE.badge, checkbox.checked ? '1' : '0')
        })
    }

    initResetButton() {
        document.getElementById('devReset')?.addEventListener('click', () => {
            clear()
            window.location.reload()
        })
    }
}
