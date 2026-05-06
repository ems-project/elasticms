import { UndoRedo } from '@tiptap/extensions'
import IconUndo from '@tabler/icons/outline/arrow-back-up.svg?raw'
import IconRedo from '@tabler/icons/outline/arrow-forward-up.svg?raw'
import { TiptapModule } from '../types.ts'

export const historyModule: TiptapModule = {
    extensions: [UndoRedo],
    toolbarGroup: 'undo',
    toolbar: [
        {
            name: 'Undo',
            icon: IconUndo,
            tooltip: 'Undo',
            command: (e) => e.tiptap.chain().focus().undo().run()
        },
        {
            name: 'Redo',
            icon: IconRedo,
            tooltip: 'Redo',
            command: (e) => e.tiptap.chain().focus().redo().run()
        }
    ]
}
