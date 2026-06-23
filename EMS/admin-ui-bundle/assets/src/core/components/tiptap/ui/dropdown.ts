import { TiptapEditor } from '../editor.ts'

export type DropdownConfig = {
    prefix: string
    action: string
    buttonLabel?: string
    buttonTooltip: string
    icon?: string
    buildBody(): string
    onItemClick(name: string): void
    onOpen(root: HTMLElement): void
} & (
    | { iframe: true; css: string; contentCss?: string | null }
    | { iframe?: false; css?: never; contentCss?: never }
)

export type Dropdown = {
    element: HTMLElement
    hide(): void
    show(): void
    destroy(): void
    focus(): void
    setLabel(text: string): void
}

export function createDropdown(editor: TiptapEditor, config: DropdownConfig): Dropdown {
    const doc = editor.docParent
    const useIframe = config.iframe ?? false
    let panel: HTMLDivElement | null = null
    let onOpenReady: (() => void) | null = null

    const wrapper = doc.createElement('div')
    wrapper.className = `tiptap-toolbar-dropdown tiptap-toolbar-dropdown--${config.prefix}${useIframe ? ' tiptap-toolbar-dropdown-iframe' : ''}`

    const button = doc.createElement('button')
    button.type = 'button'
    button.dataset.action = config.action
    button.title = config.buttonTooltip
    button.className = 'tiptap-toolbar-dropdown-btn'

    const label = doc.createElement('span')

    if (config.icon) {
        button.innerHTML = config.icon
        const svg = button.querySelector('svg')
        if (svg) {
            svg.setAttribute('width', '16')
            svg.setAttribute('height', '16')
        }
    } else if (config.buttonLabel) {
        label.className = 'tiptap-toolbar-dropdown-label'
        label.textContent = config.buttonLabel

        const arrow = doc.createElement('span')
        arrow.textContent = '▾'
        button.appendChild(label)
        button.appendChild(arrow)
    }

    const hide = () => {
        if (panel) panel.hidden = true
    }

    const positionPanel = () => {
        if (!panel) return
        const rect = button.getBoundingClientRect()
        panel.style.top = `${rect.bottom}px`
        panel.style.left = `${rect.left}px`
    }

    const bindItemEvents = (root: HTMLElement) => {
        root.addEventListener('mousedown', (e) => {
            const target = e.target as HTMLElement
            const item = target.closest<HTMLElement>('[data-name]')
            if (!item?.dataset.name) return
            e.preventDefault()
            config.onItemClick(item.dataset.name)
            hide()
        })
    }

    const initPanel = (onReady: () => void) => {
        if (panel) {
            onReady()
            return
        }

        panel = doc.createElement('div')
        panel.className = `tiptap-dropdown tiptap-dropdown--${config.prefix}`
        doc.body.appendChild(panel)

        if (useIframe) {
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

                    if (config.css) {
                        const style = iframeDoc.createElement('style')
                        style.textContent = config.css
                        iframeDoc.head.appendChild(style)
                    }

                    iframeDoc.body.innerHTML = config.buildBody()
                    bindItemEvents(iframeDoc.body)
                    onOpenReady = () => config.onOpen(iframeDoc.body)
                    onReady()
                },
                { once: true }
            )

            panel.appendChild(iframe)
        } else {
            const content = doc.createElement('div')
            content.className = 'tiptap-dropdown-content'
            content.innerHTML = config.buildBody()
            panel.appendChild(content)
            bindItemEvents(content)
            onOpenReady = () => config.onOpen(content)
            onReady()
        }
    }

    const handleOutsideClick = (e: MouseEvent) => {
        const target = e.target as HTMLElement
        if (!panel || panel.hidden) return
        if (panel.contains(target) || button.contains(target)) return
        hide()
    }

    const onBlur = () => {
        if (panel && !panel.hidden) hide()
    }

    const onEscape = (e: KeyboardEvent) => {
        if (e.key === 'Escape') {
            hide()
        }
    }

    doc.addEventListener('mousedown', handleOutsideClick)
    editor.docEditor.addEventListener('mousedown', handleOutsideClick)
    doc.addEventListener('keydown', onEscape)
    window.addEventListener('blur', onBlur)
    window.addEventListener('resize', hide)
    window.addEventListener('scroll', hide, true)

    const open = () => {
        initPanel(() => {
            panel!.hidden = false
            window.focus()
            positionPanel()
            onOpenReady?.()
        })
    }

    button.addEventListener('click', (e) => {
        e.stopPropagation()
        if (panel && !panel.hidden) {
            hide()
            return
        }
        open()
    })
    wrapper.appendChild(button)

    return {
        element: wrapper,
        hide,
        show: open,
        setLabel(text: string) {
            label.textContent = text
            button.title = text !== config.buttonLabel ? text : ''
        },
        focus() {
            window.focus()
            button.focus()
        },
        destroy() {
            panel?.remove()
            doc.removeEventListener('mousedown', handleOutsideClick)
            editor.docEditor.removeEventListener('mousedown', handleOutsideClick)
            doc.removeEventListener('keydown', onEscape)
            window.removeEventListener('resize', hide)
            window.removeEventListener('scroll', hide, true)
            window.removeEventListener('blur', onBlur)
        }
    }
}
