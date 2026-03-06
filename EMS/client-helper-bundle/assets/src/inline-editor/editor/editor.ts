import {IframeContentChanged, IframeLoadMessage, IframeRequestInlineEdit} from "../types";
import {ApiService, RenderResponse} from "./api";
import {Messenger} from "./messenger";
import { init as initSidebarResize } from './sidebar-resize';

interface EditorOptions {
    baseUrl: string,
    iframe: HTMLIFrameElement
}

export class InlineEditor {
    private readonly api: ApiService
    private readonly messenger: Messenger
    private readonly baseUrl: string

    constructor(options: EditorOptions) {
        this.api = new ApiService({
            baseUrl: `${options.baseUrl}/api`,
            onRenderResponse: (response) => this.render(response)
        })
        this.messenger = new Messenger(options.iframe);
        this.baseUrl = options.baseUrl;

        this.setupListeners();
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

    private setupListeners() {
        this.messenger
            .on('IFRAME_LOAD', (msg) => this.onIframeLoad(msg))
            .on('IFRAME_UNLOAD', () => this.onIframeUnload())
            .on('IFRAME_REQUEST_INLINE_EDIT', (msg) => this.onIframeRequestInlineEdit(msg))
            .on('IFRAME_CONTENT_CHANGED', (msg) => this.onIframeContentChanged(msg))
    }

    private async onIframeLoad(msg: IframeLoadMessage) {
        const newUrl = `${this.baseUrl}${msg.path}`;
        document.title = `Inline Editor: ${msg.title}`;

        if (window.location.pathname !== newUrl) {
            window.history.replaceState({ path: msg.path }, '', newUrl);
        }

        const data = await this.api.init(msg.url, msg.elements);
        if (data.elements && data.elements.length > 0) {
            this.messenger.send({ type: 'EDITOR_ELEMENTS', selectors: data.elements });
        }

        initSidebarResize();
    }

    private onIframeUnload() {
        ['.editor-sidebar-content', '.editor-topbar'].forEach(s =>
            document.querySelector(s)?.replaceChildren()
        );
    }

    private onIframeContentChanged(msg: IframeContentChanged) {
        console.debug(msg.content);
    }

    private async onIframeRequestInlineEdit(msg: IframeRequestInlineEdit) {
        await this.api.draft(msg.element);

        this.messenger.send({ type: 'EDITOR_INLINE_EDIT', element: msg.element });
    }
}