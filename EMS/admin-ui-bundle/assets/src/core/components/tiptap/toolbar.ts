import { TiptapModule, ToolbarItem } from './types.ts'
import { TiptapEditor } from './editor.ts'
import { WysiwygProfile } from '../wysiwyg/wysiwyg.ts'

export class Toolbar {
    readonly container: HTMLElement
    private tiptapEditor!: TiptapEditor
    private items: Map<string, ToolbarItem> = new Map()
    private readonly wysiwygProfile: WysiwygProfile

    constructor(modules: TiptapModule[], wysiwygProfile: WysiwygProfile) {
        modules.flatMap((m) => m.toolbar ?? []).forEach((item) => this.items.set(item.name, item))
        this.wysiwygProfile = wysiwygProfile

        this.container = document.createElement('div')
        this.container.className = 'tiptap-toolbar'
        this.container.onmousedown = (e) => e.preventDefault()

        this.build()
    }

    private build() {
        const removed = this.wysiwygProfile.config.removeButtons?.split(',') || []

        let currentRow = this.createRow(this.container)
        for (const item of this.wysiwygProfile.config.toolbarGroups) {
            if (item === '/') {
                currentRow = this.createRow(this.container)
                continue
            }

            const groups = item.groups ?? [item.name]
            groups.forEach((groupName) => {
                const groupDiv = document.createElement('div')
                groupDiv.className = 'tiptap-toolbar-group'

                this.getItemsByToolbarGroup(groupName).forEach((toolbarItem) => {
                    if (removed.includes(toolbarItem.name)) return
                    groupDiv.appendChild(this.createButton(toolbarItem))
                })

                if (groupDiv.children.length > 0) currentRow.appendChild(groupDiv)
            })
        }
    }

    bind(tiptapEditor: TiptapEditor) {
        this.tiptapEditor = tiptapEditor
    }

    private getItemsByToolbarGroup(groupName: string): ToolbarItem[] {
        return Array.from(this.items.values()).filter((item) => item.group === groupName)
    }

    mount(target: HTMLElement) {
        target.appendChild(this.container)
        this.update()
    }

    private createRow(parent: HTMLElement): HTMLElement {
        const row = document.createElement('div')
        row.className = 'tiptap-toolbar-row'
        parent.appendChild(row)
        return row
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
            item.command(this.tiptapEditor)
            this.update()
        }

        return btn
    }

    getButton(name: string): HTMLElement | null {
        return this.container.querySelector<HTMLButtonElement>(`[data-action="${name}"]`)
    }

    update() {
        if (!this.container) return
        this.container.querySelectorAll<HTMLButtonElement>('button[data-action]').forEach((btn) => {
            const item = this.items.get(btn.dataset.action!)
            if (item) btn.classList.toggle('is-active', item.isActive?.(this.tiptapEditor) ?? false)
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
