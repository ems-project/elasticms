import IconClear from '@tabler/icons/outline/eraser.svg?raw'
import { TiptapModule } from '../types.ts'

export const cleanupModule: TiptapModule = {
    toolbarGroup: 'cleanup',
    toolbar: [
        {
            name: 'RemoveFormat',
            icon: IconClear,
            tooltip: 'Remove Format',
            command: (e) => {
                const chain = e.tiptap.chain().focus()
                Object.keys(e.tiptap.schema.marks).forEach((mark) => {
                    if (mark !== 'anchor') chain.unsetMark(mark)
                })
                chain.clearNodes().run()
            }
        }
    ]
}
