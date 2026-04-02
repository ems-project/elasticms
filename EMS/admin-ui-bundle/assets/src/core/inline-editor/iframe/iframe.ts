import { EditorEditMessage, InlineCollection, InlineElement } from '../types'
import { Messenger } from '../iframe/messenger'
import { NavigationObserver } from './navigation'
import { TiptapEditor } from '../../components/tiptap/editor.ts'

interface IframeOptions {
    prefix: string
}

interface EditSession {
    element: HTMLElement
    originalContent: string
    observer?: MutationObserver
    tiptapEditor?: TiptapEditor
}

export class Iframe {
    private readonly messenger = new Messenger()
    private readonly prefix: string
    private inlineSelectors: string[] = []
    private activeSessions = new Map<string, EditSession>()
    private toolbar: HTMLElement

    constructor(options: IframeOptions) {
        this.prefix = options.prefix
        this.toolbar = window.parent.document.getElementById('editor-toolbar') as HTMLElement
        this.init()
    }

    private init() {
        this.setupNavigation()
        this.setupEventListeners()
        this.sendLoadMessage()
    }

    private setupNavigation() {
        new NavigationObserver({
            onUpdate: (url) => this.sendLoadMessage(url),
            onLeave: () => this.messenger.send({ type: 'IFRAME_UNLOAD' })
        })
    }

    private setupEventListeners() {
        document.addEventListener('click', (e) => this.onClick(e))

        this.messenger.on('EDITOR_ELEMENTS', (msg) => (this.inlineSelectors = msg.selectors))
        this.messenger.on('EDITOR_EDIT', (msg) => this.onEditorEdit(msg))
        this.messenger.on('EDITOR_DISCARD', () => this.onEditorDiscard())
        this.messenger.on('EDITOR_REFRESH', () => {
            this.toolbar.innerHTML = ''
            window.location.reload()
        })
    }

    private sendLoadMessage(url: string = window.location.href) {
        const path = new URL(url).pathname
        if (!path.startsWith(this.prefix)) return

        const normalizedPath = path.slice(this.prefix.length).replace(/^(?!\/)/, '/')
        const collection: InlineCollection = {}

        document
            .querySelectorAll<HTMLElement>('[data-ems-id][data-path][data-inline-id]')
            .forEach((el) => {
                const item = this.getInlineElement(el)
                if (item) (collection[item.emsId] ??= []).push(item)
            })

        this.messenger.send({
            type: 'IFRAME_LOAD',
            url,
            path: normalizedPath,
            title: document.title,
            collection
        })
    }

    private onClick(event: MouseEvent) {
        if (!this.inlineSelectors.length) return

        const target = event.target as HTMLElement
        const editableElement = target.closest(this.inlineSelectors.join(',')) as HTMLElement

        if (!editableElement) return

        const inlineElement = this.getInlineElement(editableElement)
        if (!inlineElement) return

        const session = this.activeSessions.get(inlineElement.selector)
        if (session) {
            this.syncToolbar(session)
        } else {
            this.messenger.send({ type: 'IFRAME_REQUEST_EDIT', element: inlineElement })
        }
    }

    private onEditorEdit(msg: EditorEditMessage) {
        Object.entries(msg.data).forEach(([selector, draftContent]) => {
            if (!this.inlineSelectors.includes(selector)) return

            const element = document.querySelector(selector) as HTMLElement
            if (!element) return

            this.startEditSession(element, draftContent)

            if (selector === msg.element.selector) {
                this.syncToolbar(this.activeSessions.get(selector))
                element.focus()
            }
        })
    }

    private startEditSession(element: HTMLElement, draftData: string | null) {
        const inlineData = this.getInlineElement(element)
        if (!inlineData || this.activeSessions.has(inlineData.selector)) return

        const session: EditSession = {
            element,
            originalContent: element.innerHTML
        }

        element.classList.add('inline-is-editing')
        if (draftData) element.innerHTML = draftData

        const isWysiwyg = element.dataset.fieldType === 'wysiwyg'

        if (isWysiwyg) {
            session.tiptapEditor = this.initTiptap(element, inlineData)
        } else {
            session.observer = this.initNativeObserver(element, inlineData)
            element.contentEditable = 'true'
        }

        this.activeSessions.set(inlineData.selector, session)
    }

    private initTiptap(element: HTMLElement, info: InlineElement) {
        const editor = new TiptapEditor({ element })

        editor.tiptap.on('update', ({ editor }) => {
            this.notifyContentChange(info, editor.getHTML())
        })
        editor.tiptap.on('focus', () => {
            this.syncToolbar(this.activeSessions.get(info.selector))
        })

        return editor
    }

    private initNativeObserver(element: HTMLElement, info: InlineElement) {
        let debounce: number
        const observer = new MutationObserver(() => {
            window.clearTimeout(debounce)
            debounce = window.setTimeout(() => {
                this.notifyContentChange(info, element.innerHTML)
            }, 500)
        })

        observer.observe(element, { characterData: true, childList: true, subtree: true })
        return observer
    }

    private onEditorDiscard() {
        this.activeSessions.forEach((session) => {
            session.element.classList.remove('inline-is-editing')

            session.observer?.disconnect()
            session.tiptapEditor?.destroy()

            session.element.innerHTML = session.originalContent
            session.element.contentEditable = 'false'
        })
        this.activeSessions.clear()
        this.toolbar.innerHTML = ''
    }

    private notifyContentChange(element: InlineElement, content: string) {
        this.messenger.send({ type: 'IFRAME_CONTENT_CHANGED', element, content })
    }

    private syncToolbar(session?: EditSession) {
        if (session?.tiptapEditor) {
            session.tiptapEditor.attachToolbar(this.toolbar)
        } else {
            this.toolbar.innerHTML = ''
        }
    }

    private getInlineElement(element: HTMLElement): InlineElement | null {
        const { emsId, path, inlineId } = element.dataset
        if (!emsId || !path || !inlineId) return null

        const tag = element.tagName.toLowerCase()
        return {
            emsId,
            path,
            id: inlineId,
            tag,
            selector: `${tag}[data-inline-id="${inlineId}"]`
        }
    }
}
