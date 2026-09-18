'use strict'

const THEME_MODE_STORAGE_KEY = 'ems.theme.mode'
const THEME_COLOR_STORAGE_KEY = 'ems.dev.themeColor'

type ThemeMode = 'light' | 'dark'

export default class Theme {
    constructor() {
        this.initMode()
        this.initColorOverride()
        this.initModeButtons()
        this.initColorSwatches()
    }

    initMode() {
        const stored = this.readMode()
        const effective: ThemeMode = stored ?? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')
        document.documentElement.setAttribute('data-bs-theme', effective)
    }

    readMode(): ThemeMode | null {
        try {
            const value = localStorage.getItem(THEME_MODE_STORAGE_KEY)
            return value === 'light' || value === 'dark' ? value : null
        } catch {
            return null
        }
    }

    setMode(mode: ThemeMode) {
        document.documentElement.setAttribute('data-bs-theme', mode)
        try {
            localStorage.setItem(THEME_MODE_STORAGE_KEY, mode)
        } catch {
            return
        }
        this.updateModeButtons(mode)
    }

    initModeButtons() {
        const buttons = document.querySelectorAll<HTMLButtonElement>('.mode-btn')
        if (buttons.length === 0) {
            return
        }
        // initMode() runs first and always sets this attribute.
        const effective = (document.documentElement.getAttribute('data-bs-theme') as ThemeMode | null) ?? 'light'
        this.updateModeButtons(effective)

        buttons.forEach((button) => {
            button.addEventListener('click', () => {
                const mode = button.dataset.mode
                if (mode === 'light' || mode === 'dark') {
                    this.setMode(mode)
                }
            })
        })
    }

    updateModeButtons(mode: ThemeMode) {
        document.querySelectorAll<HTMLButtonElement>('.mode-btn').forEach((button) => {
            button.classList.toggle('active', button.dataset.mode === mode)
        })
    }

    // The server always renders `data-theme="{{ theme_color }}"` on <html>
    // (see base/html5.html.twig + css/admin/components/_theme-color.scss).
    // Only override it here when a super-admin picked a preview color.
    initColorOverride() {
        const color = this.readColorOverride()
        if (color) {
            document.documentElement.setAttribute('data-theme', color)
        }
    }

    readColorOverride(): string | null {
        try {
            return localStorage.getItem(THEME_COLOR_STORAGE_KEY)
        } catch {
            return null
        }
    }

    setColorOverride(color: string) {
        document.documentElement.setAttribute('data-theme', color)
        try {
            localStorage.setItem(THEME_COLOR_STORAGE_KEY, color)
        } catch {
            return
        }
        this.updateColorSwatches(color)
    }

    initColorSwatches() {
        const swatches = document.querySelectorAll<HTMLButtonElement>('.skin-swatch')
        if (swatches.length === 0) {
            return
        }
        const current = this.readColorOverride()
        if (current) {
            this.updateColorSwatches(current)
        }

        swatches.forEach((swatch) => {
            swatch.addEventListener('click', () => {
                const color = swatch.dataset.skin
                if (color) {
                    this.setColorOverride(color)
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
}
