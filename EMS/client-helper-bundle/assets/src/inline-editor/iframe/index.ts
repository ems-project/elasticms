import { Messenger } from "./messenger";
import { ElementInfo } from "../types"
import { NavigationObserver } from "./navigation";

const messenger = new Messenger();
const iframe = window.frameElement as HTMLIFrameElement | null;
const locationPrefix = iframe?.dataset.prefix ?? '';


export function initIframe()
{
    sendLoadMessage();

    new NavigationObserver({
        onUpdate: (url) => sendLoadMessage(url),
        onLeave: () => messenger.send({ type: 'IFRAME_UNLOAD' }),
    });
}

function sendLoadMessage(url: string = window.location.href) {
    const loc = new URL(url);
    const path = loc.pathname;

    if (!path.startsWith(locationPrefix)) {
        console.warn(`Invalid path, does not start with prefix: ${path}`);
        return;
    }

    let realPath = path.slice(locationPrefix.length);
    if (!realPath.startsWith('/')) {
        realPath = '/' + realPath;
    }

    messenger.send({
        type: 'IFRAME_LOAD',
        url: url,
        path: realPath,
        elements: findElements()
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