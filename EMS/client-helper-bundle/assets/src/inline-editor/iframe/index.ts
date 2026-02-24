import { init as initGo2editor } from "./go2editor";
import { Messenger } from "./messenger";
import { Editables, EditableElement } from "../types"

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
            title: document.title,
            editables: findEditables()
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

function findEditables(): Editables[]
{
    const nodes = document.querySelectorAll<HTMLElement>('.inline-edit-element');
    const editableElements: EditableElement[] = Array.from(nodes)
        .filter((el): el is HTMLElement & { dataset: { emsId: string; path: string } } =>
            Boolean(el.dataset.emsId && el.dataset.path)
        )
        .map(el => ({
            tag: el.tagName,
            emsId: el.dataset.emsId,
            path: el.dataset.path
        }));

    const mapEditables = new Map<string, { tag: string; path: string }[]>();
    editableElements.forEach(el => {
        if (!mapEditables.has(el.emsId)) {
            mapEditables.set(el.emsId, []);
        }
        mapEditables.get(el.emsId)!.push({ tag: el.tag, path: el.path });
    })

    return Array.from(mapEditables.entries()).map(([emsId, elements]) => ({
        emsId,
        elements
    }));
}