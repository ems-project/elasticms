import './../../../../css/core/components/_wysiwyg_tiptap.scss'
import { WysiwygRevisionOptions } from './types.ts'

import { TiptapEditor } from '../tiptap/editor.ts'
import ChangeEvent from '../../events/changeEvent.ts'
import { ToolbarAction } from '../tiptap/types.ts'
import IconSource from '@tabler/icons/outline/code.svg?raw'
import IconMaximize from '@tabler/icons/outline/maximize.svg?raw'
import IconMinimize from '@tabler/icons/outline/minimize.svg?raw'

interface ConfigGroup {
    name: string
    groups: string[]
}

export default class Tiptap {
    element: HTMLTextAreaElement
    config: ConfigGroup[]
    private groupRegistry: Record<string, string[]> = {}
    options: WysiwygRevisionOptions | null

    isSourceView: boolean = false
    isMaximized: boolean = false

    constructor(element: HTMLTextAreaElement, options: WysiwygRevisionOptions | null) {
        this.element = element
        this.options = options

        this.config = [
            {
                name: 'default',
                groups: Object.keys(this.groupRegistry)
            }
        ]

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

        const tiptapEditor = new TiptapEditor({
            element: doc.body,
            textarea: this.element,
            toolbarElement: toolbar,
            toolbarConfig: {
                customActions: [
                    this.getSourceAction(),
                    this.getMaximizeAction(),
                ]
            }
        })

        tiptapEditor.tiptap.on('update', ({ editor }) => {
            this.element.value = editor.getHTML()
            if (this.options === null) return

            const changeEvent = new ChangeEvent(this.element)
            changeEvent.dispatch()
        })
    }

    private getSourceAction(): ToolbarAction
    {
        return {
            name: 'Source',
            group: 'mode',
            icon: IconSource,
            tooltip: 'Source Code',
            command: (e) => {
                if (!e.textarea || !e.toolbar) return

                const container = e.toolbar.container.closest('.wysiwyg-container')
                if (!container?.classList.contains('wysiwyg-container')) return

                this.isSourceView = !this.isSourceView
                container.classList.toggle('is-source-mode', this.isSourceView)

                if (this.isSourceView) {
                    e.textarea.value = e.tiptap.getHTML()
                    e.toolbar.setDisabled(true, ['Source', 'Maximize'])

                    console.debug(e.textarea.value)
                } else {
                    e.tiptap.commands.setContent(e.textarea.value)
                    e.toolbar.setDisabled(false, ['Source', 'Maximize'])
                }
            },
            isActive: () => this.isSourceView
        }
    }

    private getMaximizeAction(): ToolbarAction
    {
        return  {
            name: 'Maximize',
            group: 'tools',
            icon: IconMaximize,
            tooltip: 'Maximize',
            command: (e) => {
                if (!e.toolbar) return
                const container = e.toolbar.container.closest('.wysiwyg-container')
                if (!container?.classList.contains('wysiwyg-container')) return

                this.isMaximized = !this.isMaximized;

                document.body.classList.toggle('wysiwyg-maximized-active', this.isMaximized);
                container.classList.toggle('is-maximized', this.isMaximized);

                const btn = container.querySelector('[data-action="Maximize"]');
                if (btn) btn.innerHTML = this.isMaximized ? IconMinimize : IconMaximize;
            },
            isActive: () => this.isMaximized
        }
    }
}
