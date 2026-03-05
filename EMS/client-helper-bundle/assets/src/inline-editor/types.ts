export const MESSAGE_SOURCE = 'inline-editor';

export type IframeLoadMessage = {
    type: 'IFRAME_LOAD';
    url: string;
    path: string;
    title: string;
    elements: InlineElement[];
};
export type IframeRequestDraft = {
    type: 'IFRAME_REQUEST_DRAFT';
    element: InlineElement;
}

export type EditorElements = {
    type: 'EDITOR_ELEMENTS',
    selectors: string[]
}

export type EditorToIframeMessage =
    | EditorElements;

export type IframeToEditorMessage =
    | IframeLoadMessage
    | { type: 'IFRAME_UNLOAD' }
    | IframeRequestDraft
;

export interface InlineElement {
    emsId: string;
    path: string;
    tag: string;
    selector: string;
}