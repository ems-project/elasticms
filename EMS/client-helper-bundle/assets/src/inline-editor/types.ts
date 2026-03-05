export const MESSAGE_SOURCE = 'inline-editor';

export type IframeLoadMessage = {
    type: 'IFRAME_LOAD';
    url: string;
    path: string;
    elements: ElementInfo[];
};

export type EditorToIframeMessage =
    | { type: 'EDITOR_READY' };

export type IframeToEditorMessage =
    | IframeLoadMessage
    | { type: 'IFRAME_UNLOAD' }
;

export interface ElementInfo {
    emsId: string;
    path: string;
    tag: string;
}