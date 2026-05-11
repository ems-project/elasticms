import '../../../../css/core/components/tiptap/_toolbar.scss'

import { ToolbarItem, ToolbarItemCustom } from './types.ts'
import { TiptapEditor } from './editor.ts'

export class Toolbar {
    private readonly container: HTMLElement
    private readonly editor: TiptapEditor
    private items: Map<string, ToolbarItem> = new Map()
    private groups: Map<string, (ToolbarItem | ToolbarItemCustom)[]> = new Map()

    constructor(editor: TiptapEditor) {
        this.editor = editor

        this.container = document.createElement('div')
        this.container.className = 'tiptap-toolbar'
        this.container.onmousedown = (e) => e.preventDefault()
    }

    addItem(group: string, item: ToolbarItem | ToolbarItemCustom) {
        if ('name' in item) this.items.set(item.name, item)
        if (!this.groups.has(group)) this.groups.set(group, [])
        this.groups.get(group)!.push(item)
    }

    addRowBreak() {
        this.groups.set(`__break_${this.groups.size}`, [])
    }

    mount(target: HTMLElement) {
        this.build()
        target.appendChild(this.container)
        this.update()
    }

    private build() {
        this.container.innerHTML = ''
        let row = document.createElement('div')
        row.className = 'tiptap-toolbar-row'

        for (const [key, items] of this.groups) {
            if (key.startsWith('__break_')) {
                if (row.children.length > 0) this.container.appendChild(row)
                row = document.createElement('div')
                row.className = 'tiptap-toolbar-row'
                continue
            }

            const groupDiv = document.createElement('div')
            groupDiv.className = 'tiptap-toolbar-group'

            for (const item of items) {
                if ('create' in item) {
                    groupDiv.appendChild(item.create(this.editor))
                } else {
                    groupDiv.appendChild(this.createButton(item))
                }
            }

            if (groupDiv.children.length > 0) row.appendChild(groupDiv)
        }

        if (row.children.length > 0) this.container.appendChild(row)
    }

    private createButton(item: ToolbarItem): HTMLButtonElement {
        const btn = document.createElement('button')
        btn.type = 'button'
        btn.classList.add('tiptap-toolbar-button')
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
            if (item) {
                btn.classList.toggle('is-active', item.isActive?.(this.editor) ?? false)
                btn.disabled = item.isDisabled?.(this.editor) ?? false
            }
        })
    }

    setDisabled(disabled: boolean, exclude: string[] = []) {
        this.container.querySelectorAll<HTMLButtonElement>('button[data-action]').forEach((btn) => {
            const name = btn.dataset.action
            if (name) btn.disabled = disabled && !exclude.includes(name)
        })
    }

    destroy() {
        for (const [, items] of this.groups) {
            for (const item of items) {
                if ('destroy' in item && item.destroy) item.destroy(this.editor)
            }
        }
        this.container.parentNode?.removeChild(this.container)
    }
}
