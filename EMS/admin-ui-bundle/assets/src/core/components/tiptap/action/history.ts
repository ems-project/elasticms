import { UndoRedo } from '@tiptap/extensions'
import IconUndo from '@tabler/icons/outline/arrow-back-up.svg?raw'
import IconRedo from '@tabler/icons/outline/arrow-forward-up.svg?raw'
import { ToolbarAction } from '../types.ts'

export const historyActions: ToolbarAction[] = [
    {
        name: 'Undo',
        group: 'undo',
        icon: IconUndo,
        tooltip: 'Undo',
        extensions: [UndoRedo],
        command: (e) => e.tiptap.chain().focus().undo().run(),
        isActive: () => false
    },
    {
        name: 'Redo',
        group: 'undo',
        icon: IconRedo,
        tooltip: 'Redo',
        extensions: [UndoRedo],
        command: (e) => e.tiptap.chain().focus().redo().run(),
        isActive: () => false
    }
]
