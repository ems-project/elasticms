import './../../../../css/core/components/_wysiwyg_tiptap.scss'
import tiptapIframeCss from './../../../../css/core/components/_wysiwyg_tiptap_iframe.scss?inline'

import { TiptapEditor } from '../tiptap/editor.ts'
import { TiptapModule } from '../tiptap/types.ts'
import ChangeEvent from '../../events/changeEvent.ts'
import IconSource from '@tabler/icons/outline/code.svg?raw'
import IconMaximize from '@tabler/icons/outline/arrows-maximize.svg?raw'
import IconMinimize from '@tabler/icons/outline/arrows-minimize.svg?raw'
import { getWysiwygOptions, getWysiwygProfile, WysiwygOptions } from './wysiwyg.ts'

export default class Tiptap {
    textarea: HTMLTextAreaElement

    isSourceView: boolean = false
    isMaximized: boolean = false
    container: HTMLDivElement
    wysiwygOptions: WysiwygOptions

    constructor(element: HTMLTextAreaElement) {
        this.textarea = element
        this.container = document.createElement('div')
        this.wysiwygOptions = getWysiwygOptions(element)

        this.init()
    }

    private init() {
        const height = this.wysiwygOptions?.height ?? this.textarea.offsetHeight

        this.container.className = 'wysiwyg-container'
        this.textarea.parentNode?.insertBefore(this.container, this.textarea)

        const toolbar = document.createElement('div')
        toolbar.className = 'wysiwyg-toolbar'
        this.container.appendChild(toolbar)

        this.container.appendChild(this.textarea)
        this.textarea.classList.add('wysiwyg-source-view')

        const iframe = this.createIframe()

        const tiptapEditor = new TiptapEditor({
            content: this.textarea.value,
            element: iframe.body,
            toolbarElement: toolbar,
            customModules: [this.getSourceModule(), this.getMaximizeModule()],
            wysiwygProfile: this.wysiwygOptions.inRevision ? getWysiwygProfile() : null,
            wysiwygOptions: this.wysiwygOptions
        })

        const toolbarHeight = toolbar.offsetHeight || 0
        this.container.style.height = `${height + toolbarHeight}px`

        tiptapEditor.tiptap.on('update', () => {
            this.textarea.value = tiptapEditor.getHTML()

            if (this.wysiwygOptions.inRevision) {
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
        style.textContent = tiptapIframeCss
        doc.head.appendChild(style)

        if (this.wysiwygOptions.contentCss) {
            const linkContentCSS = doc.createElement('link')
            linkContentCSS.rel = 'stylesheet'
            linkContentCSS.href = this.wysiwygOptions.contentCss
            doc.head.appendChild(linkContentCSS)
        }

        return doc
    }

    private getSourceModule(): TiptapModule {
        return {
            toolbarGroup: 'mode',
            toolbar: [
                {
                    name: 'Source',
                    icon: IconSource,
                    tooltip: 'Source Code',
                    isActive: () => this.isSourceView,
                    command: (tiptapEditor) => {
                        this.isSourceView = !this.isSourceView
                        this.container.classList.toggle('is-source-mode', this.isSourceView)

                        if (this.isSourceView) {
                            this.textarea.value = tiptapEditor.getHTML()
                            tiptapEditor.toolbar.setDisabled(true, ['Source', 'Maximize'])
                        } else {
                            tiptapEditor.setContent(this.textarea.value)
                            tiptapEditor.toolbar.setDisabled(false, ['Source', 'Maximize'])
                        }
                        tiptapEditor.toolbar.update()
                    }
                }
            ]
        }
    }

    private getMaximizeModule(): TiptapModule {
        return {
            toolbarGroup: 'tools',
            toolbar: [
                {
                    name: 'Maximize',
                    icon: IconMaximize,
                    tooltip: 'Maximize',
                    isActive: () => this.isMaximized,
                    command: (tiptapEditor) => {
                        this.isMaximized = !this.isMaximized

                        document.body.classList.toggle('wysiwyg-maximized-active', this.isMaximized)
                        this.container.classList.toggle('is-maximized', this.isMaximized)

                        const button = tiptapEditor.toolbar.getButton('Maximize')
                        if (button)
                            button.innerHTML = this.isMaximized ? IconMinimize : IconMaximize

                        tiptapEditor.toolbar.update()
                    }
                }
            ]
        }
    }
}
