import { Editor, Extension, Mark, Node } from '@tiptap/core'
import Document from '@tiptap/extension-document'
import Text from '@tiptap/extension-text'

import './../../../../css/core/components/wysiwyg.scss'
import { WysiwygProfile, WysiwygRevisionOptions } from './types.ts'
import {
    DefaultModules,
    fa5Icons,
    IconSet,
    TiptapContext,
    TiptapModule,
    ToolbarAction
} from '../tiptap/types.ts'

interface ConfigGroup {
    name: string
    groups: string[]
}

export interface TiptapOptions {
    modules?: TiptapModule[]
    icons?: IconSet
}

export default class Tiptap implements TiptapContext {
    isSourceView: boolean = false
    isMaximized: boolean = false

    element: HTMLTextAreaElement
    iframe: HTMLIFrameElement | null = null
    innerEditor: Editor | null = null
    icons: IconSet

    config: ConfigGroup[]

    private readonly modules: TiptapModule[]
    private groupRegistry: Record<string, string[]> = {}
    private actionRegistry: Record<string, ToolbarAction> = {}
    private extensions: (Extension | Mark | Node)[] = []

    constructor(
        element: HTMLTextAreaElement,
        _options: WysiwygRevisionOptions | null,
        _profile: WysiwygProfile,
        tiptapOptions?: TiptapOptions
    ) {
        this.element = element
        this.modules = tiptapOptions?.modules || DefaultModules
        this.icons = tiptapOptions?.icons || fa5Icons

        this.resolveModules()

        this.config = [
            {
                name: 'default',
                groups: Object.keys(this.groupRegistry)
            }
        ]

        this.initIframe()
        this.initToolbar()
    }

    private resolveModules() {
        this.extensions = [Document, Text]

        for (const mod of this.modules) {
            this.extensions.push(...mod.extensions)
            Object.assign(this.groupRegistry, mod.groups)
            Object.assign(this.actionRegistry, mod.actions)
        }
    }

    private initToolbar() {
        const toolbar = document.createElement('div')
        toolbar.className = 'wysiwyg-toolbar'

        this.config.forEach((section) => {
            const sectionDiv = document.createElement('div')
            sectionDiv.className = 'wysiwyg-toolbar-section'

            section.groups.forEach((groupName) => {
                const groupDiv = document.createElement('div')
                groupDiv.className = 'wysiwyg-toolbar-group'

                const items = this.groupRegistry[groupName] || []
                items.forEach((actionKey) => {
                    const action = this.actionRegistry[actionKey]
                    if (action) {
                        groupDiv.appendChild(this.createButton(actionKey, action))
                    }
                })

                if (groupDiv.children.length > 0) {
                    sectionDiv.appendChild(groupDiv)
                }
            })

            toolbar.appendChild(sectionDiv)
        })

        const container = this.element.parentElement
        if (container) {
            container.insertBefore(toolbar, container.firstChild)
        }
    }

    private createButton(key: string, action: ToolbarAction): HTMLButtonElement {
        const btn = document.createElement('button')
        btn.type = 'button'
        btn.innerHTML = this.icons[action.icon]
        btn.dataset.action = key

        if (action.tooltip) {
            btn.title = action.tooltip
        }

        btn.onclick = (event) => {
            event.preventDefault()
            event.stopPropagation()

            if (action.command) {
                action.command(this.innerEditor!, this)
                if (this.innerEditor) {
                    this.updateToolbarUI(this.innerEditor)
                }
            }
        }
        return btn
    }

    public updateToolbarUI(editor: Editor) {
        const container = this.element.parentElement
        const toolbar = container?.querySelector('.wysiwyg-toolbar') as HTMLElement

        if (!toolbar) return

        const buttons = toolbar.querySelectorAll('button[data-action]')
        buttons.forEach((btn) => {
            const actionKey = (btn as HTMLElement).dataset.action
            const action = actionKey ? this.actionRegistry[actionKey] : null

            if (action) {
                const active = action.isActive(editor, this)
                btn.classList.toggle('is-active', active)
            }
        })
    }

    private initIframe() {
        const container = document.createElement('div')
        container.className = 'wysiwyg-container'
        this.element.parentNode?.insertBefore(container, this.element)

        container.appendChild(this.element)
        this.element.className = 'wysiwyg-source-view'

        this.iframe = document.createElement('iframe')
        this.iframe.className = 'wysiwyg-iframe'
        container.appendChild(this.iframe)

        const doc = this.iframe.contentDocument
        if (doc) {
            const style = doc.createElement('style')
            style.textContent = `
                body { margin: 0; font-family: sans-serif; margin: 10px }
                .ProseMirror { outline: none; min-height: 100%; }
            `
            doc.head.appendChild(style)
            this.setupEditorInsideIframe(doc.body)
        }
    }

    private setupEditorInsideIframe(mountElement: HTMLElement) {
        if (!this.iframe?.contentWindow) return

        this.innerEditor = new Editor({
            element: mountElement,
            extensions: this.extensions,
            content: this.element.value,
            onUpdate: ({ editor }) => {
                this.element.value = editor.getHTML()
                this.updateToolbarUI(editor)
            },
            onSelectionUpdate: ({ editor }) => {
                this.updateToolbarUI(editor)
            },
            onTransaction: ({ editor }) => {
                this.updateToolbarUI(editor)
            }
        })
    }

    public setToolbarDisabled(toolbar: HTMLElement, disabled: boolean) {
        const buttons = toolbar.querySelectorAll(
            'button:not([data-action="source"]):not([data-action="maximize"])'
        )
        buttons.forEach((btn) => {
            const b = btn as HTMLButtonElement
            b.disabled = disabled
            b.style.opacity = disabled ? '0.4' : '1'
            b.style.cursor = disabled ? 'not-allowed' : 'pointer'
        })
    }
}
