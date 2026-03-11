import {
  IframeContentChanged,
  IframeLoadMessage,
  IframeRequestInlineEdit,
  InlineElement
} from '../types'
import { ApiService, RenderResponse } from './api'
import { Messenger } from './messenger'
import { SidebarResizer } from './sidebar'

type EditorAction = 'close' | 'discard'

interface EditorOptions {
  baseUrl: string
  iframe: HTMLIFrameElement
  currentUrl: string
}

interface EditorState {
  elements: InlineElement[]
  draftId?: string
  inlineEdit?: InlineElement
}

export class InlineEditor {
  private readonly api: ApiService
  private readonly messenger: Messenger
  private readonly iframe: HTMLIFrameElement
  private readonly baseUrl: string
  private readonly defaultTitle: string
  private readonly state: EditorState

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
      elements: []
    }

    this.setupListeners()
    this.setupSidebar()
  }

  private setupListeners() {
    document.addEventListener('click', (event) => this.onClick(event))

    this.messenger
      .on('IFRAME_LOAD', (msg) => this.onIframeLoad(msg))
      .on('IFRAME_UNLOAD', () => this.onIframeUnload())
      .on('IFRAME_REQUEST_INLINE_EDIT', (msg) => this.onIframeRequestInlineEdit(msg))
      .on('IFRAME_CONTENT_CHANGED', (msg) => this.onIframeContentChanged(msg))
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
      this.state.elements = msg.elements.filter((element) => {
        return data.elements.includes(element.selector)
      })

      this.messenger.send({ type: 'EDITOR_ELEMENTS', selectors: data.elements })
    }
  }

  private async reload() {
    this.clear()
    await this.api.init(this.state.elements)
  }

  private clear() {
    document
      .querySelectorAll<HTMLElement>('[data-editor-clear="true"]')
      .forEach((element) => element.replaceChildren())
  }

  private onIframeUnload() {
    const title = document.querySelector('.editor-title') as HTMLElement | null
    if (title) {
      title.textContent = this.defaultTitle
    }

    this.clear()
  }

  private onIframeContentChanged(msg: IframeContentChanged) {
    console.debug(msg.content)
  }

  private async onIframeRequestInlineEdit(msg: IframeRequestInlineEdit) {
    const response = await this.api.edit(msg.element)

    this.state.inlineEdit = msg.element
    this.state.draftId = response.draftId

    this.messenger.send({ type: 'EDITOR_INLINE_EDIT', element: msg.element })
  }

  private async actionDiscard() {
    const inlineEdit = this.state.inlineEdit ?? null;
    if (null === inlineEdit) return;

    this.messenger.send({ type: 'EDITOR_DISCARD' })

    const draftId = this.state.draftId ?? null;
    if (draftId) {
      await this.api.discard(draftId);
    }

    await this.reload()
  }
}
