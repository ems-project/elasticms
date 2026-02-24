import { init as initSidebarResize } from './sidebar-resize';
import {Messenger} from "./messenger";
import {IframeToEditorMessage} from "../types";

export function initEditor() {
    initSidebarResize();

    const iframe = document.getElementById('preview-iframe') as HTMLIFrameElement;
    const messenger = new Messenger(iframe);

    messenger.on(async (message) => {
        if (message.type === 'IFRAME_READY') {
            await render(message);
           toggleEdit();
        }
    });

    function toggleEdit(): void
    {
        const editButton = document.getElementById('btn-edit') as HTMLIFrameElement;
        editButton.addEventListener('click', function () {
            messenger.send({ type: 'EDITOR_TOGGLE_EDIT' })
        });
    }

    async function render(message: IframeToEditorMessage): Promise<void>
    {
        const renderUrl = <string>document.body.dataset.renderUrl;

        const response = await fetch(renderUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                title: message.title,
                editables: message.editables
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