import TextAlign from '@tiptap/extension-text-align'
import IconJustifyLeft from '@tabler/icons/outline/align-left.svg?raw'
import IconJustifyCenter from '@tabler/icons/outline/align-center.svg?raw'
import IconJustifyRight from '@tabler/icons/outline/align-right.svg?raw'
import IconJustifyBlock from '@tabler/icons/outline/align-justified.svg?raw'
import { TiptapModule } from '../types.ts'

const CustomTextAlign = TextAlign.configure({
    types: ['heading', 'paragraph', 'div'],
    alignments: ['left', 'center', 'right', 'justify']
})

export const justifyModule: TiptapModule = {
    isEnabled: (wysiwygProfile) => wysiwygProfile.hasPlugin('justify'),
    extensions: [CustomTextAlign],
    toolbar: {
        group: 'align',
        items: [
            {
                name: 'JustifyLeft',
                icon: IconJustifyLeft,
                tooltip: 'align_left',
                command: (e) => e.tiptap.chain().focus().unsetTextAlign().run(),
                isActive: (e) => {
                    const isCenter = e.tiptap.isActive({ textAlign: 'center' })
                    const isRight = e.tiptap.isActive({ textAlign: 'right' })
                    const isJustify = e.tiptap.isActive({ textAlign: 'justify' })

                    return !isCenter && !isRight && !isJustify
                }
            },
            {
                name: 'JustifyCenter',
                icon: IconJustifyCenter,
                tooltip: 'align_center',
                command: (e) => e.tiptap.chain().focus().setTextAlign('center').run(),
                isActive: (e) => e.tiptap.isActive({ textAlign: 'center' })
            },
            {
                name: 'JustifyRight',
                icon: IconJustifyRight,
                tooltip: 'align_right',
                command: (e) => e.tiptap.chain().focus().setTextAlign('right').run(),
                isActive: (e) => e.tiptap.isActive({ textAlign: 'right' })
            },
            {
                name: 'JustifyBlock',
                icon: IconJustifyBlock,
                tooltip: 'align_justify',
                command: (e) => e.tiptap.chain().focus().setTextAlign('justify').run(),
                isActive: (e) => e.tiptap.isActive({ textAlign: 'justify' })
            }
        ]
    }
}
