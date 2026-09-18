'use strict'

const THEME_MODE_STORAGE_KEY = 'ems.theme.mode'
const THEME_COLOR_STORAGE_KEY = 'ems.dev.themeColor'

type ThemeMode = 'light' | 'dark'

export default class Theme {
    constructor() {
        this.initMode()
        this.initColorOverride()
        this.initModeToggle()
        this.initColorSelect()
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
    }

    initModeToggle() {
        const select = document.getElementById('devThemeMode')
        if (!(select instanceof HTMLSelectElement)) {
            return
        }
        select.value = this.readMode() ?? 'light'
        select.addEventListener('change', () => {
            if (select.value === 'light' || select.value === 'dark') {
                this.setMode(select.value)
            }
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
    }

    initColorSelect() {
        const select = document.getElementById('devThemeColor')
        if (!(select instanceof HTMLSelectElement)) {
            return
        }
        const current = this.readColorOverride()
        if (current) {
            select.value = current
        }
        select.addEventListener('change', () => {
            this.setColorOverride(select.value)
        })
    }
}
