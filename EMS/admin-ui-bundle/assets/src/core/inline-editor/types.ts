export const MESSAGE_SOURCE = 'inline-editor'

export type IframeLoadMessage = {
    type: 'IFRAME_LOAD'
    url: string
    path: string
    title: string
    collection: InlineCollection
}
export type IframeRequestEditMessage = {
    type: 'IFRAME_REQUEST_EDIT'
    element: InlineElement
}
export type IframeContentChangedMessage = {
    type: 'IFRAME_CONTENT_CHANGED'
    element: InlineElement
    content: string
}

export type IframeToEditorMessage =
    | IframeLoadMessage
    | { type: 'IFRAME_UNLOAD' }
    | IframeRequestEditMessage
    | IframeContentChangedMessage

export type EditorElementsMessage = {
    type: 'EDITOR_ELEMENTS'
    selectors: string[]
}
export type EditorEditMessage = {
    type: 'EDITOR_EDIT'
    element: InlineElement,
    data: Record<string, null | string>
}
export type EditorDiscardMessage = {
    type: 'EDITOR_DISCARD'
}
export type EditorRefreshMessage = {
    type: 'EDITOR_REFRESH'
}

export type EditorToIframeMessage =
    | EditorElementsMessage
    | EditorEditMessage
    | EditorDiscardMessage
    | EditorRefreshMessage

export type EmsId = string

export type InlineCollection = Record<EmsId, InlineElement[]>;

export interface InlineElement {
    emsId: EmsId
    path: string
    id: string
    tag: string
    selector: string
}
