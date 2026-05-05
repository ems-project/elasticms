import '../../../../css/core/components/tiptap/_content_menu.scss'

import type { TiptapEditor } from './editor.ts'
import type { ContextMenuItem } from './types.ts'

const CONTEXT_NODES: Record<string, string[]> = {
    table: ['table', 'tableFigure', 'tableCaption'],
    list: ['bulletList', 'orderedList', 'listItem'],
    link: ['link'],
    image: ['image']
}

export class ContextMenu {
    private el: HTMLElement | null = null
    private readonly editor: TiptapEditor
    private static active: ContextMenu | null = null

    constructor(editor: TiptapEditor) {
        this.editor = editor
        this.editor.tiptap.view.dom.addEventListener('contextmenu', this.onMenu)
    }

    private get editorDoc(): Document {
        return this.editor.tiptap.view.dom.ownerDocument
    }

    private onClickOutside = (e: MouseEvent) => {
        if (this.el && !this.el.contains(e.target as Node)) this.close()
    }
    private onKeyDown = (e: KeyboardEvent) => {
        if (e.key === 'Escape') this.close()
    }
    private onMenu = (e: MouseEvent) => {
        if (e.ctrlKey) return

        const items = this.getItems()
        if (items.length === 0) return

        e.preventDefault()
        this.open(e, items)
    }

    private open(e: MouseEvent, items: ContextMenuItem[]) {
        ContextMenu.active?.close()
        ContextMenu.active = this

        this.el = this.render(items)
        this.position(e)

        this.getAllDocuments().forEach((doc) => {
            doc.addEventListener('mousedown', this.onClickOutside)
            doc.addEventListener('keydown', this.onKeyDown)
        })
    }

    close() {
        ContextMenu.active = null

        if (!this.el) return

        this.el.remove()
        this.el = null
        this.getAllDocuments().forEach((doc) => {
            doc.removeEventListener('mousedown', this.onClickOutside)
            doc.removeEventListener('keydown', this.onKeyDown)
        })
    }

    private getAllDocuments(): Document[] {
        const docs = [document, this.editorDoc]
        document.querySelectorAll('iframe').forEach((frame) => {
            if (frame.contentDocument && frame.contentDocument !== this.editorDoc) {
                docs.push(frame.contentDocument)
            }
        })
        return docs
    }

    private isContextActive(context: string): boolean {
        const nodes = CONTEXT_NODES[context]
        return nodes
            ? nodes.some((n) => this.editor.tiptap.isActive(n))
            : this.editor.tiptap.isActive(context)
    }

    private getItems(): ContextMenuItem[] {
        return this.editor.modules
            .filter((m) => m.contextMenuNode && this.isContextActive(m.contextMenuNode))
            .flatMap((m) => m.contextMenu ?? [])
            .sort((a, b) => (a.order ?? 0) - (b.order ?? 0))
    }

    private render(items: ContextMenuItem[]): HTMLElement {
        const menu = document.createElement('div')
        menu.className = 'tiptap-context-menu'

        const topLevel: ContextMenuItem[] = []
        const grouped = new Map<string, ContextMenuItem[]>()

        for (const item of items) {
            if (item.parent) {
                if (!grouped.has(item.parent)) grouped.set(item.parent, [])
                grouped.get(item.parent)!.push(item)
            } else {
                topLevel.push(item)
            }
        }

        for (const [label, children] of grouped) {
            const icon = children.find((c) => c.parentIcon)?.parentIcon
            menu.appendChild(this.renderSubmenu(label, children, icon))
        }

        for (const item of topLevel) {
            menu.appendChild(this.renderItem(item))
        }

        document.body.appendChild(menu)
        return menu
    }

    private renderItem(item: ContextMenuItem): HTMLElement {
        const btn = document.createElement('button')
        btn.type = 'button'
        btn.className = 'tiptap-context-menu-item'

        const isDisabled = item.disabled?.(this.editor) ?? false
        if (isDisabled) {
            btn.classList.add('is-disabled')
            btn.disabled = true
        }

        if (item.icon) {
            const icon = document.createElement('span')
            icon.className = 'tiptap-context-menu-icon'
            icon.innerHTML = item.icon
            btn.appendChild(icon)
        }

        const label = document.createElement('span')
        label.textContent = item.label
        btn.appendChild(label)

        btn.addEventListener('click', (e) => {
            e.preventDefault()
            e.stopPropagation()
            this.close()
            item.command(this.editor)
        })

        return btn
    }

    private renderSubmenu(label: string, children: ContextMenuItem[], icon?: string): HTMLElement {
        const wrapper = document.createElement('div')
        wrapper.className = 'tiptap-context-menu-submenu'

        const trigger = document.createElement('button')
        trigger.type = 'button'
        trigger.className = 'tiptap-context-menu-item has-submenu'

        if (icon) {
            const iconEl = document.createElement('span')
            iconEl.className = 'tiptap-context-menu-icon'
            iconEl.innerHTML = icon
            trigger.appendChild(iconEl)
        }

        const text = document.createElement('span')
        text.textContent = label
        trigger.appendChild(text)

        const arrow = document.createElement('span')
        arrow.className = 'tiptap-context-menu-arrow'
        arrow.textContent = '\u25B6'
        trigger.appendChild(arrow)

        const panel = document.createElement('div')
        panel.className = 'tiptap-context-menu-panel'
        for (const child of children) {
            panel.appendChild(this.renderItem(child))
        }

        wrapper.appendChild(trigger)
        wrapper.appendChild(panel)
        return wrapper
    }

    private getFrameOffset(): { x: number; y: number } {
        const doc = this.editorDoc
        if (doc === document) return { x: 0, y: 0 }
        const frame = [...document.querySelectorAll('iframe')].find(
            (f) => f.contentDocument === doc
        )
        if (!frame) return { x: 0, y: 0 }
        const rect = frame.getBoundingClientRect()
        return { x: rect.left, y: rect.top }
    }

    private position(e: MouseEvent) {
        if (!this.el) return
        const offset = this.getFrameOffset()
        const x = e.clientX + offset.x + window.scrollX
        const y = e.clientY + offset.y + window.scrollY
        const rect = this.el.getBoundingClientRect()
        this.el.style.left =
            (x + rect.width > window.innerWidth + window.scrollX ? x - rect.width : x) + 'px'
        this.el.style.top =
            (y + rect.height > window.innerHeight + window.scrollY ? y - rect.height : y) + 'px'
    }

    destroy() {
        this.close()
        this.editor.tiptap.view.dom.removeEventListener('contextmenu', this.onMenu)
    }
}
