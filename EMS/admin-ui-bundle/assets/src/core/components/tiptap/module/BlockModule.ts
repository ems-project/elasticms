import Blockquote from '@tiptap/extension-blockquote'
import HorizontalRule from '@tiptap/extension-horizontal-rule'
import { TiptapModule } from '../types.ts'

export const BlockModule: TiptapModule = {
    name: 'block',
    extensions: [Blockquote, HorizontalRule],
    groups: {
        insert: ['horizontalRule'],
        blocks: ['blockquote']
    },
    actions: {
        horizontalRule: {
            icon: 'horizontalRule',
            tooltip: 'Insert Horizontal Line',
            command: (e) => e.chain().focus().setHorizontalRule().run(),
            isActive: () => false
        },
        blockquote: {
            icon: 'blockquote',
            tooltip: 'Blockquote (Ctrl+Shift+B)',
            command: (e) => e.chain().focus().toggleBlockquote().run(),
            isActive: (e) => e.isActive('blockquote')
        }
    }
}
