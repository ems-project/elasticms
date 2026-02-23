import {
    EditorToIframeMessage,
    IframeToEditorMessage,
    MESSAGE_SOURCE
} from '../messages';

type EventHandler = (message: EditorToIframeMessage) => void;

export class Messenger {
    private handlers: EventHandler[] = [];

    constructor() {
        window.addEventListener('message', this.handleMessage);
        window.addEventListener('load', () => {
            this.send({
                type: 'IFRAME_READY',
                url: window.location.href,
                title: document.title
            });
        });
    }

    private handleMessage = (event: MessageEvent) => {
        const data = event.data;
        if (typeof data !== 'object' || data === null || data.source !== MESSAGE_SOURCE) {
            return;
        }

        const message = event.data as EditorToIframeMessage;
        console.debug('Iframe received:', message);

        this.handlers.forEach((h) => h(message));
    }

    public send(message: IframeToEditorMessage) {
        window.parent.postMessage({ ...message, source: MESSAGE_SOURCE}, '*');
    }

    public on(handler: EventHandler) {
        this.handlers.push(handler);
    }
}