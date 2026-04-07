import IconClear from '@tabler/icons/outline/eraser.svg?raw'
import { ToolbarAction } from '../types.ts'

export const cleanupActions: ToolbarAction[] = [
    {
        name: 'RemoveFormat',
        group: 'cleanup',
        icon: IconClear,
        tooltip: 'Remove Format',
        command: (e) => e.tiptap.chain().focus().unsetAllMarks().clearNodes().run(),
        isActive: () => false
    }
]
