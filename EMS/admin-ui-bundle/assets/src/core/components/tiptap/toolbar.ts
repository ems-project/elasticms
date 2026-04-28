import './../../../../css/core/components/_tiptap_toolbar.scss'
import { TiptapModule } from './types.ts'
import { TiptapEditor } from './editor.ts'
import { WysiwygProfile } from '../wysiwyg/wysiwyg.ts'

export class Toolbar {
    readonly container: HTMLElement
    private tiptapEditor!: TiptapEditor
    private modules: Map<string, TiptapModule> = new Map()
    private readonly wysiwygProfile: WysiwygProfile

    constructor(modules: TiptapModule[], wysiwygProfile: WysiwygProfile) {
        modules.forEach((m) => this.modules.set(m.name, m))
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

                this.getModulesByToolbarGroup(groupName).forEach((mod) => {
                    if (removed.includes(mod.name)) return
                    groupDiv.appendChild(this.createButton(mod))
                })

                if (groupDiv.children.length > 0) currentRow.appendChild(groupDiv)
            })
        }
    }

    bind(tiptapEditor: TiptapEditor) {
        this.tiptapEditor = tiptapEditor
    }

    private getModulesByToolbarGroup(groupName: string): TiptapModule[] {
        return Array.from(this.modules.values()).filter(
            (m) => m.toolbar && m.toolbar.group === groupName
        )
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

    private createButton(mod: TiptapModule): HTMLButtonElement {
        const btn = document.createElement('button')
        btn.type = 'button'
        btn.innerHTML = mod.toolbar!.icon
        btn.dataset.action = mod.name
        if (mod.toolbar!.tooltip) btn.title = mod.toolbar!.tooltip

        btn.onclick = (e) => {
            e.preventDefault()
            e.stopPropagation()
            mod.command?.(this.tiptapEditor)
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
            const mod = this.modules.get(btn.dataset.action!)
            if (mod) btn.classList.toggle('is-active', mod.isActive(this.tiptapEditor))
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
