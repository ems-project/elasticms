import { TiptapEditor } from '../editor.ts'

export type IframeDropdownConfig = {
    prefix: string
    css: string
    contentCss: string | null
    buttonLabel: string
    buildBody(): string
    onItemClick(name: string): void
    onOpen(iframeDoc: Document): void
}

export type IframeDropdown = {
    element: HTMLElement
    hide(): void
    destroy(): void
    setLabel(text: string): void
}

export function createIframeDropdown(
    editor: TiptapEditor,
    config: IframeDropdownConfig
): IframeDropdown {
    const doc = editor.docParent
    let panel: HTMLDivElement | null = null
    let onOpenReady: (() => void) | null = null

    const wrapper = doc.createElement('div')
    wrapper.className = `tiptap-dropdown tiptap-dropdown--${config.prefix}`

    const button = doc.createElement('button')
    button.type = 'button'
    button.dataset.action = config.buttonLabel
    button.className = 'tiptap-dropdown-btn'

    const label = doc.createElement('span')
    label.className = 'tiptap-dropdown-label'
    label.textContent = config.buttonLabel

    const arrow = doc.createElement('span')
    arrow.textContent = '▾'

    button.appendChild(label)
    button.appendChild(arrow)

    const hide = () => {
        if (panel) panel.hidden = true
    }

    const positionPanel = () => {
        if (!panel) return
        const rect = button.getBoundingClientRect()
        panel.style.top = `${rect.bottom}px`
        panel.style.left = `${rect.left}px`
    }

    const initPanel = (onReady: () => void) => {
        if (panel) {
            onReady()
            return
        }

        panel = doc.createElement('div')
        panel.className = `tiptap-dropdown-panel tiptap-dropdown-panel--${config.prefix}`
        doc.body.appendChild(panel)

        const iframe = doc.createElement('iframe')
        iframe.className = 'tiptap-dropdown-iframe'

        iframe.addEventListener(
            'load',
            () => {
                const iframeDoc = iframe.contentDocument
                if (!iframeDoc) return

                if (config.contentCss) {
                    const link = iframeDoc.createElement('link')
                    link.rel = 'stylesheet'
                    link.href = config.contentCss
                    iframeDoc.head.appendChild(link)
                }

                const s = iframeDoc.createElement('style')
                s.textContent = config.css
                iframeDoc.head.appendChild(s)

                iframeDoc.body.innerHTML = config.buildBody()

                iframeDoc.addEventListener('mousedown', (e) => {
                    e.preventDefault()
                    const li = (e.target as HTMLElement).closest('li')
                    if (!li?.dataset.name) return
                    config.onItemClick(li.dataset.name)
                    hide()
                })

                iframeDoc.addEventListener('click', (e) => {
                    if (!(e.target as HTMLElement).closest('li')) hide()
                })

                onOpenReady = () => config.onOpen(iframeDoc)
                onReady()
            },
            { once: true }
        )

        panel.appendChild(iframe)
    }

    const handleOutsideClick = (e: MouseEvent) => {
        if (panel && !panel.contains(e.target as Node) && !button.contains(e.target as Node)) hide()
    }

    button.addEventListener('click', (e) => {
        e.stopPropagation()
        if (panel && !panel.hidden) {
            hide()
            return
        }
        initPanel(() => {
            panel!.hidden = false
            window.focus()
            positionPanel()
            onOpenReady?.()
        })
    })

    doc.addEventListener('mousedown', handleOutsideClick)
    window.addEventListener('blur', hide)
    window.addEventListener('resize', hide)
    window.addEventListener('scroll', hide, true)

    wrapper.appendChild(button)

    return {
        element: wrapper,
        hide,
        setLabel(text: string) {
            label.textContent = text
            button.title = text !== config.buttonLabel ? text : ''
        },
        destroy() {
            panel?.remove()
            doc.removeEventListener('mousedown', handleOutsideClick)
            window.removeEventListener('blur', hide)
            window.removeEventListener('resize', hide)
            window.removeEventListener('scroll', hide, true)
        }
    }
}
