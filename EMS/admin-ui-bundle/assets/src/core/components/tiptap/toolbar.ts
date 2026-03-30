import './toolbar.css'
import type { TiptapEditor } from './editor.ts'
import { ToolbarAction } from './types.ts'

export class Toolbar {
    element: HTMLElement
    private actionRegistry: Record<string, ToolbarAction>
    private groupRegistry: Record<string, string[]>
    private tiptapEditor: TiptapEditor

    constructor(
        element: HTMLElement,
        groupRegistry: Record<string, string[]>,
        actionRegistry: Record<string, ToolbarAction>,
        tiptapEditor: TiptapEditor
    ) {
        element.classList.add('tiptap-toolbar')

        this.element = element
        this.actionRegistry = actionRegistry
        this.groupRegistry = groupRegistry
        this.tiptapEditor = tiptapEditor

        this.build()
    }

    private build() {
        Object.keys(this.groupRegistry).forEach((groupName) => {
            const groupDiv = document.createElement('div')
            groupDiv.className = 'tiptap-toolbar-group'

            const items = this.groupRegistry[groupName] || []
            items.forEach((actionKey) => {
                const action = this.actionRegistry[actionKey]
                if (action) {
                    groupDiv.appendChild(this.createButton(actionKey, action))
                }
            })

            if (groupDiv.children.length > 0) {
                this.element.appendChild(groupDiv)
            }
        })
    }

    private createButton(key: string, action: ToolbarAction): HTMLButtonElement {
        const btn = document.createElement('button')
        btn.type = 'button'
        btn.innerHTML = action.icon
        btn.dataset.action = key

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
        this.element.querySelectorAll('button[data-action]').forEach((btn) => {
            const actionKey = (btn as HTMLElement).dataset.action
            const action = actionKey ? this.actionRegistry[actionKey] : null
            if (action) {
                btn.classList.toggle('is-active', action.isActive(this.tiptapEditor))
            }
        })
    }

    setDisabled(disabled: boolean, exclude: string[] = []) {
        const buttons = this.element.querySelectorAll<HTMLButtonElement>('button[data-action]')

        buttons.forEach((btn) => {
            const actionKey = btn.dataset.action

            if (actionKey && exclude.includes(actionKey)) {
                btn.disabled = false
                btn.style.opacity = '1'
                btn.style.cursor = 'pointer'
                return
            }

            btn.disabled = disabled
            btn.style.opacity = disabled ? '0.4' : '1'
            btn.style.cursor = disabled ? 'not-allowed' : 'pointer'
        })
    }
}
