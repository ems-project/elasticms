import { UndoRedo } from '@tiptap/extensions'
import { TiptapModule } from '../types.ts'

export const HistoryModule: TiptapModule = {
    name: 'history',
    extensions: [UndoRedo],
    groups: {
        undo: ['undo', 'redo']
    },
    actions: {
        undo: {
            icon: 'undo',
            tooltip: 'Undo (Ctrl+Z)',
            command: (e) => e.tiptap.chain().focus().undo().run(),
            isActive: () => false
        },
        redo: {
            icon: 'redo',
            tooltip: 'Redo (Ctrl+Y)',
            command: (e) => e.tiptap.chain().focus().redo().run(),
            isActive: () => false
        }
    }
}
