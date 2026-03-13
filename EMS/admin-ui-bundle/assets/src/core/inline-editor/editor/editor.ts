import {
  IframeContentChangedMessage,
  IframeLoadMessage,
  IframeRequestEditMessage,
  IframeResponseContentMessage,
  InlineElement
} from '../types'
import { ApiService, RenderResponse } from './api'
import { Messenger } from './messenger'
import { SidebarResizer } from './sidebar'

type EditorAction = 'close' | 'toggleFullscreen' | 'toggleSidebar' | 'discard' | 'save'

interface EditorOptions {
  baseUrl: string
  iframe: HTMLIFrameElement
  currentUrl: string
}

interface EditState {
  type: 'edit'
  draftId: string
  inlineEdit: InlineElement
  action?: 'save'
}
interface IdleState {
  type: 'idle'
}

type EditorState = IdleState | EditState;

export class InlineEditor {
  private readonly api: ApiService
  private readonly messenger: Messenger
  private readonly baseUrl: string
  private readonly defaultTitle: string
  private state: EditorState = { type: 'idle' }
  private elements: InlineElement[] = [];
  private iframeUrl: string;

  private readonly actions: Record<EditorAction, (element: HTMLElement) => void> = {
    close: () => {
      window.location.href = this.iframeUrl
    },
    discard: () => this.actionDiscard(),
    save: () => this.actionSave(),
    toggleFullscreen: (element) => this.actionToggleFullscreen(element),
    toggleSidebar: (element) => this.actionToggleSidebar(element)
  }

  constructor(options: EditorOptions) {
    this.api = new ApiService({
      onRenderResponse: (response) => this.render(response)
    })
    this.messenger = new Messenger(options.iframe)
    this.baseUrl = options.baseUrl
    this.defaultTitle = document.querySelector('.editor-title')?.innerHTML ?? ''
    this.iframeUrl = options.iframe.src;

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
      .on('IFRAME_RESPONSE_CONTENT', (msg) => this.onIframeResponseContent(msg))
  }

  private setupSidebar() {
    const container = document.querySelector('.editor-body') as HTMLElement
    const handle = document.querySelector('.editor-sidebar-resize-handle') as HTMLElement

    if (container && handle) {
      new SidebarResizer(container, handle)
    }
  }

  private async reload() {
    this.clear()
    await this.api.init(this.elements)

    this.state = { type: 'idle' };
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

    const data = await this.api.init(msg.elements)
    if (data.elements && data.elements.length > 0) {
      this.elements = msg.elements.filter((element) => {
        return data.elements.includes(element.selector)
      })

      this.messenger.send({ type: 'EDITOR_ELEMENTS', selectors: data.elements })
    }

    this.iframeUrl = msg.url;
  }

  private onIframeUnload() {
    const title = document.querySelector('.editor-title') as HTMLElement | null
    if (title) {
      title.textContent = this.defaultTitle
    }

    this.clear()
  }

  private async onIframeRequestEdit(msg: IframeRequestEditMessage) {
    const response = await this.api.edit(msg.element)

    this.state = {
      type: 'edit',
      draftId: response.draftId,
      inlineEdit:  msg.element,
    }

    this.messenger.send({ type: 'EDITOR_INLINE_EDIT', element: msg.element })
  }

  private async onIframeContentChanged(msg: IframeContentChangedMessage) {
    if (this.state.type !== 'edit') return;

    if (msg.element.selector !== this.state.inlineEdit.selector) return;

    await this.api.save(this.state.draftId, this.state.inlineEdit, msg.content);
  }

  private async onIframeResponseContent(msg: IframeResponseContentMessage)
  {
    if (this.state.type !== 'edit') return;
    if (this.state.action == 'save') {
      await this.api.save(this.state.draftId, this.state.inlineEdit, msg.content);
      this.messenger.send({ type: 'EDITOR_DISCARD' });
      await this.reload();
    }
  }

  private async actionDiscard() {
    if (this.state.type !== 'edit') return;

    this.messenger.send({ type: 'EDITOR_DISCARD' })
    await this.api.discard(this.state.draftId);
    await this.reload()
  }

  private async actionSave() {
    if (this.state.type !== 'edit') return;

    this.state.action = 'save';

    this.messenger.send({ type: 'EDITOR_REQUEST_CONTENT', element: this.state.inlineEdit });
  }

  private actionToggleFullscreen(button: HTMLElement) {
    const editor = document.querySelector('.editor') as HTMLElement;
    const icon = button.querySelector('i') as HTMLElement;

    if (!document.fullscreenElement) {
      editor.requestFullscreen().then(() => icon.classList.replace('fa-expand', 'fa-compress'));
    } else {
      document.exitFullscreen().then(() => icon.classList.replace('fa-compress', 'fa-expand'));
    }
  }

  private actionToggleSidebar(button: HTMLElement) {
    const editorBody = document.querySelector('.editor-body') as HTMLElement;
    const isHidden = editorBody.getAttribute('data-sidebar-hidden') === 'true';
    const newStatus = !isHidden;

    editorBody.setAttribute('data-sidebar-hidden', String(newStatus));

    const icon = button.querySelector('i') as HTMLElement;
    icon.classList.toggle('fa-angles-left', newStatus);
    icon.classList.toggle('fa-angles-right', !newStatus);
  }
}
