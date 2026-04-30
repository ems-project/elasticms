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
import { TiptapModule } from '../types.ts'

export const basicStyleModule: TiptapModule[] = [
    {
        toolbarGroup: 'basicstyles',
        toolbar: [
            {
                name: 'Bold',
                icon: IconBold,
                tooltip: 'Bold',
                extensions: [Bold],
                command: (e) => e.tiptap.chain().focus().toggleBold().run(),
                isActive: (e) => e.tiptap.isActive('bold')
            },
            {
                name: 'Italic',
                icon: IconItalic,
                tooltip: 'Italic',
                extensions: [Italic],
                command: (e) => e.tiptap.chain().focus().toggleItalic().run(),
                isActive: (e) => e.tiptap.isActive('italic')
            },
            {
                name: 'Strike',
                icon: IconStrike,
                tooltip: 'Strike Through',
                extensions: [Strike],
                command: (e) => e.tiptap.chain().focus().toggleStrike().run(),
                isActive: (e) => e.tiptap.isActive('strike')
            }
        ]
    },
    {
        isEnabled: (wysiwygProfile) =>
            wysiwygProfile.config.extraPlugins?.includes('basicstyles') ?? false,
        toolbarGroup: 'basicstyles',
        toolbar: [
            {
                extensions: [Subscript],
                name: 'Subscript',
                icon: IconSubscript,
                tooltip: 'Subscript',
                command: (e) => e.tiptap.chain().focus().toggleSubscript().run(),
                isActive: (e) => e.tiptap.isActive('subscript')
            },
            {
                extensions: [Superscript],
                name: 'Superscript',
                icon: IconSuperscript,
                tooltip: 'Superscript',
                command: (e) => e.tiptap.chain().focus().toggleSuperscript().run(),
                isActive: (e) => e.tiptap.isActive('superscript')
            }
        ]
    },
]