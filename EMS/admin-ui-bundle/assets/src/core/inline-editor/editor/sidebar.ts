export class SidebarResizer {
    private isResizing = false
    private animationFrame: number | null = null
    private overlay: HTMLDivElement | null = null

    constructor(
        private container: HTMLElement,
        private handle: HTMLElement,
        private minWidth = 200
    ) {
        this.init()
    }

    private init() {
        const savedWidth = localStorage.getItem('sidebar-width')
        if (savedWidth) {
            this.applyWidth(parseInt(savedWidth))
        }

        this.handle.addEventListener('pointerdown', this.onPointerDown)
        document.addEventListener('pointerup', this.onPointerUp)
        document.addEventListener('pointermove', this.onPointerMove)
    }

    private onPointerDown = (e: PointerEvent) => {
        this.isResizing = true
        this.container.style.transition = 'none'
        document.body.style.cursor = 'col-resize'
        this.createOverlay()
        e.preventDefault()
    }

    private onPointerUp = () => {
        if (!this.isResizing) return
        this.isResizing = false
        this.container.style.transition = ''
        document.body.style.cursor = 'default'
        this.removeOverlay()
    }

    private onPointerMove = (e: PointerEvent) => {
        if (!this.isResizing) return

        const rect = this.container.getBoundingClientRect()
        const width = rect.right - e.clientX
        const constrainedWidth = Math.max(this.minWidth, Math.min(rect.width - 100, width))

        if (this.animationFrame) cancelAnimationFrame(this.animationFrame)

        this.animationFrame = requestAnimationFrame(() => {
            this.applyWidth(constrainedWidth)
            localStorage.setItem('sidebar-width', constrainedWidth.toString())
        })
    }

    private applyWidth(width: number) {
        this.container.style.setProperty('--sidebar-resizer-width', `${width}px`)
    }

    private createOverlay() {
        this.overlay = document.createElement('div')
        Object.assign(this.overlay.style, {
            position: 'fixed',
            top: '0',
            left: '0',
            width: '100%',
            height: '100%',
            cursor: 'col-resize',
            zIndex: '9999'
        })
        document.body.appendChild(this.overlay)
    }

    private removeOverlay() {
        this.overlay?.remove()
        this.overlay = null
    }
}
