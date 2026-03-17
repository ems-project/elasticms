import { EditorEditMessage, InlineCollection, InlineElement } from '../types'
import { Messenger } from '../iframe/messenger'
import { NavigationObserver } from './navigation'

interface IframeOptions {
    prefix: string
}

interface EditSession {
    element: HTMLElement
    observer: MutationObserver
    originalContent: string
}

export class Iframe {
    private readonly messenger: Messenger
    private readonly prefix: string
    private inlineSelectors: string[] = []

    private activeSessions = new Map<string, EditSession>()

    constructor(options: IframeOptions) {
        this.messenger = new Messenger()
        this.prefix = options.prefix

        this.sendLoadMessage()
        this.setupListeners()
    }

    private setupListeners() {
        new NavigationObserver({
            onUpdate: (url) => this.sendLoadMessage(url),
            onLeave: () => this.messenger.send({ type: 'IFRAME_UNLOAD' })
        })

        document.addEventListener('click', (event) => this.onClick(event))

        this.messenger.on('EDITOR_ELEMENTS', (msg) => {
            this.inlineSelectors = msg.selectors
        })
        this.messenger.on('EDITOR_EDIT', (msg) => this.onEditorEdit(msg))
        this.messenger.on('EDITOR_DISCARD', () => this.onEditorDiscard())
        this.messenger.on('EDITOR_REFRESH', () => {
            window.location.reload()
        })
    }

    private sendLoadMessage(url: string = window.location.href) {
        const loc = new URL(url)
        const path = loc.pathname

        if (!path.startsWith(this.prefix)) {
            console.warn(`Invalid path, does not start with prefix: ${path}`)
            return
        }

        let realPath = path.slice(this.prefix.length)
        if (!realPath.startsWith('/')) {
            realPath = '/' + realPath
        }

        const collection: InlineCollection = {}
        document
            .querySelectorAll<HTMLElement>('[data-ems-id][data-path][data-inline-id]')
            .forEach((element) => {
                const item = this.getInlineElement(element)
                if (item) {
                    ;(collection[item.emsId] ??= []).push(item)
                }
            })

        this.messenger.send({
            type: 'IFRAME_LOAD',
            url: url,
            path: realPath,
            title: document.title,
            collection: collection
        })
    }

    private onClick(event: MouseEvent): void {
        const target = event.target as HTMLElement
        const selectors = this.inlineSelectors.join(',')

        const matchedElement = target.closest(selectors) as HTMLElement

        if (matchedElement) {
            const inlineElement = this.getInlineElement(matchedElement)
            if (null === inlineElement) return

            if (this.activeSessions.has(inlineElement.selector)) return

            this.messenger.send({
                type: 'IFRAME_REQUEST_EDIT',
                element: inlineElement
            })
        }
    }

    private onEditorEdit(msg: EditorEditMessage) {
        Object.entries(msg.data).forEach(([selector, data]) => {
            if (!this.inlineSelectors.includes(selector)) return

            const element = document.querySelector(selector) as HTMLElement | null
            if (null === element) return

            this.setupInlineEdit(element, data)

            if (selector === msg.element.selector) {
                element.focus()
            }
        })
    }

    private onEditorDiscard() {
        this.activeSessions.forEach((session) => {
            session.observer.disconnect()
            session.element.innerHTML = session.originalContent
            session.element.contentEditable = 'false'
            session.element.classList.remove('inline-is-editing')
        })

        this.activeSessions.clear()
    }

    private setupInlineEdit(element: HTMLElement, draftData: string | null = null) {
        const inlineElement = this.getInlineElement(element)
        if (null === inlineElement) return

        const id = inlineElement.selector

        if (!element || this.activeSessions.has(id)) return

        const originalContent = element.innerHTML
        element.contentEditable = 'true'
        element.classList.add('inline-is-editing')

        if (draftData) {
            element.innerHTML = draftData
        }

        let debounceTimer: number | undefined
        const observer = new MutationObserver(() => {
            clearTimeout(debounceTimer)
            debounceTimer = window.setTimeout(() => {
                this.messenger.send({
                    type: 'IFRAME_CONTENT_CHANGED',
                    element: inlineElement,
                    content: element.innerHTML
                })
            }, 500)
        })
        observer.observe(element, {
            characterData: true,
            childList: true,
            subtree: true
        })

        this.activeSessions.set(id, { element, observer, originalContent })
    }

    private getInlineElement(element: HTMLElement): InlineElement | null {
        const { emsId, path, inlineId } = element.dataset
        if (!emsId || !path || !inlineId) return null

        const tag = element.tagName.toLowerCase()

        return {
            emsId: emsId,
            path: path,
            id: inlineId,
            tag: tag,
            selector: `${tag}[data-inline-id="${inlineId}"]`
        }
    }
}
