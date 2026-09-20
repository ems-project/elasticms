'use strict'

import Theme, { ThemeMode } from './theme.ts'
import Sidebar, { SIDEBAR_COLLAPSED_CHANGE_EVENT } from './sidebar.ts'

const DEV_BADGE_STORAGE_KEY = 'ems.dev.badge'

export default class DevPanel {
    constructor(private theme: Theme, private sidebarComponent: Sidebar) {
        this.initModeButtons()
        this.initColorSwatches()
        this.initCollapseCheckbox()
        this.initBadgeToggle()
    }

    initModeButtons() {
        const buttons = document.querySelectorAll<HTMLButtonElement>('.mode-btn')
        if (buttons.length === 0) {
            return
        }
        const effective = (document.documentElement.getAttribute('data-bs-theme') as ThemeMode | null) ?? 'light'
        this.updateModeButtons(effective)

        buttons.forEach((button) => {
            button.addEventListener('click', () => {
                const mode = button.dataset.mode
                if (mode === 'light' || mode === 'dark') {
                    this.theme.setMode(mode)
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
        if (swatches.length === 0) {
            return
        }
        const current = this.theme.readColorOverride()
        if (current) {
            this.updateColorSwatches(current)
        }

        swatches.forEach((swatch) => {
            swatch.addEventListener('click', () => {
                const color = swatch.dataset.skin
                if (color) {
                    this.theme.setColorOverride(color)
                    this.updateColorSwatches(color)
                }
                // Avoid the browser's default focus ring lingering on the
                // clicked swatch, which can look like a second "active" mark.
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

        const enabled = this.readBadgeEnabled()
        checkbox.checked = enabled
        badge.style.display = enabled ? '' : 'none'

        checkbox.addEventListener('change', () => {
            badge.style.display = checkbox.checked ? '' : 'none'
            try {
                localStorage.setItem(DEV_BADGE_STORAGE_KEY, checkbox.checked ? '1' : '0')
            } catch {
                return
            }
        })
    }

    readBadgeEnabled(): boolean {
        try {
            return localStorage.getItem(DEV_BADGE_STORAGE_KEY) === '1'
        } catch {
            return false
        }
    }
}
