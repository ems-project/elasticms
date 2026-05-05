import { ContextMenuItem } from '../types.ts'

import IconTable from '@tabler/icons/outline/table.svg?raw'
import IconTableDelete from '@tabler/icons/outline/trash.svg?raw'
import IconRow from '@tabler/icons/outline/table-row.svg?raw'
import IconRowBefore from '@tabler/icons/outline/row-insert-top.svg?raw'
import IconRowAfter from '@tabler/icons/outline/row-insert-bottom.svg?raw'
import IconRowDelete from '@tabler/icons/outline/row-remove.svg?raw'
import IconColumn from '@tabler/icons/outline/table-column.svg?raw'
import IconColumnBefore from '@tabler/icons/outline/column-insert-left.svg?raw'
import IconColumnAfter from '@tabler/icons/outline/column-insert-right.svg?raw'
import IconColumnDelete from '@tabler/icons/outline/column-remove.svg?raw'
import { Editor } from '@tiptap/core'
import { openTableDialog } from './tableDialog.ts'

export const tableContextMenu: ContextMenuItem[] = [
    {
        label: 'Insert row before',
        icon: IconRowBefore,
        parentIcon: IconRow,
        parent: 'Row',
        order: 0,
        command: (e) => e.tiptap.chain().focus().addRowBefore().run()
    },
    {
        label: 'Insert row after',
        icon: IconRowAfter,
        parent: 'Row',
        order: 1,
        command: (e) => e.tiptap.chain().focus().addRowAfter().run()
    },
    {
        label: 'Delete row(s)',
        icon: IconRowDelete,
        parent: 'Row',
        order: 99,
        command: (e) => e.tiptap.chain().focus().deleteRow().run()
    },
    {
        label: 'Insert column before',
        icon: IconColumnBefore,
        parent: 'Column',
        parentIcon: IconColumn,
        order: 0,
        command: (e) => e.tiptap.chain().focus().addColumnBefore().run()
    },
    {
        label: 'Insert column after',
        icon: IconColumnAfter,
        parent: 'Column',
        order: 1,
        command: (e) => e.tiptap.chain().focus().addColumnAfter().run()
    },
    {
        label: 'Delete column(s)',
        icon: IconColumnDelete,
        parent: 'Column',
        order: 99,
        command: (e) => e.tiptap.chain().focus().deleteColumn().run()
    },
    {
        label: 'Delete table',
        icon: IconTableDelete,
        order: 98,
        command: (e) => commandDeleteTable(e.tiptap)
    },
    {
        label: 'Table properties',
        icon: IconTable,
        order: 99,
        command: (e) => openTableDialog(e, 'edit')
    }
]

function commandDeleteTable(tiptap: Editor) {
    const { $from } = tiptap.state.selection
    for (let d = $from.depth; d > 0; d--) {
        if ($from.node(d).type.name === 'tableFigure') {
            tiptap
                .chain()
                .focus()
                .deleteRange({ from: $from.before(d), to: $from.after(d) })
                .run()
            return
        }
    }
    tiptap.chain().focus().deleteTable().run()
}
