import { UndoRedo } from '@tiptap/extensions'
import IconUndo from '@tabler/icons/outline/arrow-back-up.svg?raw'
import IconRedo from '@tabler/icons/outline/arrow-forward-up.svg?raw'

import { TiptapModule } from '../types.ts'

export const HistoryModule: TiptapModule = {
    name: 'history',
    extensions: [UndoRedo],
    groups: {
        undo: ['undo', 'redo']
    },
    actions: {
        undo: {
            icon: IconUndo,
            tooltip: 'Undo (Ctrl+Z)',
            command: (e) => e.tiptap.chain().focus().undo().run(),
            isActive: () => false
        },
        redo: {
            icon: IconRedo,
            tooltip: 'Redo (Ctrl+Y)',
            command: (e) => e.tiptap.chain().focus().redo().run(),
            isActive: () => false
        }
    }
}
