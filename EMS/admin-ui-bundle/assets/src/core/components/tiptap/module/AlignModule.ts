import TextAlign from '@tiptap/extension-text-align'
import IconAlignLeft from '@tabler/icons/outline/align-left.svg?raw'
import IconAlignCenter from '@tabler/icons/outline/align-center.svg?raw'
import IconAlignRight from '@tabler/icons/outline/align-right.svg?raw'
import IconAlignJustify from '@tabler/icons/outline/align-justified.svg?raw'
import { TiptapModule } from '../types'

export const AlignModule: TiptapModule = {
    name: 'align',
    extensions: [
        TextAlign.configure({
            types: ['heading', 'paragraph'],
            alignments: ['left', 'center', 'right', 'justify'],
            defaultAlignment: 'left'
        })
    ],
    groups: {
        align: ['alignLeft', 'alignCenter', 'alignRight', 'alignJustify']
    },
    actions: {
        alignLeft: {
            icon: IconAlignLeft,
            tooltip: 'Align Left',
            command: (e) => e.tiptap.chain().focus().setTextAlign('left').run(),
            isActive: (e) => e.tiptap.isActive({ textAlign: 'left' })
        },
        alignCenter: {
            icon: IconAlignCenter,
            tooltip: 'Align Center',
            command: (e) => e.tiptap.chain().focus().setTextAlign('center').run(),
            isActive: (e) => e.tiptap.isActive({ textAlign: 'center' })
        },
        alignRight: {
            icon: IconAlignRight,
            tooltip: 'Align Right',
            command: (e) => e.tiptap.chain().focus().setTextAlign('right').run(),
            isActive: (e) => e.tiptap.isActive({ textAlign: 'right' })
        },
        alignJustify: {
            icon: IconAlignJustify,
            tooltip: 'Justify',
            command: (e) => e.tiptap.chain().focus().setTextAlign('justify').run(),
            isActive: (e) => e.tiptap.isActive({ textAlign: 'justify' })
        }
    }
}
