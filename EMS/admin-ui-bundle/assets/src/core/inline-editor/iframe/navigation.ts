interface NavigationOptions {
  onUpdate: (url: string) => void
  onLeave: () => void
}

export class NavigationObserver {
  private lastUrl = window.location.href

  constructor(private options: NavigationOptions) {
    this.init()
  }

  private init(): void {
    this.patchHistory()

    // Browser events
    window.addEventListener('popstate', this.notify)
    window.addEventListener('hashchange', this.notify)
    window.addEventListener('beforeunload', () => this.options.onLeave())

    document.body.addEventListener('click', this.handleLinkClick)
  }

  private notify = (): void => {
    const url = window.location.href
    if (url === this.lastUrl) return
    this.lastUrl = url
    this.options.onUpdate(url)
  }

  private patchHistory(): void {
    ['pushState', 'replaceState'].forEach((method) => {
      const m = method as 'pushState' | 'replaceState'
      const original = history[m]
      history[m] = (...args) => {
        this.options.onLeave()
        original.apply(history, args)
        this.notify()
      }
    })
  }

  private handleLinkClick = (e: MouseEvent): void => {
    const link = (e.target as HTMLElement).closest('a')
    if (!link) return

    const href = link.getAttribute('href')
    if (!href || href.startsWith('#')) return

    const url = new URL(href, window.location.href)

    if (url.origin !== window.location.origin && link.getAttribute('target') !== '_blank') {
      e.preventDefault()
      console.warn(`Blocked external link: ${url.href}`)
    }
  }
}
