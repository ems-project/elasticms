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
            tooltip: 'history_undo',
            command: (e) => e.tiptap.chain().focus().undo().run(),
            isDisabled: (e) => !e.tiptap.can().undo()
        },
        {
            name: 'Redo',
            icon: IconRedo,
            tooltip: 'history_redo',
            command: (e) => e.tiptap.chain().focus().redo().run(),
            isDisabled: (e) => !e.tiptap.can().redo()
        }
    ]
}
