import Bold from '@tiptap/extension-bold'
import Italic from '@tiptap/extension-italic'
import Strike from '@tiptap/extension-strike'
import Superscript from '@tiptap/extension-superscript'
import Subscript from '@tiptap/extension-subscript'
import IconBold from '@tabler/icons/outline/bold.svg?raw'
import IconItalic from '@tabler/icons/outline/italic.svg?raw'
import IconStrike from '@tabler/icons/outline/strikethrough.svg?raw'
import IconSubscript from '@tabler/icons/outline/subscript.svg?raw'
import IconSuperscript from '@tabler/icons/outline/superscript.svg?raw'
import { TiptapModule } from '../Types.ts'

export const BasicStyleModule: TiptapModule[] = [
    {
        toolbar: {
            group: 'basicstyles',
            items: [
                {
                    name: 'Bold',
                    icon: IconBold,
                    tooltip: 'text_bold',
                    order: 1,
                    extensions: [Bold],
                    command: (e) => e.tiptap.chain().focus().toggleBold().run(),
                    isActive: (e) => e.tiptap.isActive('bold')
                },
                {
                    name: 'Italic',
                    icon: IconItalic,
                    tooltip: 'text_italic',
                    order: 2,
                    extensions: [Italic],
                    command: (e) => e.tiptap.chain().focus().toggleItalic().run(),
                    isActive: (e) => e.tiptap.isActive('italic')
                },
                {
                    name: 'Strike',
                    icon: IconStrike,
                    tooltip: 'text_strike',
                    order: 3,
                    extensions: [Strike],
                    command: (e) => e.tiptap.chain().focus().toggleStrike().run(),
                    isActive: (e) => e.tiptap.isActive('strike')
                }
            ]
        }
    },
    {
        isEnabled: (wysiwygProfile) => wysiwygProfile.hasPlugin('basicstyles'),
        toolbar: {
            group: 'basicstyles',
            items: [
                {
                    extensions: [Subscript],
                    name: 'Subscript',
                    icon: IconSubscript,
                    tooltip: 'text_subscript',
                    order: 4,
                    command: (e) => e.tiptap.chain().focus().toggleSubscript().run(),
                    isActive: (e) => e.tiptap.isActive('subscript')
                },
                {
                    extensions: [Superscript],
                    name: 'Superscript',
                    icon: IconSuperscript,
                    tooltip: 'text_superscript',
                    order: 5,
                    command: (e) => e.tiptap.chain().focus().toggleSuperscript().run(),
                    isActive: (e) => e.tiptap.isActive('superscript')
                }
            ]
        }
    }
]
