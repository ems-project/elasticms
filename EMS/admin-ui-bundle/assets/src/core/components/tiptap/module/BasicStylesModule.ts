import Bold from '@tiptap/extension-bold'
import Italic from '@tiptap/extension-italic'
import Strike from '@tiptap/extension-strike'
import IconBold from '@tabler/icons/outline/bold.svg?raw'
import IconItalic from '@tabler/icons/outline/italic.svg?raw'
import IconStrike from '@tabler/icons/outline/strikethrough.svg?raw'
import { TiptapModule } from '../types.ts'

export const BasicStylesModule: TiptapModule = {
    name: 'basicstyles',
    extensions: [Bold, Italic, Strike],
    groups: {
        basicstyles: ['bold', 'italic', 'strike']
    },
    actions: {
        bold: {
            icon: IconBold,
            tooltip: 'Bold (Ctrl+B)',
            command: (e) => e.tiptap.chain().focus().toggleBold().run(),
            isActive: (e) => e.tiptap.isActive('bold')
        },
        italic: {
            icon: IconItalic,
            tooltip: 'Italic (Ctrl+I)',
            command: (e) => e.tiptap.chain().focus().toggleItalic().run(),
            isActive: (e) => e.tiptap.isActive('italic')
        },
        strike: {
            icon: IconStrike,
            tooltip: 'Strikethrough',
            command: (e) => e.tiptap.chain().focus().toggleStrike().run(),
            isActive: (e) => e.tiptap.isActive('strike')
        }
    }
}
