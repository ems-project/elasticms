import Blockquote from '@tiptap/extension-blockquote'
import HorizontalRule from '@tiptap/extension-horizontal-rule'
import IconHorizontalRule from '@tabler/icons/outline/separator.svg?raw'
import IconBlockquote from '@tabler/icons/outline/quote.svg?raw'
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
            icon: IconHorizontalRule,
            tooltip: 'Insert Horizontal Line',
            command: (e) => e.tiptap.chain().focus().setHorizontalRule().run(),
            isActive: () => false
        },
        blockquote: {
            icon: IconBlockquote,
            tooltip: 'Blockquote (Ctrl+Shift+B)',
            command: (e) => e.tiptap.chain().focus().toggleBlockquote().run(),
            isActive: (e) => e.tiptap.isActive('blockquote')
        }
    }
}
