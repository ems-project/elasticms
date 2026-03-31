import Bold from '@tiptap/extension-bold'
import Italic from '@tiptap/extension-italic'
import Strike from '@tiptap/extension-strike'
import IconBold from '@tabler/icons/outline/bold.svg?raw'
import IconItalic from '@tabler/icons/outline/italic.svg?raw'
import IconStrike from '@tabler/icons/outline/strikethrough.svg?raw'
import { ToolbarAction } from '../types.ts'

export const basicStyleActions: ToolbarAction[] = [
    {
        name: 'Bold',
        group: 'basicstyles',
        icon: IconBold,
        tooltip: 'Bold',
        extensions: [Bold],
        command: (e) => e.tiptap.chain().focus().toggleBold().run(),
        isActive: (e) => e.tiptap.isActive('bold')
    },
    {
        name: 'Italic',
        group: 'basicstyles',
        icon: IconItalic,
        tooltip: 'Italic',
        extensions: [Italic],
        command: (e) => e.tiptap.chain().focus().toggleItalic().run(),
        isActive: (e) => e.tiptap.isActive('italic')
    },
    {
        name: 'Strike',
        group: 'basicstyles',
        icon: IconStrike,
        tooltip: 'Strike Through',
        extensions: [Strike],
        command: (e) => e.tiptap.chain().focus().toggleStrike().run(),
        isActive: (e) => e.tiptap.isActive('strike')
    }
]
