import './tiptap.css'
import { WysiwygProfile, WysiwygRevisionOptions } from './types.ts'
import { DefaultModules, fa5Icons, IconSet, TiptapModule } from '../tiptap/types.ts'

import { TiptapEditor } from '../tiptap/editor.ts'
import ChangeEvent from '../../events/changeEvent.ts'

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
    options: WysiwygRevisionOptions | null

    constructor(
        element: HTMLTextAreaElement,
        options: WysiwygRevisionOptions | null,
        _profile: WysiwygProfile,
        tiptapOptions?: TiptapOptions
    ) {
        this.element = element
        this.options = options

        this.config = [
            {
                name: 'default',
                groups: Object.keys(this.groupRegistry)
            }
        ]

        const height = this.options?.height ?? this.element.offsetHeight
        this.element.style.display = 'none'

        const icons = tiptapOptions?.icons ?? fa5Icons
        this.init(height, icons)
    }

    private init(height: number, icons: IconSet) {
        const container = document.createElement('div')
        container.className = 'wysiwyg-container'
        container.style.height = `${height}px`
        this.element.parentNode?.insertBefore(container, this.element)

        const loading = document.createElement('div')
        loading.className = 'wysiwyg-loading'
        loading.innerHTML = icons.loading
        container.appendChild(loading)

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
            .ProseMirror { outline: none; min-height: 100%; white-space: pre-wrap; }
        `
        doc.head.appendChild(style)

        new TiptapEditor({
            element: doc.body,
            textarea: this.element,
            toolbarElement: toolbar,
            onUpdate: () => this.onUpdate(),
            onReady: () => loading.remove(),
            icons: icons,
            modules: DefaultModules
        })
    }

    private onUpdate() {
        if (this.options === null) return

        const changeEvent = new ChangeEvent(this.element)
        changeEvent.dispatch()
    }
}
