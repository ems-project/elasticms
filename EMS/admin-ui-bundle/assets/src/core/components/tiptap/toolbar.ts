import './../../../../css/core/components/_tiptap_toolbar.scss'
import { Extension, Mark, Node } from '@tiptap/core'
import { Actions, HtmlTransform, TiptapModule } from './types.ts'
import { TiptapEditor } from './editor.ts'
import { WysiwygProfile } from '../wysiwyg/wysiwyg.ts'

export interface ToolbarConfig {
    customActions?: TiptapModule[]
    wysiwygProfile?: WysiwygProfile | null
}

export class Toolbar {
    readonly container: HTMLElement
    private extensions: (Extension | Mark | Node)[] = []
    private htmlTransforms: HtmlTransform[] = []
    private tiptapEditor!: TiptapEditor
    private actions: Map<string, TiptapModule> = new Map(Actions.map((a) => [a.name, a]))
    private readonly wysiwygProfile: WysiwygProfile

    constructor(config: ToolbarConfig) {
        config.customActions?.forEach((action) => this.actions.set(action.name, action))

        this.wysiwygProfile = config.wysiwygProfile ?? new WysiwygProfile()

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

                this.getActionsByGroup(groupName).forEach((action) => {
                    if (removed.includes(action.name)) return
                    if (action.isEnabled && !action.isEnabled(this.wysiwygProfile)) return

                    groupDiv.appendChild(this.createButton(action))

                    action.extensions?.forEach((ext) => {
                        if (
                            ext.name &&
                            !this.extensions.some((e) => (e as any).name === ext.name)
                        ) {
                            this.extensions.push(ext)
                        }
                    })

                    action.htmlTransforms?.forEach((t) => {
                        if (!this.htmlTransforms.some((x) => x.name === t.name)) {
                            this.htmlTransforms.push(t)
                        }
                    })
                })

                if (groupDiv.children.length > 0) currentRow.appendChild(groupDiv)
            })
        }
    }

    bind(tiptapEditor: TiptapEditor) {
        this.tiptapEditor = tiptapEditor
    }

    getExtensions(): (Extension | Mark | Node)[] {
        return this.extensions
    }
    getHtmlTransforms(): HtmlTransform[] {
        return this.htmlTransforms
    }
    getActionsByGroup(groupName: string): TiptapModule[] {
        return Array.from(this.actions.values()).filter((action) => action.group === groupName)
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

    private createButton(action: TiptapModule): HTMLButtonElement {
        const btn = document.createElement('button')
        btn.type = 'button'
        btn.innerHTML = action.icon ?? ''
        btn.dataset.action = action.name
        if (action.tooltip) btn.title = action.tooltip

        btn.onclick = (e) => {
            e.preventDefault()
            e.stopPropagation()
            action.command?.(this.tiptapEditor)
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
            const action = this.actions.get(btn.dataset.action!)
            if (action) {
                btn.classList.toggle('is-active', action.isActive(this.tiptapEditor))
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
        this.container.parentNode?.removeChild(this.container)
    }
}
