export const MESSAGE_SOURCE = 'inline-editor';

export type EditorToIframeMessage =
    | { type: 'EDITOR_TOGGLE_EDIT' };

export type IframeToEditorMessage =
    | { type: 'IFRAME_READY'; url: string; title: string, editables: Editables[] };

export interface EditableElement {
    tag: string;
    emsId: string;
    path: string;
}

export interface Editables {
    emsId: string;
    elements: { tag: string; path: string }[];
}