import './../../../../css/core/components/_wysiwyg_tiptap.scss'
import tiptapIframeCss from './../../../../css/core/components/_wysiwyg_tiptap_iframe.scss?inline'

import { TiptapEditor } from '../tiptap/editor.ts'
import { TiptapModule } from '../tiptap/types.ts'
import ChangeEvent from '../../events/changeEvent.ts'
import IconSource from '@tabler/icons/outline/code.svg?raw'
import IconSourceOff from '@tabler/icons/outline/code-off.svg?raw'
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

        void this.init()
    }

    private async init() {
        const height = this.wysiwygOptions?.height ?? this.textarea.offsetHeight

        this.container.className = 'wysiwyg-container'
        this.textarea.parentNode?.insertBefore(this.container, this.textarea)

        const toolbar = document.createElement('div')
        toolbar.className = 'wysiwyg-toolbar'
        this.container.appendChild(toolbar)

        this.container.appendChild(this.textarea)
        this.textarea.classList.add('wysiwyg-source-view')

        const mount = await this.createIframe()

        const tiptapEditor = new TiptapEditor({
            content: this.textarea.value,
            element: mount,
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

    private createIframe(): Promise<HTMLElement> {
        return new Promise((resolve) => {
            const iframe = document.createElement('iframe')
            iframe.className = 'wysiwyg-iframe'

            iframe.addEventListener(
                'load',
                () => {
                    const doc = iframe.contentDocument as Document

                    const style = doc.createElement('style')
                    style.textContent = tiptapIframeCss
                    doc.head.appendChild(style)

                    if (this.wysiwygOptions.contentCss) {
                        const link = doc.createElement('link')
                        link.rel = 'stylesheet'
                        link.href = this.wysiwygOptions.contentCss
                        doc.head.appendChild(link)
                    }

                    while (doc.body.firstChild) doc.body.removeChild(doc.body.firstChild)

                    const mount = doc.createElement('div')
                    mount.className = 'wysiwyg-editor-root'
                    doc.body.appendChild(mount)

                    resolve(mount)
                },
                { once: true }
            )

            this.container.appendChild(iframe)
        })
    }

    private getSourceModule(): TiptapModule {
        return {
            toolbarGroup: 'mode',
            toolbar: [
                {
                    name: 'Source',
                    icon: IconSource,
                    tooltip: 'mode_source_code',
                    isActive: () => this.isSourceView,
                    command: (tiptapEditor) => {
                        this.isSourceView = !this.isSourceView
                        this.container.classList.toggle('is-source-mode', this.isSourceView)

                        const button = tiptapEditor.toolbar.getButton('Source')
                        if (button) {
                            button.innerHTML = this.isSourceView ? IconSourceOff : IconSource
                            button.title = this.isSourceView ? 'Hide Source' : 'Show Source'
                        }

                        if (this.isSourceView) {
                            this.textarea.value = tiptapEditor.getHTML()
                            tiptapEditor.toolbar.setDisabled(true, ['Source', 'Maximize'])
                        } else {
                            tiptapEditor.setContent(this.textarea.value)
                            tiptapEditor.toolbar.setDisabled(false, ['Source', 'Maximize'])
                        }
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
                    tooltip: 'tools_maximize',
                    isActive: () => this.isMaximized,
                    command: (tiptapEditor) => {
                        this.isMaximized = !this.isMaximized

                        document.body.classList.toggle('wysiwyg-maximized-active', this.isMaximized)
                        this.container.classList.toggle('is-maximized', this.isMaximized)

                        const button = tiptapEditor.toolbar.getButton('Maximize')
                        if (button) {
                            button.innerHTML = this.isMaximized ? IconMinimize : IconMaximize
                            button.title = this.isMaximized ? 'Minimize' : 'Maximize'
                        }

                        tiptapEditor.toolbar.update()
                    }
                }
            ]
        }
    }
}
