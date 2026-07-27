import '../../../../../css/core/components/tiptap/_context_menu.scss'

import type { TiptapEditor } from './../Editor.ts'
import { CellSelection } from '@tiptap/pm/tables'
import type { ContextMenuItem, TiptapModule } from './../Types.ts'
import { TranslationKey } from '../Translations.ts'

const CONTEXT_NODES: Record<string, string[]> = {
    table: ['table', 'tableFigure', 'tableCaption'],
    list: ['bulletList', 'orderedList', 'listItem'],
    link: ['link'],
    image: ['image', 'imageBlock', 'imageFigure', 'imageCaption']
}

type SeparatorItem = { separator: true }

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

    private open(e: MouseEvent, items: (ContextMenuItem | SeparatorItem)[]) {
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
        if (module.contextMenu?.selector) {
            const target = e.target as HTMLElement | null
            if (target?.closest(module.contextMenu?.selector)) return true
        }

        if (!module.contextMenu?.node) return false

        const nodes = CONTEXT_NODES[module.contextMenu.node]
        return nodes
            ? nodes.some((n) => this.editor.tiptap.isActive(n))
            : this.editor.tiptap.isActive(module.contextMenu?.node)
    }

    private getItems(e: MouseEvent): (ContextMenuItem | SeparatorItem)[] {
        const separator: SeparatorItem = { separator: true }

        const groups = this.editor.modules
            .filter(
                (m) =>
                    (m.contextMenu?.node || m.contextMenu?.selector) && this.isContextActive(m, e)
            )
            .sort((a, b) => (a.contextMenu?.order ?? 0) - (b.contextMenu?.order ?? 0))
            .map((m) => m.contextMenu!.items.sort((a, b) => (a.order ?? 0) - (b.order ?? 0)))
            .filter((g) => g.length > 0)

        return groups.flatMap((group, i) => (i === 0 ? group : [separator, ...group]))
    }

    private render(items: (ContextMenuItem | SeparatorItem)[]): HTMLElement {
        const doc = this.editor.docParent
        const menu = doc.createElement('div')
        menu.className = 'tiptap-context-menu'

        const renderedSubmenus = new Set<TranslationKey>()

        for (const item of items) {
            if ('separator' in item) {
                menu.appendChild(doc.createElement('hr'))
                continue
            }

            if (item.parent) {
                if (renderedSubmenus.has(item.parent)) continue
                renderedSubmenus.add(item.parent)

                const siblings = items.filter(
                    (i): i is ContextMenuItem => !('separator' in i) && i.parent === item.parent
                )
                const allDisabled = siblings.every((c) => c.disabled?.(this.editor) ?? false)
                if (allDisabled) continue

                const icon = siblings.find((c) => c.parentIcon)?.parentIcon
                menu.appendChild(this.renderSubmenu(item.parent, siblings, icon))
            } else {
                menu.appendChild(this.renderItem(item))
            }
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
        label.textContent = this.editor.trans(item.label)
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
    private renderSubmenu(
        label: TranslationKey,
        children: ContextMenuItem[],
        icon?: string
    ): HTMLElement {
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
        text.textContent = this.editor.trans(label)
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
        const win = this.editor.docEditor.defaultView
        const frame = win?.frameElement as HTMLIFrameElement | null
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
