import { Editor, Extension, Mark, Node } from '@tiptap/core'
import { DEFAULT_EXTENSIONS } from './extensions.ts'
import { Toolbar } from './toolbar.ts'
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
    element: HTMLElement
    readonly modules: TiptapModule[]
    private extensions: (Extension | Mark | Node)[] = []
    private htmlTransforms: HtmlTransform[] = []

    constructor(options: TiptapEditorOptions) {
        this.element = options.element
        this.modules = [...Modules, ...(options.customModules ?? [])]

        const profile = options.wysiwygProfile ?? new WysiwygProfile()
        this.collectExtensions(profile)

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

        if (options.toolbarElement) this.attachToolbar(options.toolbarElement)
    }

    private collectExtensions(profile: WysiwygProfile) {
        for (const mod of this.modules) {
            if (mod.isEnabled && !mod.isEnabled(profile)) continue

            mod.extensions?.forEach((ext) => {
                if (!this.extensions.some((e) => (e as any).name === (ext as any).name)) {
                    this.extensions.push(ext)
                }
            })

            mod.htmlTransforms?.forEach((t) => {
                if (!this.htmlTransforms.some((x) => x.name === t.name)) {
                    this.htmlTransforms.push(t)
                }
            })
        }
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
        this.element.innerHTML = ''
    }
}
