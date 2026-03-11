export const MESSAGE_SOURCE = 'inline-editor'

export type IframeLoadMessage = {
  type: 'IFRAME_LOAD'
  url: string
  path: string
  title: string
  elements: InlineElement[]
}
export type IframeRequestInlineEdit = {
  type: 'IFRAME_REQUEST_INLINE_EDIT'
  element: InlineElement
}
export type IframeContentChanged = {
  type: 'IFRAME_CONTENT_CHANGED'
  element: InlineElement
  content: string
}

export type EditorElementsMessage = {
  type: 'EDITOR_ELEMENTS'
  selectors: string[]
}
export type EditorInlineEditMessage = {
  type: 'EDITOR_INLINE_EDIT'
  element: InlineElement
}

export type EditorToIframeMessage = EditorElementsMessage | EditorInlineEditMessage

export type IframeToEditorMessage =
  | IframeLoadMessage
  | { type: 'IFRAME_UNLOAD' }
  | IframeRequestInlineEdit
  | IframeContentChanged

export interface InlineElement {
  emsId: string
  path: string
  tag: string
  selector: string
}
