import {
    EditorToIframeMessage,
    IframeToEditorMessage,
    MESSAGE_SOURCE
} from '../types';
import {debug} from "../logger";

type EventHandler = (message: IframeToEditorMessage) => void;

export class Messenger {
    private iframe: HTMLIFrameElement;
    private handlers: EventHandler[] = [];

    constructor(iframe: HTMLIFrameElement) {
        this.iframe = iframe;
        window.addEventListener('message', this.handleMessage);
    }

    private handleMessage = (event: MessageEvent) => {
        const data = event.data;
        if (typeof data !== 'object' || data === null || data.source !== MESSAGE_SOURCE) {
            return;
        }

        const message = event.data as IframeToEditorMessage;
        debug('Iframe received:', message);

        this.handlers.forEach((h) => h(message));
    }

    public send(message: EditorToIframeMessage) {
        this.iframe.contentWindow?.postMessage({ ...message, source: MESSAGE_SOURCE}, '*');
    }

    public on<K extends IframeToEditorMessage['type']>(
        type: K,
        handler: (message: Extract<IframeToEditorMessage, { type: K }>) => void
    ) {
        const wrapper: EventHandler = (msg) => {
            if (msg.type === type) {
                handler(msg as Extract<IframeToEditorMessage, { type: K }>);
            }
        };
        this.handlers.push(wrapper);
    }
}