import './tiptap.css'
import { WysiwygProfile, WysiwygRevisionOptions } from './types.ts'
import { DefaultModules, IconSet, TiptapModule } from '../tiptap/types.ts'

import { TiptapEditor } from '../tiptap/editor.ts'

interface ConfigGroup {
    name: string
    groups: string[]
}

export interface TiptapOptions {
    modules?: TiptapModule[]
    icons?: IconSet
}

export default class Tiptap {
    element: HTMLTextAreaElement
    config: ConfigGroup[]

    private groupRegistry: Record<string, string[]> = {}
    tiptapOptions: TiptapOptions | null = null
    options: WysiwygRevisionOptions | null

    constructor(
        element: HTMLTextAreaElement,
        options: WysiwygRevisionOptions | null,
        _profile: WysiwygProfile,
        tiptapOptions?: TiptapOptions
    ) {
        this.element = element
        this.options = options
        this.tiptapOptions = tiptapOptions ?? null

        this.config = [
            {
                name: 'default',
                groups: Object.keys(this.groupRegistry)
            }
        ]

        this.init()
    }

    private init() {
        const height = this.options?.height ?? this.element.offsetHeight;

        const container = document.createElement('div')
        container.className = 'wysiwyg-container'
        container.style.height = `${height}px`
        this.element.parentNode?.insertBefore(container, this.element)

        const toolbar = document.createElement('div')
        toolbar.className = 'wysiwyg-toolbar'
        container.appendChild(toolbar)

        container.appendChild(this.element)
        this.element.className = 'wysiwyg-source-view'

        const iframe = document.createElement('iframe')
        iframe.className = 'wysiwyg-iframe'
        container.appendChild(iframe)

        const doc = iframe.contentDocument
        if (null === doc) return

        const style = doc.createElement('style')
        style.textContent = `
            body { margin: 0; font-family: sans-serif; margin: 10px }
            .ProseMirror { outline: none; min-height: 100%; }
        `
        doc.head.appendChild(style)

        new TiptapEditor({
            element: doc.body,
            textarea: this.element,
            icons: this.tiptapOptions?.icons,
            modules: DefaultModules,
            toolbarElement: toolbar
        })
    }
}
