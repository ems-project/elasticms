import { Messenger } from "./messenger";
import {EditorElements, InlineElement} from "../types"
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

    messenger.on('EDITOR_ELEMENTS', (message) => onEditorElements(message))
}

function setupInlineEdit(element: HTMLElement) {
    const inlineElement = getInlineElement(element);
    if (null === inlineElement) return;

    element.addEventListener('click', (e) => {
        e.preventDefault();
        messenger.send({
            type: 'IFRAME_REQUEST_DRAFT',
            element: inlineElement
        });
    })
}

function onEditorElements(message: EditorElements) {
    message.selectors.forEach(selector => {
        const element = document.querySelector<HTMLElement>(selector);
        if (element) {
            setupInlineEdit(element);
        }
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
        title: document.title,
        elements: findInlineElements()
    });
}

function findInlineElements(): InlineElement[] {
    const inlineElements: InlineElement[] = [];
    const query = '[data-ems-id][data-path][data-inline-id]';

    document.querySelectorAll<HTMLElement>(query).forEach(element => {
        const inlineElement = getInlineElement(element);
        if (inlineElement) {
            inlineElements.push(inlineElement);
        }
    });

    return inlineElements;
}

function getInlineElement(element: HTMLElement): InlineElement | null {
    const { emsId, path, inlineId } = element.dataset;
    if (!emsId || !path || !inlineId) return null;

    const tag = element.tagName.toLowerCase();

    return {
        emsId: emsId,
        path: path,
        tag: tag,
        selector: `${tag}[data-inline-id="${inlineId}"]`
    }
}