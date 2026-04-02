import { Editor } from '@tiptap/core'
import Document from '@tiptap/extension-document'
import Paragraph from '@tiptap/extension-paragraph'
import Text from '@tiptap/extension-text'
import { DefaultToolbarConfig, ToolbarConfigItem } from './types.ts'
import { Toolbar } from './toolbar.ts'

interface TiptapEditorOptions {
    element: HTMLElement
    toolbarElement?: HTMLElement | null
    textarea?: HTMLTextAreaElement
    toolbarConfig?: ToolbarConfigItem[]
}

export class TiptapEditor {
    tiptap: Editor
    toolbar: Toolbar
    element: HTMLElement
    textarea: HTMLTextAreaElement | null = null

    isSourceView: boolean = false
    isMaximized: boolean = false

    constructor(options: TiptapEditorOptions) {
        this.element = options.element
        this.textarea = options.textarea ?? null

        const config = options.toolbarConfig ?? DefaultToolbarConfig
        this.toolbar = new Toolbar(config)
        this.toolbar.bind(this)

        this.tiptap = new Editor({
            element: {
                mount: options.element
            },
            extensions: [Document, Paragraph, Text, ...this.toolbar.getExtensions()],
            content: this.textarea?.value || this.element.innerHTML,
            onUpdate: () => this.toolbar.update(),
            onSelectionUpdate: () => this.toolbar.update(),
            onTransaction: () => this.toolbar.update()
        })

        if (options.toolbarElement) {
            this.attachToolbar(options.toolbarElement)
        }
    }

    attachToolbar(target: HTMLElement) {
        target.innerHTML = ''
        this.toolbar.mount(target)
    }

    destroy() {
        this.tiptap.destroy()
        this.toolbar.destroy()
        this.element.innerHTML = ''
    }
}
