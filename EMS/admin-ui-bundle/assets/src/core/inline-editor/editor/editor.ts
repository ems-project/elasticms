import {
    IframeContentChangedMessage,
    IframeLoadMessage,
    IframeRequestEditMessage,
    InlineCollection
} from '../types'

import { ApiService, RenderResponse } from './api'
import { Messenger } from './messenger'
import { SidebarResizer } from './sidebar'

type EditorAction = 'close' | 'toggleSidebar' | 'discard' | 'save' | 'publish'

interface EditorOptions {
    baseUrl: string
    iframe: HTMLIFrameElement
    currentUrl: string
}

export class InlineEditor {
    private readonly api: ApiService
    private readonly messenger: Messenger
    private readonly baseUrl: string
    private readonly defaultTitle: string
    private collection: InlineCollection = {}
    private iframeUrl: string

    private readonly actions: Record<EditorAction, (element: HTMLElement) => void> = {
        close: () => {
            window.location.href = this.iframeUrl
        },
        discard: () => this.actionDiscard(),
        save: () => this.actionSave(),
        publish: () => this.actionPublish(),
        toggleSidebar: (element) => this.actionToggleSidebar(element)
    }

    constructor(options: EditorOptions) {
        this.api = new ApiService({
            onRenderResponse: (response) => this.render(response)
        })
        this.messenger = new Messenger(options.iframe)
        this.baseUrl = options.baseUrl
        this.defaultTitle = document.querySelector('.editor-title')?.innerHTML ?? ''
        this.iframeUrl = options.iframe.src

        this.setupListeners()
        this.setupSidebar()
    }

    private setupListeners() {
        document.addEventListener('click', (event) => this.onClick(event))

        this.messenger
            .on('IFRAME_LOAD', (msg) => this.onIframeLoad(msg))
            .on('IFRAME_UNLOAD', () => this.onIframeUnload())
            .on('IFRAME_REQUEST_EDIT', (msg) => this.onIframeRequestEdit(msg))
            .on('IFRAME_CONTENT_CHANGED', (msg) => this.onIframeContentChanged(msg))
    }

    private setupSidebar() {
        const container = document.querySelector('.editor-body') as HTMLElement
        const handle = document.querySelector('.editor-sidebar-resize-handle') as HTMLElement

        if (container && handle) {
            new SidebarResizer(container, handle)
        }
    }

    private async reload() {
        await this.api.init(this.collection)
    }

    private clear() {
        document
            .querySelectorAll<HTMLElement>('[data-editor-clear="true"]')
            .forEach((element) => element.replaceChildren())
    }

    private render(response: RenderResponse) {
        for (const selector in response.render) {
            const html = response.render[selector]
            const element = document.querySelector<HTMLElement>(selector)

            if (element && html) {
                element.innerHTML = html
            }
        }
    }

    private onClick(event: MouseEvent): void {
        const target = event.target as HTMLElement
        const element = target.closest<HTMLElement>('[data-editor-action]')
        if (!element) return

        const action = element.dataset.editorAction as EditorAction
        if (!action || !this.actions[action]) return

        this.actions[action](element)
    }

    private async onIframeLoad(msg: IframeLoadMessage) {
        const newUrl = `${this.baseUrl}${msg.path}`
        document.title = `Inline Editor: ${msg.title}`

        if (window.location.pathname !== newUrl) {
            window.history.replaceState({ path: msg.path }, '', newUrl)
        }

        if (Object.keys(msg.collection).length > 0) {
            const data = await this.api.init(msg.collection)
            if (data.elements && data.elements.length > 0) {
                this.messenger.send({ type: 'EDITOR_ELEMENTS', selectors: data.elements })
            }
        }

        this.collection = msg.collection
        this.iframeUrl = msg.url
    }

    private onIframeUnload() {
        const title = document.querySelector('.editor-title') as HTMLElement | null
        if (title) {
            title.textContent = this.defaultTitle
        }

        this.clear()
    }

    private async onIframeRequestEdit(msg: IframeRequestEditMessage) {
        const emsId = msg.element.emsId
        const elements = this.collection[msg.element.emsId]

        const response = await this.api.edit(emsId, elements)
        this.messenger.send({
            type: 'EDITOR_EDIT',
            element: msg.element,
            data: response.data
        })

        await this.reload()
    }

    private async onIframeContentChanged(msg: IframeContentChangedMessage) {
        await this.api.autoSave(msg.element, msg.content)
    }

    private async actionDiscard() {
        this.messenger.send({ type: 'EDITOR_DISCARD' })

        await this.api.discard(this.collection)
        this.clear()
        await this.reload()
    }

    private async actionSave() {
        this.messenger.send({ type: 'EDITOR_DISCARD' })

        this.clear()
        await this.reload()
    }

    private async actionPublish() {
        await this.api.publish(this.collection)
        this.messenger.send({ type: 'EDITOR_REFRESH' })
    }

    private actionToggleSidebar(button: HTMLElement) {
        const editorBody = document.querySelector('.editor-body') as HTMLElement
        const isHidden = editorBody.getAttribute('data-sidebar-hidden') === 'true'
        const newStatus = !isHidden

        editorBody.setAttribute('data-sidebar-hidden', String(newStatus))

        const icon = button.querySelector('i') as HTMLElement
        icon.classList.toggle('fa-angles-left', newStatus)
        icon.classList.toggle('fa-angles-right', !newStatus)
    }
}
