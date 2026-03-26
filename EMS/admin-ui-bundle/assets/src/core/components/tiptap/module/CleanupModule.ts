import { TiptapModule } from '../types.ts'

export const CleanupModule: TiptapModule = {
    name: 'cleanup',
    extensions: [],
    groups: {
        cleanup: ['clear']
    },
    actions: {
        clear: {
            icon: 'clear',
            tooltip: 'Remove Formatting',
            command: (e) => e.chain().focus().unsetAllMarks().clearNodes().run(),
            isActive: () => false
        }
    }
}
