import Blockquote from '@tiptap/extension-blockquote'
import HorizontalRule from '@tiptap/extension-horizontal-rule'
import IconHorizontalRule from '@tabler/icons/outline/separator-horizontal.svg?raw'
import IconBlockquote from '@tabler/icons/outline/quote.svg?raw'
import { TiptapModule } from '../types.ts'

export const insertModule: TiptapModule[] = [
    {
        extensions: [HorizontalRule],
        toolbar: {
            group: 'insert',
            items: [
                {
                    name: 'HorizontalRule',
                    icon: IconHorizontalRule,
                    tooltip: 'horizontal_line_insert',
                    order: 3,
                    command: (e) => e.tiptap.chain().focus().setHorizontalRule().run()
                }
            ]
        }
    },
    {
        extensions: [Blockquote],
        toolbar: {
            group: 'blocks',
            items: [
                {
                    name: 'Blockquote',
                    icon: IconBlockquote,
                    tooltip: 'block_quote_insert',
                    command: (e) => e.tiptap.chain().focus().toggleBlockquote().run(),
                    isActive: (e) => e.tiptap.isActive('blockquote')
                }
            ]
        }
    }
]
