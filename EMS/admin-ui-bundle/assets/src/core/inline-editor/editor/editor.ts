import { IframeContentChanged, IframeLoadMessage, IframeRequestInlineEdit } from '../types'
import { ApiService, RenderResponse } from './api'
import { Messenger } from './messenger'
import { SidebarResizer } from './sidebar'

type EditorAction = 'close' | 'discard'

interface EditorOptions {
  baseUrl: string
  iframe: HTMLIFrameElement
  currentUrl: string
}

interface IdleState {
  type: 'idle'
  url: string
}

interface LoadingState {
  type: 'loading'
}

interface EditState {
  type: 'edit'
  draftId: string
}

type EditorState = IdleState | LoadingState | EditState

export class InlineEditor {
  private readonly api: ApiService
  private readonly messenger: Messenger
  private readonly iframe: HTMLIFrameElement
  private readonly baseUrl: string
  private readonly defaultTitle: string

  private state: EditorState

  private readonly actions: Record<EditorAction, (element: HTMLElement) => void> = {
    close: () => {
      window.location.href = this.iframe.src
    },
    discard: () => this.actionDiscard()
  }

  constructor(options: EditorOptions) {
    this.api = new ApiService({
      onRenderResponse: (response) => this.render(response)
    })
    this.messenger = new Messenger(options.iframe)
    this.iframe = options.iframe
    this.baseUrl = options.baseUrl
    this.defaultTitle = document.querySelector('.editor-title')?.innerHTML ?? ''

    this.state = {
      type: 'idle',
      url: options.currentUrl
    }

    this.setupListeners()
  }

  private setupListeners() {
    this.messenger
      .on('IFRAME_LOAD', (msg) => this.onIframeLoad(msg))
      .on('IFRAME_UNLOAD', () => this.onIframeUnload())
      .on('IFRAME_REQUEST_INLINE_EDIT', (msg) => this.onIframeRequestInlineEdit(msg))
      .on('IFRAME_CONTENT_CHANGED', (msg) => this.onIframeContentChanged(msg))

    document.addEventListener('click', (event) => this.onClick(event))
  }

  private setupSidebar() {
    const container = document.querySelector('.editor-body') as HTMLElement
    const handle = document.querySelector('.editor-sidebar-resize-handle') as HTMLElement

    if (container && handle) {
      new SidebarResizer(container, handle)
    }
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

    const data = await this.api.init(msg.elements)
    if (data.elements && data.elements.length > 0) {
      this.messenger.send({ type: 'EDITOR_ELEMENTS', selectors: data.elements })
    }

    this.setupSidebar()

    this.state = {
      type: 'idle',
      url: msg.url
    }
  }

  private onIframeUnload() {
    this.state = { type: 'loading' }

    const title = document.querySelector('.editor-title') as HTMLElement | null
    if (title) {
      title.textContent = this.defaultTitle
    }

    document
      .querySelectorAll<HTMLElement>('[data-editor-clear="true"]')
      .forEach((element) => element.replaceChildren())
  }

  private onIframeContentChanged(msg: IframeContentChanged) {
    console.debug(msg.content)
  }

  private async onIframeRequestInlineEdit(msg: IframeRequestInlineEdit) {
    const response = await this.api.edit(msg.element)

    this.state = {
      type: 'edit',
      draftId: response.draftId
    }

    this.messenger.send({ type: 'EDITOR_INLINE_EDIT', element: msg.element })
  }

  private async actionDiscard() {
    if (this.state.type !== 'edit') {
      return
    }

   // await this.api.discard(this.state.draftId);


  }
}
