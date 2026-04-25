import { Editor } from '@tiptap/core'
import { DEFAULT_EXTENSIONS } from './extensions.ts'
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
            element: { mount: options.element },
            extensions: [...DEFAULT_EXTENSIONS, ...this.toolbar.getExtensions()],
            content: this.transformToEditor(options.content ?? ''),
            onUpdate: () => this.toolbar.update(),
            onSelectionUpdate: () => this.toolbar.update(),
            onTransaction: () => this.toolbar.update()
        })

        if (options.toolbarElement) this.attachToolbar(options.toolbarElement)
    }

    getHTML(): string {
        return this.transformToOutput(this.tiptap.getHTML())
    }

    setContent(html: string) {
        this.tiptap.commands.setContent(this.transformToEditor(html))
    }

    private transformToEditor(html: string): string {
        console.debug('transform to editor')

        const transforms = this.toolbar.getHtmlTransforms()
        if (!transforms.length) return html
        const doc = new DOMParser().parseFromString(`<div>${html}</div>`, 'text/html')
        const root = doc.body.firstChild as HTMLElement
        transforms.forEach((t) => t.toEditor?.(doc))
        return root.innerHTML
    }

    private transformToOutput(html: string): string {
        console.debug('transform to output')

        const transforms = this.toolbar.getHtmlTransforms()
        if (!transforms.length) return html
        const doc = new DOMParser().parseFromString(`<div>${html}</div>`, 'text/html')
        const root = doc.body.firstChild as HTMLElement
        transforms.forEach((t) => t.toOutput?.(doc))
        return root.innerHTML
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
