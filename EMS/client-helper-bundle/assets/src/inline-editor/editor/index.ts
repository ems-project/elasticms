import { init as initSidebarResize } from './sidebar-resize';
import {Messenger} from "./messenger";
import {IframeLoadMessage} from "../types";

const iframe = document.getElementById('preview-iframe') as HTMLIFrameElement;
const baseUrl = <string>document.body.dataset.baseUrl;
const messenger = new Messenger(iframe);

export function initEditor() {
    messenger.on('IFRAME_LOAD', async (msg) => {
        const newUrl = `${baseUrl}${msg.path}`;
        document.title = `Inline Editor: ${msg.title}`;

        if (window.location.pathname !== newUrl) {
            window.history.replaceState({ path: msg.path }, '', newUrl);
        }

        await render(msg);
        initSidebarResize();
    });

    messenger.on('IFRAME_UNLOAD', (msg) => {
        ['.editor-sidebar-content', '.editor-topbar'].forEach(s =>
            document.querySelector(s)?.replaceChildren()
        );
    })
}

async function render(message: IframeLoadMessage): Promise<void>
{
    const response = await fetch(baseUrl + '/render', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
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