export const MESSAGE_SOURCE = 'inline-editor'

export type IframeLoadMessage = {
    type: 'IFRAME_LOAD'
    url: string
    path: string
    title: string
    elements: InlineElement[]
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
export type IframeResponseContentMessage = {
    type: 'IFRAME_RESPONSE_CONTENT'
    element: InlineElement
    content: string
}

export type IframeToEditorMessage =
    | IframeLoadMessage
    | { type: 'IFRAME_UNLOAD' }
    | IframeRequestEditMessage
    | IframeContentChangedMessage
    | IframeResponseContentMessage

export type EditorElementsMessage = {
    type: 'EDITOR_ELEMENTS'
    selectors: string[]
}
export type EditorInlineEditMessage = {
    type: 'EDITOR_INLINE_EDIT'
    element: InlineElement
}
export type EditorDiscardMessage = {
    type: 'EDITOR_DISCARD'
}
export type EditorRequestContentMessage = {
    type: 'EDITOR_REQUEST_CONTENT'
    element: InlineElement
}

export type EditorToIframeMessage =
    | EditorElementsMessage
    | EditorInlineEditMessage
    | EditorDiscardMessage
    | EditorRequestContentMessage

export interface InlineElement {
    emsId: string
    path: string
    tag: string
    selector: string
}
