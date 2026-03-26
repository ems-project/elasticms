import Bold from '@tiptap/extension-bold'
import Italic from '@tiptap/extension-italic'
import Strike from '@tiptap/extension-strike'
import { TiptapModule } from '../types.ts'

export const BasicStylesModule: TiptapModule = {
    name: 'basicstyles',
    extensions: [Bold, Italic, Strike],
    groups: {
        basicstyles: ['bold', 'italic', 'strike']
    },
    actions: {
        bold: {
            icon: 'bold',
            tooltip: 'Bold (Ctrl+B)',
            command: (e) => e.chain().focus().toggleBold().run(),
            isActive: (e) => e.isActive('bold')
        },
        italic: {
            icon: 'italic',
            tooltip: 'Italic (Ctrl+I)',
            command: (e) => e.chain().focus().toggleItalic().run(),
            isActive: (e) => e.isActive('italic')
        },
        strike: {
            icon: 'strike',
            tooltip: 'Strikethrough',
            command: (e) => e.chain().focus().toggleStrike().run(),
            isActive: (e) => e.isActive('strike')
        }
    }
}
