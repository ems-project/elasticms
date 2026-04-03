import './../../../../css/core/components/_wysiwyg_tiptap.scss'

import { TiptapEditor } from '../tiptap/editor.ts'
import { ToolbarAction } from '../tiptap/types.ts'
import ChangeEvent from '../../events/changeEvent.ts'
import IconSource from '@tabler/icons/outline/code.svg?raw'
import IconMaximize from '@tabler/icons/outline/maximize.svg?raw'
import IconMinimize from '@tabler/icons/outline/minimize.svg?raw'
import { getWysiwygOptions, getWysiwygProfile } from './wysiwyg.ts'

export default class Tiptap {
    textarea: HTMLTextAreaElement

    isSourceView: boolean = false
    isMaximized: boolean = false
    container: HTMLDivElement

    constructor(element: HTMLTextAreaElement) {
        this.textarea = element
        this.container = document.createElement('div')

        this.init()
    }

    private init() {
        const wysiwygOptions = getWysiwygOptions(this.textarea)
        const height = wysiwygOptions?.height ?? this.textarea.offsetHeight

        this.container.className = 'wysiwyg-container'
        this.textarea.parentNode?.insertBefore(this.container, this.textarea)

        const toolbar = document.createElement('div')
        toolbar.className = 'wysiwyg-toolbar'
        this.container.appendChild(toolbar)

        this.container.appendChild(this.textarea)
        this.textarea.classList.add('wysiwyg-source-view')

        const iframe = this.createIframe()

        const tiptapEditor = new TiptapEditor({
            element: iframe.body,
            content: this.textarea.value,
            toolbarElement: toolbar,
            toolbarConfig: {
                customActions: [this.getSourceAction(), this.getMaximizeAction()],
                wysiwygProfile: wysiwygOptions.inRevision ? getWysiwygProfile() : null
            }
        })

        const toolbarHeight = toolbar.offsetHeight || 0
        this.container.style.height = `${height + toolbarHeight}px`

        tiptapEditor.tiptap.on('update', ({ editor }) => {
            this.textarea.value = editor.getHTML()

            if (wysiwygOptions.inRevision) {
                const changeEvent = new ChangeEvent(this.textarea)
                changeEvent.dispatch()
            }
        })
    }

    private createIframe(): Document {
        const iframe = document.createElement('iframe')
        iframe.className = 'wysiwyg-iframe'
        this.container.appendChild(iframe)

        const doc = iframe.contentDocument as Document
        const style = doc.createElement('style')
        style.textContent = `
            html {
                height: 100%;
            }
            
            body {
                margin: 0;
                padding: 0;
                min-height: 100%;
                font-family: sans-serif;
                overflow-y: auto;
            }
            
            .ProseMirror {
                outline: none;
                white-space: pre-wrap;
                box-sizing: border-box;
                padding: 10px;
                min-height: 100%;
            }
        `
        doc.head.appendChild(style)

        return doc
    }

    private getSourceAction(): ToolbarAction {
        return {
            name: 'Source',
            group: 'mode',
            icon: IconSource,
            tooltip: 'Source Code',
            command: (e) => {
                this.isSourceView = !this.isSourceView
                this.container.classList.toggle('is-source-mode', this.isSourceView)

                if (this.isSourceView) {
                    this.textarea.value = e.tiptap.getHTML()
                    e.toolbar.setDisabled(true, ['Source', 'Maximize'])
                } else {
                    e.tiptap.commands.setContent(this.textarea.value)
                    e.toolbar.setDisabled(false, ['Source', 'Maximize'])
                }

                e.toolbar.update()
            },
            isActive: () => this.isSourceView
        }
    }

    private getMaximizeAction(): ToolbarAction {
        return {
            name: 'Maximize',
            group: 'tools',
            icon: IconMaximize,
            tooltip: 'Maximize',
            command: (e) => {
                this.isMaximized = !this.isMaximized

                document.body.classList.toggle('wysiwyg-maximized-active', this.isMaximized)
                this.container.classList.toggle('is-maximized', this.isMaximized)

                const button = e.toolbar.getButton('Maximize')
                if (button) button.innerHTML = this.isMaximized ? IconMinimize : IconMaximize

                e.toolbar.update()
            },
            isActive: () => this.isMaximized
        }
    }
}
