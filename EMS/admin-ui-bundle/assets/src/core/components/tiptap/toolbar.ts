import '../../../../css/core/components/tiptap/_toolbar.scss'

import { ToolbarItem } from './types.ts'
import { TiptapEditor } from './editor.ts'

export class Toolbar {
    private readonly container: HTMLElement
    private readonly editor: TiptapEditor
    private items: Map<string, ToolbarItem> = new Map()
    private groups: Map<string, ToolbarItem[]> = new Map()

    constructor(editor: TiptapEditor) {
        this.editor = editor

        this.container = document.createElement('div')
        this.container.className = 'tiptap-toolbar'
        this.container.onmousedown = (e) => e.preventDefault()
    }

    addItem(group: string, item: ToolbarItem) {
        this.items.set(item.name, item)
        if (!this.groups.has(group)) this.groups.set(group, [])
        this.groups.get(group)!.push(item)
    }

    mount(target: HTMLElement) {
        this.build()
        target.appendChild(this.container)
        this.update()
    }

    private build() {
        this.container.innerHTML = ''
        const row = document.createElement('div')
        row.className = 'tiptap-toolbar-row'

        for (const [, items] of this.groups) {
            const groupDiv = document.createElement('div')
            groupDiv.className = 'tiptap-toolbar-group'

            items.forEach((item) => groupDiv.appendChild(this.createButton(item)))

            if (groupDiv.children.length > 0) row.appendChild(groupDiv)
        }

        this.container.appendChild(row)
    }

    private createButton(item: ToolbarItem): HTMLButtonElement {
        const btn = document.createElement('button')
        btn.type = 'button'
        btn.innerHTML = item.icon
        btn.dataset.action = item.name
        if (item.tooltip) btn.title = item.tooltip

        btn.onclick = (e) => {
            e.preventDefault()
            e.stopPropagation()
            item.command(this.editor)
            this.update()
        }

        return btn
    }

    getButton(name: string): HTMLElement | null {
        return this.container.querySelector<HTMLButtonElement>(`[data-action="${name}"]`)
    }

    update() {
        this.container.querySelectorAll<HTMLButtonElement>('button[data-action]').forEach((btn) => {
            const item = this.items.get(btn.dataset.action!)
            if (item) btn.classList.toggle('is-active', item.isActive?.(this.editor) ?? false)
        })
    }

    setDisabled(disabled: boolean, exclude: string[] = []) {
        this.container.querySelectorAll<HTMLButtonElement>('button[data-action]').forEach((btn) => {
            const name = btn.dataset.action
            if (name) btn.disabled = disabled && !exclude.includes(name)
        })
    }

    destroy() {
        this.container.parentNode?.removeChild(this.container)
    }
}
