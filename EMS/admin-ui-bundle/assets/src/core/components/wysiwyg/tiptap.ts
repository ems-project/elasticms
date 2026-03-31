import './tiptap.css'
import { WysiwygRevisionOptions } from './types.ts'

import { TiptapEditor } from '../tiptap/editor.ts'
import ChangeEvent from '../../events/changeEvent.ts'

interface ConfigGroup {
    name: string
    groups: string[]
}

export default class Tiptap {
    element: HTMLTextAreaElement
    config: ConfigGroup[]
    private groupRegistry: Record<string, string[]> = {}
    options: WysiwygRevisionOptions | null

    constructor(element: HTMLTextAreaElement, options: WysiwygRevisionOptions | null) {
        this.element = element
        this.options = options

        this.config = [
            {
                name: 'default',
                groups: Object.keys(this.groupRegistry)
            }
        ]

        console.debug(this.element.offsetHeight)

        this.init()
    }

    private init() {
        const height = this.options?.height ?? this.element.offsetHeight

        const container = document.createElement('div')
        container.className = 'wysiwyg-container'

        this.element.parentNode?.insertBefore(container, this.element)

        const toolbar = document.createElement('div')
        toolbar.className = 'wysiwyg-toolbar'
        container.appendChild(toolbar)

        container.appendChild(this.element)
        this.element.classList.add('wysiwyg-source-view')

        const iframe = document.createElement('iframe')
        iframe.className = 'wysiwyg-iframe'
        iframe.style.height = `${height}px`
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
            onUpdate: () => this.onUpdate()
        })
    }

    private onUpdate() {
        if (this.options === null) return

        const changeEvent = new ChangeEvent(this.element)
        changeEvent.dispatch()
    }
}
