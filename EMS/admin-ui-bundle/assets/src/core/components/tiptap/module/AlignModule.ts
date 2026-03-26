import TextAlign from '@tiptap/extension-text-align'
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
            icon: 'alignLeft',
            tooltip: 'Align Left',
            command: (e) => e.tiptap.chain().focus().setTextAlign('left').run(),
            isActive: (e) => e.tiptap.isActive({ textAlign: 'left' })
        },
        alignCenter: {
            icon: 'alignCenter',
            tooltip: 'Align Center',
            command: (e) => e.tiptap.chain().focus().setTextAlign('center').run(),
            isActive: (e) => e.tiptap.isActive({ textAlign: 'center' })
        },
        alignRight: {
            icon: 'alignRight',
            tooltip: 'Align Right',
            command: (e) => e.tiptap.chain().focus().setTextAlign('right').run(),
            isActive: (e) => e.tiptap.isActive({ textAlign: 'right' })
        },
        alignJustify: {
            icon: 'alignJustify',
            tooltip: 'Justify',
            command: (e) => e.tiptap.chain().focus().setTextAlign('justify').run(),
            isActive: (e) => e.tiptap.isActive({ textAlign: 'justify' })
        }
    }
}
