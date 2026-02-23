export const MESSAGE_SOURCE = 'inline-editor';

export type EditorToIframeMessage =
    | { type: 'EDITOR_INIT' }
    | { type: 'EDITOR_TOGGLE_EDIT' };

export type IframeToEditorMessage =
    | { type: 'IFRAME_READY'; url: string; title: string };