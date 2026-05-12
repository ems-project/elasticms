import { Node as TiptapNode } from '@tiptap/core'
import { ExtensionType } from './../extensions.ts'

export function getDivExtension(): ExtensionType {
    return TiptapNode.create({
        name: 'div',
        group: 'block',
        content: 'inline*',
        parseHTML() {
            return [{ tag: 'div' }]
        },
        renderHTML({ HTMLAttributes }) {
            return ['div', HTMLAttributes, 0]
        }
    })
}
