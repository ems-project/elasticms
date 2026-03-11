import { EditorElementsMessage, EditorInlineEditMessage, InlineElement } from '../types'
import { Messenger } from '../iframe/messenger'
import { NavigationObserver } from './navigation'

interface IframeOptions {
  prefix: string
}

export class Iframe {
  private readonly messenger: Messenger
  private readonly prefix: string
  private inlineEditElement: HTMLElement | null = null

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

    this.messenger.on('EDITOR_ELEMENTS', (msg) => this.onEditorElements(msg))
    this.messenger.on('EDITOR_INLINE_EDIT', (msg) => this.onEditorInlineEdit(msg))
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

    this.messenger.send({
      type: 'IFRAME_LOAD',
      url: url,
      path: realPath,
      title: document.title,
      elements: this.findInlineElements()
    })
  }

  private onEditorElements(msg: EditorElementsMessage) {
    msg.selectors.forEach((selector) => {
      const element = document.querySelector<HTMLElement>(selector)
      if (element) {
        this.setupInlineEdit(element)
      }
    })
  }

  private onEditorInlineEdit(msg: EditorInlineEditMessage) {
    const element = document.querySelector(msg.element.selector) as HTMLElement | null
    if (null === element) return

    this.inlineEditElement = element

    element.contentEditable = 'true'
    element.focus()
    element.classList.add('inline-is-editing')

    let debounceTimer: number | undefined
    const activeObserver = new MutationObserver(() => {
      clearTimeout(debounceTimer)
      debounceTimer = window.setTimeout(() => {
        this.messenger.send({
          type: 'IFRAME_CONTENT_CHANGED',
          element: msg.element,
          content: element.innerHTML
        })
      }, 500)
    })

    activeObserver.observe(element, {
      characterData: true,
      childList: true,
      subtree: true
    })
  }

  private setupInlineEdit(element: HTMLElement) {
    const inlineElement = this.getInlineElement(element)
    if (null === inlineElement) return

    element.addEventListener('click', (e) => {
      e.preventDefault()

      if (null === this.inlineEditElement) {
        this.messenger.send({
          type: 'IFRAME_REQUEST_INLINE_EDIT',
          element: inlineElement
        })
      }
    })
  }

  private findInlineElements(): InlineElement[] {
    const inlineElements: InlineElement[] = []
    const query = '[data-ems-id][data-path][data-inline-id]'

    document.querySelectorAll<HTMLElement>(query).forEach((element) => {
      const inlineElement = this.getInlineElement(element)
      if (inlineElement) {
        inlineElements.push(inlineElement)
      }
    })

    return inlineElements
  }

  private getInlineElement(element: HTMLElement): InlineElement | null {
    const { emsId, path, inlineId } = element.dataset
    if (!emsId || !path || !inlineId) return null

    const tag = element.tagName.toLowerCase()

    return {
      emsId: emsId,
      path: path,
      tag: tag,
      selector: `${tag}[data-inline-id="${inlineId}"]`
    }
  }
}
