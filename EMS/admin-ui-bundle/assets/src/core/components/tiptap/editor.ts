import { Editor, Extension, Mark, Node } from '@tiptap/core'
import Document from '@tiptap/extension-document'
import Text from '@tiptap/extension-text'
import { DefaultModules, fa5Icons, IconSet, TiptapModule, ToolbarAction } from './types.ts'
import { Toolbar } from './toolbar.ts'

interface TiptapEditorOptions {
    element: HTMLElement
    toolbarElement: HTMLElement
    textarea?: HTMLTextAreaElement
    onUpdate?: (editor: Editor) => void
    onReady?: () => void
    modules?: TiptapModule[]
    icons?: IconSet
}

export class TiptapEditor {
    tiptap: Editor
    toolbar: Toolbar
    icons: IconSet
    element: HTMLElement
    textarea: HTMLTextAreaElement | null = null

    isSourceView: boolean = false
    isMaximized: boolean = false

    private groupRegistry: Record<string, string[]> = {}
    private actionRegistry: Record<string, ToolbarAction> = {}

    constructor(options: TiptapEditorOptions) {
        const modules = options.modules || DefaultModules
        this.icons = options.icons || fa5Icons
        this.element = options.element
        this.textarea = options.textarea ?? null

        const extensions = this.resolveModules(modules)

        this.toolbar = new Toolbar(
            options.toolbarElement,
            this.icons,
            this.groupRegistry,
            this.actionRegistry,
            this
        )

        this.tiptap = new Editor({
            element: options.element,
            extensions,
            content: this.textarea?.value || this.element.innerHTML,
            onCreate: () => options.onReady?.(),
            onUpdate: ({ editor }) => {
                this.toolbar.update()
                if (this.textarea) {
                    this.textarea.value = editor.getHTML()
                }
                options.onUpdate?.(editor)
            },
            onSelectionUpdate: () => this.toolbar.update(),
            onTransaction: () => this.toolbar.update()
        })
    }

    private resolveModules(modules: TiptapModule[]): (Extension | Mark | Node)[] {
        const extensions: (Extension | Mark | Node)[] = [Document, Text]

        for (const mod of modules) {
            extensions.push(...mod.extensions)
            Object.assign(this.groupRegistry, mod.groups)
            Object.assign(this.actionRegistry, mod.actions)
        }

        return extensions
    }
}
