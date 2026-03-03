import { init as initSidebarResize } from './sidebar-resize';
import {Messenger} from "./messenger";
import {IframeReadyMessage, IframeToEditorMessage} from "../types";

export function initEditor() {
    const iframe = document.getElementById('preview-iframe') as HTMLIFrameElement;
    const messenger = new Messenger(iframe);

    messenger.on('IFRAME_READY', async (msg) => {
        await render(msg);
        initSidebarResize();
    });

    async function render(message: IframeReadyMessage): Promise<void>
    {
        const renderUrl = <string>document.body.dataset.renderUrl;

        const response = await fetch(renderUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                url: message.url,
                elements: message.elements
            })
        });

        const json: Record<string, string> = await response.json();

        for (const selector in json) {
            const html = json[selector];
            const element = document.querySelector<HTMLElement>(selector);

            if (element && html) {
                element.innerHTML = html;
            }
        }
    }
}