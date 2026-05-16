import '../../../../../css/core/components/tiptap/_content_menu.scss'

import type { TiptapEditor } from './../editor.ts'
import { CellSelection } from '@tiptap/pm/tables'
import type { ContextMenuItem, TiptapModule } from './../types.ts'

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
    private contextTarget: Element | null = null

    constructor(editor: TiptapEditor) {
        this.editor = editor
        this.editor.tiptap.view.dom.addEventListener('mousedown', this.onMouseDown)
        this.editor.tiptap.view.dom.addEventListener('contextmenu', this.onMenu)
    }

    private onClickOutside = (e: MouseEvent) => {
        if (this.el && !this.el.contains(e.target as Node)) this.close()
    }
    private onKeyDown = (e: KeyboardEvent) => {
        if (e.key === 'Escape') this.close()
    }

    private onMenu = (e: MouseEvent) => {
        if (e.ctrlKey) return
        this.contextTarget = e.target as Element | null
        const items = this.getItems(e)
        if (items.length === 0) return
        e.preventDefault()
        this.open(e, items)
    }

    private onMouseDown = (e: MouseEvent) => {
        if (e.button === 2 && this.editor.tiptap.state.selection instanceof CellSelection) {
            e.preventDefault()
        }
    }

    private open(e: MouseEvent, items: ContextMenuItem[]) {
        ContextMenu.active?.close()
        ContextMenu.active = this

        this.el = this.render(items)
        this.position(e)
        this.adjustSubmenus()

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
        const top = this.editor.docParent
        const docs = [top, this.editor.docEditor]
        top.querySelectorAll('iframe').forEach((frame) => {
            if (frame.contentDocument && frame.contentDocument !== this.editor.docEditor) {
                docs.push(frame.contentDocument)
            }
        })
        return docs
    }

    private isContextActive(module: TiptapModule, e: MouseEvent): boolean {
        if (module.contextMenuSelector) {
            const target = e.target as HTMLElement | null
            if (target?.closest(module.contextMenuSelector)) return true
        }

        if (!module.contextMenuNode) return false

        const nodes = CONTEXT_NODES[module.contextMenuNode]
        return nodes
            ? nodes.some((n) => this.editor.tiptap.isActive(n))
            : this.editor.tiptap.isActive(module.contextMenuNode)
    }

    private getItems(e: MouseEvent): ContextMenuItem[] {
        return this.editor.modules
            .filter(
                (m) => (m.contextMenuNode || m.contextMenuSelector) && this.isContextActive(m, e)
            )
            .flatMap((m) => m.contextMenu ?? [])
            .sort((a, b) => (a.order ?? 0) - (b.order ?? 0))
    }

    private render(items: ContextMenuItem[]): HTMLElement {
        const doc = this.editor.docParent
        const menu = doc.createElement('div')
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
            const allDisabled = children.every((c) => c.disabled?.(this.editor) ?? false)
            if (allDisabled) continue
            const icon = children.find((c) => c.parentIcon)?.parentIcon
            menu.appendChild(this.renderSubmenu(label, children, icon))
        }

        for (const item of topLevel) {
            menu.appendChild(this.renderItem(item))
        }

        menu.addEventListener('contextmenu', (e) => {
            if (!e.ctrlKey) e.preventDefault()
        })

        doc.body.appendChild(menu)
        return menu
    }

    private renderItem(item: ContextMenuItem): HTMLElement {
        const doc = this.editor.docParent
        const btn = doc.createElement('button')
        btn.type = 'button'
        btn.className = 'tiptap-context-menu-item'

        const isDisabled = item.disabled?.(this.editor) ?? false
        if (isDisabled) {
            btn.classList.add('is-disabled')
            btn.disabled = true
        }

        if (item.icon) {
            const icon = doc.createElement('span')
            icon.className = 'tiptap-context-menu-icon'
            icon.innerHTML = item.icon
            btn.appendChild(icon)
        }

        const label = doc.createElement('span')
        label.textContent = item.label
        btn.appendChild(label)

        btn.addEventListener('click', (e) => {
            e.preventDefault()
            e.stopPropagation()
            const target = this.contextTarget
            this.close()
            item.command(this.editor, { target })
        })

        return btn
    }
    private renderSubmenu(label: string, children: ContextMenuItem[], icon?: string): HTMLElement {
        const doc = this.editor.docParent
        const wrapper = doc.createElement('div')
        wrapper.className = 'tiptap-context-menu-submenu'

        const trigger = doc.createElement('button')
        trigger.type = 'button'
        trigger.className = 'tiptap-context-menu-item has-submenu'

        if (icon) {
            const iconEl = doc.createElement('span')
            iconEl.className = 'tiptap-context-menu-icon'
            iconEl.innerHTML = icon
            trigger.appendChild(iconEl)
        }

        const text = doc.createElement('span')
        text.textContent = label
        trigger.appendChild(text)

        const arrow = doc.createElement('span')
        arrow.className = 'tiptap-context-menu-arrow'
        arrow.textContent = '\u25B6'
        trigger.appendChild(arrow)

        const panel = doc.createElement('div')
        panel.className = 'tiptap-context-menu-panel'
        for (const child of children) {
            panel.appendChild(this.renderItem(child))
        }

        wrapper.appendChild(trigger)
        wrapper.appendChild(panel)
        return wrapper
    }

    private getFrameOffset(): { x: number; y: number } {
        const doc = this.editor.docEditor
        if (doc === document) return { x: 0, y: 0 }
        const frame = [...this.editor.docParent.querySelectorAll('iframe')].find(
            (f) => f.contentDocument === doc
        )
        if (!frame) return { x: 0, y: 0 }
        const rect = frame.getBoundingClientRect()
        return { x: rect.left, y: rect.top }
    }

    private position(e: MouseEvent) {
        if (!this.el) return
        const win = this.editor.docParent.defaultView ?? window
        const offset = this.getFrameOffset()
        const x = e.clientX + offset.x + win.scrollX
        const y = e.clientY + offset.y + win.scrollY
        const rect = this.el.getBoundingClientRect()
        this.el.style.left =
            (x + rect.width > win.innerWidth + win.scrollX ? x - rect.width : x) + 'px'
        this.el.style.top =
            (y + rect.height > win.innerHeight + win.scrollY ? y - rect.height : y) + 'px'
    }

    private adjustSubmenus() {
        if (!this.el) return
        const win = this.editor.docParent.defaultView ?? window

        this.el.querySelectorAll<HTMLElement>('.tiptap-context-menu-submenu').forEach((wrapper) => {
            wrapper.addEventListener('mouseenter', () => {
                const panel = wrapper.querySelector<HTMLElement>('.tiptap-context-menu-panel')
                if (!panel) return

                panel.classList.remove('flip-x', 'flip-y')

                const rect = wrapper.getBoundingClientRect()
                const panelRect = panel.getBoundingClientRect()

                if (rect.right + panelRect.width > win.innerWidth) {
                    panel.classList.add('flip-x')
                }
                if (rect.top + panelRect.height > win.innerHeight) {
                    panel.classList.add('flip-y')
                }
            })
        })
    }

    destroy() {
        this.close()
        this.editor.tiptap.view.dom.removeEventListener('mousedown', this.onMouseDown)
        this.editor.tiptap.view.dom.removeEventListener('contextmenu', this.onMenu)
    }
}
