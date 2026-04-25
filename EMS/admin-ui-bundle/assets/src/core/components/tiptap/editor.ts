import { Editor } from '@tiptap/core'
import Document from '@tiptap/extension-document'
import Paragraph from '@tiptap/extension-paragraph'
import Text from '@tiptap/extension-text'
import { Toolbar, ToolbarConfig } from './toolbar.ts'

interface TiptapEditorOptions {
    element: HTMLElement
    content?: string
    toolbarElement?: HTMLElement | null
    toolbarConfig?: ToolbarConfig
}

export class TiptapEditor {
    tiptap: Editor
    toolbar: Toolbar
    element: HTMLElement

    constructor(options: TiptapEditorOptions) {
        this.element = options.element

        this.toolbar = new Toolbar(options.toolbarConfig ?? {})
        this.toolbar.bind(this)

        this.tiptap = new Editor({
            element: {
                mount: options.element
            },
            extensions: [Document, Paragraph, Text, ...this.toolbar.getExtensions()],
            content: options.content,
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
