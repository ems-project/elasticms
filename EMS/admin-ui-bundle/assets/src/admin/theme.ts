'use strict'

export type ThemeMode = 'light' | 'dark'

export default class Theme {
    get mode(): ThemeMode {
        return document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'dark' : 'light'
    }

    get color(): string | null {
        return document.documentElement.getAttribute('data-theme')
    }

    setMode(mode: ThemeMode) {
        document.documentElement.setAttribute('data-bs-theme', mode)
    }

    setColor(color: string) {
        document.documentElement.setAttribute('data-theme', color)
    }
}
