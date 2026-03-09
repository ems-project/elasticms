import {IframeContentChanged, IframeLoadMessage, IframeRequestInlineEdit} from "../types";
import {ApiService, RenderResponse} from "./api";
import {Messenger} from "./messenger";
import {SidebarResizer} from './sidebar';

interface EditorOptions {
    baseUrl: string,
    iframe: HTMLIFrameElement,
    currentUrl: string
}

export class InlineEditor {
    private readonly api: ApiService
    private readonly messenger: Messenger
    private readonly baseUrl: string
    private currentUrl: string
    private readonly defaultTitle: string;

    constructor(options: EditorOptions) {
        this.api = new ApiService({
            baseUrl: `${options.baseUrl}/api`,
            onRenderResponse: (response) => this.render(response)
        })
        this.messenger = new Messenger(options.iframe);
        this.baseUrl = options.baseUrl;
        this.currentUrl = options.currentUrl;
        this.defaultTitle = document.querySelector('.editor-title')?.innerHTML ?? '';

        this.setupListeners();
    }

    private setupListeners() {
        this.messenger
            .on('IFRAME_LOAD', (msg) => this.onIframeLoad(msg))
            .on('IFRAME_UNLOAD', () => this.onIframeUnload())
            .on('IFRAME_REQUEST_INLINE_EDIT', (msg) => this.onIframeRequestInlineEdit(msg))
            .on('IFRAME_CONTENT_CHANGED', (msg) => this.onIframeContentChanged(msg))

        document.addEventListener('click', (event) => this.onClick(event));
    }

    private render(response: RenderResponse) {
        for (const selector in response.render) {
            const html = response.render[selector];
            const element = document.querySelector<HTMLElement>(selector);

            if (element && html) {
                element.innerHTML = html;
            }
        }
    }

    private setupSidebar() {
        const container = document.querySelector('.editor-body') as HTMLElement;
        const handle = document.querySelector('.editor-sidebar-resize-handle') as HTMLElement;

        if (container && handle) {
            new SidebarResizer(container, handle);
        }
    }

    private onClick(event: PointerEvent): void {
        const target = event.target as HTMLElement;
        const element = target.closest<HTMLElement>('[data-editor-action]');
        if (!element) return;

        const action = element.dataset.editorAction;

        switch (action) {
            case 'close':
                window.location.href = this.currentUrl;
                break;
            default:
                throw new Error('Invalid action');
        }
    }

    private async onIframeLoad(msg: IframeLoadMessage) {
        this.currentUrl = msg.url;

        const newUrl = `${this.baseUrl}${msg.path}`;
        document.title = `Inline Editor: ${msg.title}`;

        if (window.location.pathname !== newUrl) {
            window.history.replaceState({ path: msg.path }, '', newUrl);
        }

        const data = await this.api.init(msg.url, msg.elements);
        if (data.elements && data.elements.length > 0) {
            this.messenger.send({ type: 'EDITOR_ELEMENTS', selectors: data.elements });
        }

        this.setupSidebar();
    }

    private onIframeUnload() {
        document.querySelector('.editor-sidebar-content')?.replaceChildren();

        const title = document.querySelector('.editor-title') as HTMLElement | null;
        if (title) {
            title.textContent = this.defaultTitle;
        }
    }

    private onIframeContentChanged(msg: IframeContentChanged) {
        console.debug(msg.content);
    }

    private async onIframeRequestInlineEdit(msg: IframeRequestInlineEdit) {
        await this.api.draft(msg.element);

        this.messenger.send({ type: 'EDITOR_INLINE_EDIT', element: msg.element });
    }
}