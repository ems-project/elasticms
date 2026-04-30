import IconClear from '@tabler/icons/outline/eraser.svg?raw'
import { TiptapModule } from '../types.ts'

export const cleanupModule: TiptapModule = {
    toolbar: [
        {
            name: 'RemoveFormat',
            group: 'cleanup',
            icon: IconClear,
            tooltip: 'Remove Format',
            command: (e) => e.tiptap.chain().focus().unsetAllMarks().run()
        }
    ]
}
