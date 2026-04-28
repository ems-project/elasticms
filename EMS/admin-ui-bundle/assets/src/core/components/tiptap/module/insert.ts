import Blockquote from '@tiptap/extension-blockquote'
import HorizontalRule from '@tiptap/extension-horizontal-rule'
import IconHorizontalRule from '@tabler/icons/outline/separator-horizontal.svg?raw'
import IconBlockquote from '@tabler/icons/outline/quote.svg?raw'
import { TiptapModule } from '../types.ts'

export const insertModule: TiptapModule[] = [
    {
        name: 'HorizontalRule',
        extensions: [HorizontalRule],
        command: (e) => e.tiptap.chain().focus().setHorizontalRule().run(),
        isActive: () => false,
        toolbar: {
            group: 'insert',
            icon: IconHorizontalRule,
            tooltip: 'Insert Horizontal Line'
        }
    },
    {
        name: 'Blockquote',
        extensions: [Blockquote],
        command: (e) => e.tiptap.chain().focus().toggleBlockquote().run(),
        isActive: (e) => e.tiptap.isActive('blockquote'),
        toolbar: {
            group: 'blocks',
            icon: IconBlockquote,
            tooltip: 'Block Quote'
        }
    }
]
