export default class DebugToolbar {
    private readonly root = document.documentElement
    private readonly resizeObserver = new ResizeObserver(([entry]) => {
        this.setOffset(entry.target.getBoundingClientRect().height)
    })

    constructor() {
        if (this.attach()) return

        const mutationObserver = new MutationObserver(() => {
            if (this.attach()) mutationObserver.disconnect()
        })
        mutationObserver.observe(document.body, { childList: true, subtree: true })
    }

    private attach(): boolean {
        const toolbar = document.querySelector<HTMLElement>('.sf-toolbar')
        if (!toolbar) return false

        this.resizeObserver.observe(toolbar)
        this.setOffset(toolbar.getBoundingClientRect().height)
        return true
    }

    private setOffset(height: number): void {
        this.root.style.setProperty('--app-bottom-offset', `${height}px`)
    }
}