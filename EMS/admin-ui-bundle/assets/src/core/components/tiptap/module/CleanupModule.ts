import IconClear from '@tabler/icons/outline/eraser.svg?raw'
import { TiptapModule } from '../types.ts'

export const CleanupModule: TiptapModule = {
    name: 'cleanup',
    extensions: [],
    groups: {
        cleanup: ['clear']
    },
    actions: {
        clear: {
            icon: IconClear,
            tooltip: 'Remove Formatting',
            command: (e) => e.tiptap.chain().focus().unsetAllMarks().clearNodes().run(),
            isActive: () => false
        }
    }
}
