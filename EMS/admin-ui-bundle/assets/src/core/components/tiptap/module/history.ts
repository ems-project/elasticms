import { UndoRedo } from '@tiptap/extensions'
import IconUndo from '@tabler/icons/outline/arrow-back-up.svg?raw'
import IconRedo from '@tabler/icons/outline/arrow-forward-up.svg?raw'
import { TiptapModule } from '../types.ts'

export const historyModule: TiptapModule[] = [
    {
        name: 'Undo',
        extensions: [UndoRedo],
        command: (e) => e.tiptap.chain().focus().undo().run(),
        toolbar: {
            group: 'undo',
            icon: IconUndo,
            tooltip: 'Undo'
        }
    },
    {
        name: 'Redo',
        extensions: [UndoRedo],
        command: (e) => e.tiptap.chain().focus().redo().run(),
        toolbar: {
            group: 'undo',
            icon: IconRedo,
            tooltip: 'Redo'
        }
    }
]
