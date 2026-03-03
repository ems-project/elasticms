export const MESSAGE_SOURCE = 'inline-editor';

export type IframeReadyMessage = {
    type: 'IFRAME_READY';
    url: string;
    elements: ElementInfo[];
};

export type EditorToIframeMessage =
    | { type: 'EDITOR_TOGGLE_EDIT' };

export type IframeToEditorMessage =
    | IframeReadyMessage;

export interface ElementInfo {
    emsId: string;
    path: string;
    tag: string;
}