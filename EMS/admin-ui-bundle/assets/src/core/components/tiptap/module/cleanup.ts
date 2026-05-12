import IconClear from '@tabler/icons/outline/eraser.svg?raw'
import { TiptapModule } from '../types.ts'

const PRESERVED_MARKS = ['anchor', 'link']

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
                    if (!PRESERVED_MARKS.includes(mark)) chain.unsetMark(mark)
                })
                chain.clearNodes().run()
            },
            isDisabled: (e) => {
                const { state } = e.tiptap
                if (state.selection.empty) return true

                const { from, to } = state.selection
                let hasFormatting = false

                state.doc.nodesBetween(from, to, (node) => {
                    if (node.marks.some((m) => !PRESERVED_MARKS.includes(m.type.name)))
                        hasFormatting = true
                    if (!node.isText && node.type !== state.schema.nodes['paragraph'])
                        hasFormatting = true
                })

                return !hasFormatting
            }
        }
    ]
}
