import './../../../../css/core/components/_wysiwyg_tiptap.scss'
import tiptapIframeCss from './../../../../css/core/components/_wysiwyg_tiptap_iframe.scss?inline'

import { TiptapEditor } from '../Tiptap/Editor.ts'
import { TiptapModule } from '../Tiptap/Types.ts'
import ChangeEvent from '../../events/ChangeEvent.ts'
import IconSource from '@tabler/icons/outline/code.svg?raw'
import IconSourceOff from '@tabler/icons/outline/code-off.svg?raw'
import IconMaximize from '@tabler/icons/outline/arrows-maximize.svg?raw'
import IconMinimize from '@tabler/icons/outline/arrows-minimize.svg?raw'
import { getWysiwygOptions, getWysiwygProfile, WysiwygOptions } from './Wysiwyg.ts'
import CodeEditor from '../../plugins/codeEditor.ts'
import { escapeHtml } from '../Tiptap/Helper.ts'
import { html as beautifyHtml } from 'js-beautify'

export default class Tiptap {
    textarea: HTMLTextAreaElement

    isSourceView: boolean = false
    isMaximized: boolean = false
    container: HTMLDivElement
    wysiwygOptions: WysiwygOptions

    private codeEditor = new CodeEditor()
    private sourceContainer: HTMLDivElement = document.createElement('div')
    private iframeWrapper: HTMLDivElement = document.createElement('div')

    private resizeObserver = new ResizeObserver(() => {
        const pre = this.sourceContainer.querySelector('pre') as any
        pre?._aceEditor?.resize(true)
    })

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

        this.sourceContainer.className = 'wysiwyg-source-container'
        this.container.appendChild(this.sourceContainer)

        const mount = await this.createIframe()

        const tiptapEditor = new TiptapEditor({
            content: this.textarea.value,
            element: mount,
            noticeElement: this.iframeWrapper,
            toolbarElement: toolbar,
            customModules: [this.getSourceModule(), this.getMaximizeModule()],
            wysiwygProfile: this.wysiwygOptions.inRevision ? getWysiwygProfile() : null,
            wysiwygOptions: this.wysiwygOptions
        })

        const toolbarHeight = toolbar.offsetHeight || 0
        this.container.style.height = `${height + toolbarHeight}px`

        this.resizeObserver.observe(this.container)
        this.initResizeHandle()

        tiptapEditor.tiptap.on('update', () => {
            this.textarea.value = tiptapEditor.getHTML()

            if (this.wysiwygOptions.inRevision) {
                const changeEvent = new ChangeEvent(this.textarea)
                changeEvent.dispatch()
            }
        })
    }

    private initResizeHandle() {
        const handle = document.createElement('div')
        handle.className = 'wysiwyg-resize-handle'
        this.container.appendChild(handle)

        let startY = 0
        let startHeight = 0

        const onMouseMove = (e: MouseEvent) => {
            const delta = e.clientY - startY
            const newHeight = Math.max(150, startHeight + delta)
            this.container.style.height = `${newHeight}px`
        }

        const onMouseUp = () => {
            document.removeEventListener('mousemove', onMouseMove)
            document.removeEventListener('mouseup', onMouseUp)
            document.body.classList.remove('wysiwyg-resizing')
        }

        handle.addEventListener('mousedown', (e) => {
            if (this.isMaximized) return
            e.preventDefault()
            startY = e.clientY
            startHeight = this.container.getBoundingClientRect().height
            document.body.classList.add('wysiwyg-resizing')
            document.addEventListener('mousemove', onMouseMove)
            document.addEventListener('mouseup', onMouseUp)
        })
    }

    private createIframe(): Promise<HTMLElement> {
        return new Promise((resolve) => {
            this.iframeWrapper.className = 'wysiwyg-iframe-wrapper'
            this.container.appendChild(this.iframeWrapper)

            const iframe = document.createElement('iframe')
            iframe.className = 'wysiwyg-iframe'

            iframe.addEventListener(
                'load',
                () => {
                    const doc = iframe.contentDocument as Document
                    doc.documentElement.lang = this.wysiwygOptions.lang ?? 'en'

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

            this.iframeWrapper.appendChild(iframe)
        })
    }

    private getSourceModule(): TiptapModule {
        return {
            isEnabled: (profile) => profile.hasPlugin('sourcearea'),
            toolbar: {
                group: 'mode',
                items: [
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
                                button.title = tiptapEditor.trans(
                                    this.isSourceView ? 'mode_source_code_hide' : 'mode_source_code'
                                )
                            }

                            if (this.isSourceView) {
                                const html = beautifyHtml(tiptapEditor.getHTML(), {
                                    indent_size: 2
                                })
                                this.sourceContainer.innerHTML = `
                                    <div class="ems-code-editor" data-language="ace/mode/html" data-min-lines="1">
                                        <input type="hidden" value="${escapeHtml(html)}" />
                                        <pre>${escapeHtml(html)}</pre>
                                    </div>
                                `
                                void this.codeEditor.load(this.sourceContainer)
                                tiptapEditor.toolbar.setDisabled(true, ['Source', 'Maximize'])
                            } else {
                                const hiddenInput = this.sourceContainer.querySelector('input')
                                tiptapEditor.setContent(hiddenInput?.value ?? '')
                                this.sourceContainer.innerHTML = ''
                                tiptapEditor.toolbar.setDisabled(false, ['Source', 'Maximize'])
                            }
                        }
                    }
                ]
            }
        }
    }

    private getMaximizeModule(): TiptapModule {
        return {
            isEnabled: (profile) => profile.hasPlugin('maximize'),
            toolbar: {
                group: 'tools',
                items: [
                    {
                        name: 'Maximize',
                        icon: IconMaximize,
                        tooltip: 'tools_maximize',
                        isActive: () => this.isMaximized,
                        command: (tiptapEditor) => {
                            this.isMaximized = !this.isMaximized

                            document.body.classList.toggle(
                                'wysiwyg-maximized-active',
                                this.isMaximized
                            )
                            this.container.classList.toggle('is-maximized', this.isMaximized)

                            const button = tiptapEditor.toolbar.getButton('Maximize')
                            if (button) {
                                button.innerHTML = this.isMaximized ? IconMinimize : IconMaximize
                                button.title = tiptapEditor.trans(
                                    this.isMaximized ? 'tools_minimize' : 'tools_maximize'
                                )
                            }

                            tiptapEditor.toolbar.update()
                        }
                    }
                ]
            }
        }
    }
}
