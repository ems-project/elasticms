import TextAlign from '@tiptap/extension-text-align'
import IconJustifyLeft from '@tabler/icons/outline/align-left.svg?raw'
import IconJustifyCenter from '@tabler/icons/outline/align-center.svg?raw'
import IconJustifyRight from '@tabler/icons/outline/align-right.svg?raw'
import IconJustifyBlock from '@tabler/icons/outline/align-justified.svg?raw'
import { TiptapModule } from '../types.ts'

const CustomTextAlign = TextAlign.extend({
    addGlobalAttributes() {
        return this.parent!().map((entry) => {
            const textAlign = entry.attributes?.textAlign
            if (!textAlign) return entry
            textAlign.parseHTML = (element: HTMLElement) =>
                element.getAttribute('data-text-align') || element.style.textAlign || null
            return entry
        })
    }
}).configure({
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
    },
    htmlTransforms: [
        {
            name: 'textAlign',
            toEditor(doc) {
                doc.querySelectorAll('p, h1, h2, h3, h4, h5, h6, div').forEach((el) => {
                    const htmlEl = el as HTMLElement
                    const dataAlign = htmlEl.getAttribute('data-text-align')
                    const styleAlign = htmlEl.style.textAlign

                    if (dataAlign) {
                        htmlEl.style.removeProperty('text-align')
                    } else if (styleAlign) {
                        htmlEl.setAttribute('data-text-align', styleAlign)
                        htmlEl.style.removeProperty('text-align')
                    }
                })
            },
            toOutput(doc) {
                doc.querySelectorAll('[data-text-align]').forEach((el) => {
                    const htmlEl = el as HTMLElement
                    htmlEl.style.textAlign = htmlEl.getAttribute('data-text-align') ?? ''
                    htmlEl.removeAttribute('data-text-align')
                })
            }
        }
    ]
}
