import './../../../../css/core/components/_tiptap_toolbar.scss'
import { Extension, Mark, Node } from '@tiptap/core'
import { ActionMap, getActionsByGroup, ToolbarAction, ToolbarConfigItem } from './types.ts'
import { TiptapEditor } from './editor.ts'

export class Toolbar {
    readonly container: HTMLElement
    private extensions: (Extension | Mark | Node)[] = []
    private tiptapEditor!: TiptapEditor
    private readonly config: ToolbarConfigItem[]

    constructor(config: ToolbarConfigItem[]) {
        this.config = config

        this.container = document.createElement('div')
        this.container.className = 'tiptap-toolbar'
        this.container.onmousedown = (e) => e.preventDefault()

        this.build()
    }

    private build() {
        let currentRow = this.createRow(this.container)

        for (const item of this.config) {
            if (item === '/') {
                currentRow = this.createRow(this.container)
                continue
            }

            const groups = item.groups ?? [item.name]
            groups.forEach((groupName) => {
                const groupDiv = document.createElement('div')
                groupDiv.className = 'tiptap-toolbar-group'

                getActionsByGroup(groupName).forEach((action) => {
                    groupDiv.appendChild(this.createButton(action))

                    action.extensions?.forEach((ext) => {
                        if (
                            ext.name &&
                            !this.extensions.some((e) => (e as any).name === ext.name)
                        ) {
                            this.extensions.push(ext)
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

    private createButton(action: ToolbarAction): HTMLButtonElement {
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

    update() {
        if (!this.container) return
        this.container.querySelectorAll<HTMLButtonElement>('button[data-action]').forEach((btn) => {
            const action = ActionMap.get(btn.dataset.action!)
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
