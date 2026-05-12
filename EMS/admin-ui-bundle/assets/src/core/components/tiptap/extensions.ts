import { Extension, Mark, Node } from '@tiptap/core'
import Document from '@tiptap/extension-document'
import Paragraph from '@tiptap/extension-paragraph'
import Text from '@tiptap/extension-text'
import { Gapcursor } from '@tiptap/extension-gapcursor'
import { HardBreak } from '@tiptap/extension-hard-break'

export type ExtensionType = Extension | Mark | Node

export const DEFAULT_EXTENSIONS = [Document, Paragraph, Text, Gapcursor, HardBreak]

function createBlockNode(name: string): ExtensionType {
    return Node.create({
        name,
        group: 'block',
        content: 'inline*',
        parseHTML() {
            return [{ tag: name }]
        },
        renderHTML({ HTMLAttributes }) {
            return [name, HTMLAttributes, 0]
        }
    })
}

export const BLOCK_NODES = {
    div: createBlockNode('div'),
    pre: createBlockNode('pre'),
    address: createBlockNode('address')
} as const
