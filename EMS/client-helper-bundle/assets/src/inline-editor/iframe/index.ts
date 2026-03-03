import { init as initGo2editor } from "./go2editor";
import { Messenger } from "./messenger";
import { ElementInfo } from "../types"

export function initIframe() {
    if (window.self === window.top) {
        initGo2editor();
        return;
    }

    const messenger = new Messenger();

    window.addEventListener('load', () => {
        messenger.send({
            type: 'IFRAME_READY',
            url: window.location.href,
            elements: findElements()
        });
    });

    messenger.on((message) => {
        if (message.type === 'EDITOR_TOGGLE_EDIT') {
            toggleInlineEdit(true)
        }
    })
}

function toggleInlineEdit(enabled: boolean): void {
    document
    .querySelectorAll<HTMLElement>('.inline-edit-element')
        .forEach(el => {
            el.contentEditable = enabled.toString();
            el.classList.toggle('is-editing', enabled);
        });
}

function findElements(): ElementInfo[] {
    const result: ElementInfo[] = [];

    document.querySelectorAll<HTMLElement>('.inline-edit-element').forEach(el => {
        const { emsId, path } = el.dataset;
        if (!emsId || !path) return;

        result.push({
            emsId,
            path,
            tag: el.tagName.toLowerCase()
        });
    });

    return result;
}