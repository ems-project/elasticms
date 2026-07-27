import '../../../../../css/core/components/tiptap/_toolbar.scss'

import { ToolbarItem, ToolbarItemCustom } from './../Types.ts'
import { TiptapEditor } from './../Editor.ts'

export class Toolbar {
    private readonly container: HTMLElement
    private readonly editor: TiptapEditor
    private items: Map<string, ToolbarItem | ToolbarItemCustom> = new Map()
    private groups: Map<string, (ToolbarItem | ToolbarItemCustom)[]> = new Map()
    private globalDisabled = false
    private globalExclude: string[] = []

    constructor(editor: TiptapEditor) {
        this.editor = editor

        this.container = document.createElement('div')
        this.container.className = 'tiptap-toolbar'
        this.container.onmousedown = (e) => e.preventDefault()
    }

    addItem(group: string, item: ToolbarItem | ToolbarItemCustom) {
        this.items.set(item.name, item)
        if (!this.groups.has(group)) this.groups.set(group, [])
        const items = this.groups.get(group)!
        items.push(item)
        items.sort(
            (a, b) =>
                (('order' in a ? a.order : undefined) ?? 0) -
                (('order' in b ? b.order : undefined) ?? 0)
        )
    }

    addRowBreak() {
        this.groups.set(`__break_${this.groups.size}`, [])
    }

    mount(target: HTMLElement) {
        if (!this.container.children.length) this.build()
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

    private createButton(item: ToolbarItem): HTMLSpanElement {
        const wrapper = document.createElement('span')
        wrapper.title = this.editor.trans(item.tooltip)

        const btn = document.createElement('button')
        btn.type = 'button'
        btn.classList.add('tiptap-toolbar-button')
        btn.innerHTML = item.icon
        btn.dataset.action = item.name

        btn.onclick = (e) => {
            e.preventDefault()
            e.stopPropagation()
            item.command(this.editor)
            this.update()
        }

        wrapper.appendChild(btn)
        return wrapper
    }

    getButton(name: string): HTMLElement | null {
        return this.container.querySelector<HTMLButtonElement>(`[data-action="${name}"]`)
    }

    update() {
        this.container.querySelectorAll<HTMLButtonElement>('button[data-action]').forEach((btn) => {
            const item = this.items.get(btn.dataset.action!)
            if (!item) return

            const name = btn.dataset.action!

            btn.classList.toggle('is-active', 'isActive' in item && item.isActive?.(this.editor))

            if (this.globalDisabled) {
                btn.disabled = !this.globalExclude.includes(name)
            } else {
                btn.disabled = ('isDisabled' in item && item.isDisabled?.(this.editor)) ?? false
            }
        })
    }

    setDisabled(disabled: boolean, exclude: string[] = []) {
        this.globalDisabled = disabled
        this.globalExclude = exclude
        this.update()
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
