import { EditorEditMessage, InlineCollection, InlineElement } from '../types'
import { Messenger } from '../iframe/messenger'
import { NavigationObserver } from './navigation'
import { TiptapEditor } from '../../components/tiptap/editor.ts'
import { getWysiwygProfile } from '../../components/wysiwyg/wysiwyg.ts'

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

    private focusSession: EditSession | null = null
    private activeSessions = new Map<string, EditSession>()
    private readonly toolbar: HTMLElement

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
        document.addEventListener('focusin', (e) => this.onFocus(e))

        this.messenger.on('EDITOR_ELEMENTS', (msg) => (this.inlineSelectors = msg.selectors))
        this.messenger.on('EDITOR_EDIT', (msg) => this.onEditorEdit(msg))
        this.messenger.on('EDITOR_DISCARD', () => this.onEditorDiscard())
        this.messenger.on('EDITOR_REFRESH', () => this.onEditorRefresh())
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

    private onFocus(event: FocusEvent) {
        this.toolbar.innerHTML = ''

        const target = event.target as HTMLElement
        const inlineElement = this.getTargetInlineElement(target)
        const session = inlineElement ? this.activeSessions.get(inlineElement.selector) : null

        if (!session) return

        this.focusSession = session
        if (target.dataset.fieldType === 'wysiwyg') {
            session.tiptapEditor?.attachToolbar(this.toolbar)
        }
    }

    private onClick(event: MouseEvent) {
        const target = event.target as HTMLElement
        const inlineElement = this.getTargetInlineElement(target)

        if (inlineElement && !this.activeSessions.has(inlineElement.selector)) {
            this.messenger.send({ type: 'IFRAME_REQUEST_EDIT', element: inlineElement })
            return
        }

        if (inlineElement) return

        const focusElement = this.focusSession?.element
        if (focusElement && target.contains(focusElement)) return

        this.toolbar.innerHTML = ''
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

    private onEditorEdit(msg: EditorEditMessage) {
        Object.entries(msg.data).forEach(([selector, draftContent]) => {
            if (!this.inlineSelectors.includes(selector)) return

            const element = document.querySelector(selector) as HTMLElement
            if (!element) return

            this.startEditSession(element, draftContent)

            if (selector === msg.element.selector) {
                element.focus()
            }
        })
    }

    private onEditorRefresh() {
        this.toolbar.innerHTML = ''
        window.location.reload()
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
        const editor = new TiptapEditor({
            element,
            content: element.innerHTML,
            wysiwygProfile: getWysiwygProfile(window.parent.document)
        })

        editor.tiptap.on('update', () => this.sendContentChanged(info, editor.getHTML()))

        return editor
    }

    private initNativeObserver(element: HTMLElement, info: InlineElement) {
        let debounce: number
        const observer = new MutationObserver(() => {
            window.clearTimeout(debounce)
            debounce = window.setTimeout(() => {
                this.sendContentChanged(info, element.innerHTML)
            }, 500)
        })

        observer.observe(element, { characterData: true, childList: true, subtree: true })
        return observer
    }

    private sendContentChanged(element: InlineElement, content: string) {
        this.messenger.send({ type: 'IFRAME_CONTENT_CHANGED', element, content })
    }

    private getTargetInlineElement(target: HTMLElement): InlineElement | null {
        if (!this.inlineSelectors.length) return null
        const selector = this.inlineSelectors.join(',')
        const editableElement = target.closest(selector) as HTMLElement
        return editableElement ? this.getInlineElement(editableElement) : null
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
