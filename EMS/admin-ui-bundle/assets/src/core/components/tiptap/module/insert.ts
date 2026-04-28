import Blockquote from '@tiptap/extension-blockquote'
import HorizontalRule from '@tiptap/extension-horizontal-rule'
import IconHorizontalRule from '@tabler/icons/outline/separator-horizontal.svg?raw'
import IconBlockquote from '@tabler/icons/outline/quote.svg?raw'
import { TiptapModule } from '../types.ts'

export const insertModule: TiptapModule[] = [
    {
        name: 'HorizontalRule',
        group: 'insert',
        icon: IconHorizontalRule,
        tooltip: 'Insert Horizontal Line',
        extensions: [HorizontalRule],
        command: (e) => e.tiptap.chain().focus().setHorizontalRule().run(),
        isActive: () => false
    },
    {
        name: 'Blockquote',
        group: 'blocks',
        icon: IconBlockquote,
        tooltip: 'Block Quote',
        extensions: [Blockquote],
        command: (e) => e.tiptap.chain().focus().toggleBlockquote().run(),
        isActive: (e) => e.tiptap.isActive('blockquote')
    }
]
