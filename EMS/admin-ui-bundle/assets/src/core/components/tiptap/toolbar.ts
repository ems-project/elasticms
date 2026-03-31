import './toolbar.css'
import { Extension, Mark, Node } from '@tiptap/core'
import type { TiptapEditor } from './editor.ts'
import { ActionMap, getActionsByGroup, ToolbarAction, ToolbarConfigItem } from './types.ts'

export class Toolbar {
    element: HTMLElement
    private extensions: (Extension | Mark | Node)[] = []
    private tiptapEditor!: TiptapEditor

    constructor(element: HTMLElement, config: ToolbarConfigItem[]) {
        element.classList.add('tiptap-toolbar')
        this.element = element
        this.build(config)
    }

    bind(tiptapEditor: TiptapEditor) {
        this.tiptapEditor = tiptapEditor
    }

    getExtensions(): (Extension | Mark | Node)[] {
        return this.extensions
    }

    private build(config: ToolbarConfigItem[]) {
        this.element.innerHTML = ''
        let currentRow = this.createRow()

        for (const item of config) {
            if (item === '/') {
                currentRow = this.createRow()
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

    private createRow(): HTMLElement {
        const row = document.createElement('div')
        row.className = 'tiptap-toolbar-row'
        this.element.appendChild(row)
        return row
    }

    private createButton(action: ToolbarAction): HTMLButtonElement {
        const btn = document.createElement('button')
        btn.type = 'button'
        btn.innerHTML = action.icon ?? ''
        btn.dataset.action = action.name

        if (action.tooltip) {
            btn.title = action.tooltip
        }

        btn.onclick = (event) => {
            event.preventDefault()
            event.stopPropagation()
            action.command?.(this.tiptapEditor)
            this.update()
        }

        return btn
    }

    update() {
        this.element.querySelectorAll<HTMLButtonElement>('button[data-action]').forEach((btn) => {
            const name = btn.dataset.action
            if (!name) return

            const action = ActionMap.get(name)
            if (!action) return

            btn.classList.toggle('is-active', action.isActive(this.tiptapEditor))
        })
    }

    setDisabled(disabled: boolean, exclude: string[] = []) {
        this.element.querySelectorAll<HTMLButtonElement>('button[data-action]').forEach((btn) => {
            const name = btn.dataset.action
            if (name) btn.disabled = disabled && !exclude.includes(name)
        })
    }
}
