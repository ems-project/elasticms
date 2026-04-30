import Blockquote from '@tiptap/extension-blockquote'
import HorizontalRule from '@tiptap/extension-horizontal-rule'
import IconHorizontalRule from '@tabler/icons/outline/separator-horizontal.svg?raw'
import IconBlockquote from '@tabler/icons/outline/quote.svg?raw'
import { TiptapModule } from '../types.ts'

export const insertModule: TiptapModule[] = [
    {
        extensions: [HorizontalRule],
        group: 'insert',
        toolbar: [
            {
                name: 'HorizontalRule',
                icon: IconHorizontalRule,
                tooltip: 'Insert Horizontal Line',
                command: (e) => e.tiptap.chain().focus().setHorizontalRule().run()
            }
        ]
    },
    {
        extensions: [Blockquote],
        group: 'blocks',
        toolbar: [
            {
                name: 'Blockquote',
                icon: IconBlockquote,
                tooltip: 'Block Quote',
                command: (e) => e.tiptap.chain().focus().toggleBlockquote().run(),
                isActive: (e) => e.tiptap.isActive('blockquote')
            }
        ]
    }
]
