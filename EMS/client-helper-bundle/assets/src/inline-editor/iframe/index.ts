import { init as initGo2editor } from "./go2editor";
import { Messenger } from "./messenger";

export function initIframe() {
    if (window.self === window.top) {
        initGo2editor();
        return;
    }

    const messenger = new Messenger();

    messenger.on((data) => {
        if (data.type === 'EDITOR_TOGGLE_EDIT') {
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