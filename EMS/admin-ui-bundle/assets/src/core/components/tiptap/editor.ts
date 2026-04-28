import { Editor, Extension, Mark, Node } from '@tiptap/core'
import { DEFAULT_EXTENSIONS } from './extensions.ts'
import { Toolbar } from './toolbar.ts'
import { ContextMenu } from './contextMenu.ts'
import { Modules, HtmlTransform, TiptapModule } from './types.ts'
import { WysiwygProfile } from '../wysiwyg/wysiwyg.ts'

interface TiptapEditorOptions {
    content?: string
    element: HTMLElement
    customModules?: TiptapModule[]
    toolbarElement?: HTMLElement | null
    wysiwygProfile?: WysiwygProfile | null
}

export class TiptapEditor {
    tiptap: Editor
    toolbar: Toolbar
    menu: ContextMenu
    element: HTMLElement
    readonly modules: TiptapModule[]
    private readonly extensions: (Extension | Mark | Node)[]
    private readonly htmlTransforms: HtmlTransform[]

    constructor(options: TiptapEditorOptions) {
        this.element = options.element

        const profile = options.wysiwygProfile ?? new WysiwygProfile()
        this.modules = [...Modules, ...(options.customModules ?? [])].filter(
            (m) => !m.isEnabled || m.isEnabled(profile)
        )

        this.extensions = this.buildExtensions()
        this.htmlTransforms = this.buildHtmlTransforms()

        this.toolbar = new Toolbar(this.modules, profile)
        this.toolbar.bind(this)

        this.tiptap = new Editor({
            element: { mount: options.element },
            extensions: [...DEFAULT_EXTENSIONS, ...this.extensions],
            content: this.transformToEditor(options.content ?? ''),
            onUpdate: () => this.toolbar.update(),
            onSelectionUpdate: () => this.toolbar.update(),
            onTransaction: () => this.toolbar.update()
        })

        this.menu = new ContextMenu(this)

        if (options.toolbarElement) this.attachToolbar(options.toolbarElement)
    }

    private buildExtensions(): (Extension | Mark | Node)[] {
        const seen = new Set<string>()
        return this.modules
            .flatMap((m) => m.extensions ?? [])
            .filter((ext) => {
                const name = (ext as any).name
                return name && !seen.has(name) && seen.add(name)
            })
    }

    private buildHtmlTransforms(): HtmlTransform[] {
        const seen = new Set<string>()
        return this.modules
            .flatMap((m) => m.htmlTransforms ?? [])
            .filter((t) => !seen.has(t.name) && seen.add(t.name))
    }

    getHTML(): string {
        return this.transformToOutput(this.tiptap.getHTML())
    }

    setContent(html: string) {
        this.tiptap.commands.setContent(this.transformToEditor(html))
    }

    private transformToEditor(html: string): string {
        if (!this.htmlTransforms.length) return html
        const doc = new DOMParser().parseFromString(`<div>${html}</div>`, 'text/html')
        const root = doc.body.firstChild as HTMLElement
        this.htmlTransforms.forEach((t) => t.toEditor?.(doc))
        return root.innerHTML
    }

    private transformToOutput(html: string): string {
        if (!this.htmlTransforms.length) return html
        const doc = new DOMParser().parseFromString(`<div>${html}</div>`, 'text/html')
        const root = doc.body.firstChild as HTMLElement
        this.htmlTransforms.forEach((t) => t.toOutput?.(doc))
        return root.innerHTML
    }

    attachToolbar(target: HTMLElement) {
        target.innerHTML = ''
        this.toolbar.mount(target)
    }

    destroy() {
        this.tiptap.destroy()
        this.toolbar.destroy()
        this.menu.destroy()
        this.element.innerHTML = ''
    }
}
