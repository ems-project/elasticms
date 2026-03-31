import { Editor } from '@tiptap/core'
import Document from '@tiptap/extension-document'
import Paragraph from '@tiptap/extension-paragraph'
import Text from '@tiptap/extension-text'
import { DefaultToolbarConfig, ToolbarConfigItem } from './types.ts'
import { Toolbar } from './toolbar.ts'

interface TiptapEditorOptions {
    element: HTMLElement
    toolbarElement?: HTMLElement
    textarea?: HTMLTextAreaElement
    toolbarConfig?: ToolbarConfigItem[]
    onUpdate?: (editor: Editor) => void
    onReady?: () => void
}

export class TiptapEditor {
    tiptap: Editor
    toolbar: Toolbar | null = null
    element: HTMLElement
    textarea: HTMLTextAreaElement | null = null

    isSourceView: boolean = false
    isMaximized: boolean = false

    constructor(options: TiptapEditorOptions) {
        this.element = options.element
        this.textarea = options.textarea ?? null

        if (options.toolbarElement) {
            const config = options.toolbarConfig ?? DefaultToolbarConfig
            this.toolbar = new Toolbar(options.toolbarElement, config)
            this.toolbar.bind(this)
        }

        this.tiptap = new Editor({
            element: options.element,
            extensions: [Document, Paragraph, Text, ...(this.toolbar?.getExtensions() ?? [])],
            content: this.textarea?.value || this.element.innerHTML,
            onUpdate: ({ editor }) => {
                this.toolbar?.update()
                if (this.textarea) {
                    this.textarea.value = editor.getHTML()
                }
                options.onUpdate?.(editor)
            },
            onSelectionUpdate: () => this.toolbar?.update(),
            onTransaction: () => this.toolbar?.update()
        })
    }
}
