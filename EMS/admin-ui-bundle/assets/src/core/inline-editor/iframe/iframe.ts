import { InlineElement } from '../types'
import { Messenger } from '../iframe/messenger'
import { NavigationObserver } from './navigation'

interface IframeOptions {
  prefix: string
}

export class Iframe {
  private readonly messenger: Messenger
  private readonly prefix: string
  private inlineSelectors: string[] = []

  private editObserver: MutationObserver | null = null;
  private editOriginalContent: string = '';
  private editElement: HTMLElement | null = null

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
       this.inlineSelectors = msg.selectors;
    })
    this.messenger.on('EDITOR_INLINE_EDIT', (msg) => {
      this.setupInlineEdit(msg.element);
    })
    this.messenger.on('EDITOR_DISCARD', () => {
      this.discardInlineEdit();
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

    this.messenger.send({
      type: 'IFRAME_LOAD',
      url: url,
      path: realPath,
      title: document.title,
      elements: this.findInlineElements()
    })
  }

  private onClick(event: MouseEvent): void {
    const target = event.target as HTMLElement

    if (target.matches(this.inlineSelectors.join(',')) && target !== this.editElement) {
      const inlineElement = this.getInlineElement(target);
      if (null === inlineElement) return;

      this.messenger.send({
        type: 'IFRAME_REQUEST_INLINE_EDIT',
        element: inlineElement
      });
    }
  }

  private setupInlineEdit(inlineElement: InlineElement)
  {
    const element = document.querySelector(inlineElement.selector) as HTMLElement | null;
    if (!element) return;

    if (this.editElement) this.discardInlineEdit();

    this.editElement = element;
    this.editOriginalContent = element.innerHTML;

    element.contentEditable = 'true';
    element.focus();
    element.classList.add('inline-is-editing');

    let debounceTimer: number | undefined
    this.editObserver = new MutationObserver(() => {
      clearTimeout(debounceTimer);
      debounceTimer = window.setTimeout(() => {
        this.messenger.send({
          type: 'IFRAME_CONTENT_CHANGED',
          element: inlineElement,
          content: element.innerHTML
        });
      }, 500);
    });
    this.editObserver.observe(element, {
      characterData: true,
      childList: true,
      subtree: true
    });
  }

  private discardInlineEdit()
  {
    if (!this.editElement) return;

    if (this.editObserver) {
      this.editObserver.disconnect();
      this.editObserver = null;
    }

    this.editElement.innerHTML = this.editOriginalContent;
    this.editElement.contentEditable = 'false';
    this.editElement.classList.remove('inline-is-editing');

    this.editElement = null;
    this.editOriginalContent = '';
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
