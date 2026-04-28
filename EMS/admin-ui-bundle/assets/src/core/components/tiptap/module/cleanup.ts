import IconClear from '@tabler/icons/outline/eraser.svg?raw'
import { TiptapModule } from '../types.ts'

export const cleanupModule: TiptapModule[] = [
    {
        name: 'RemoveFormat',
        command: (e) => e.tiptap.chain().focus().unsetAllMarks().run(),
        toolbar: {
            group: 'cleanup',
            icon: IconClear,
            tooltip: 'Remove Format'
        }
    }
]
