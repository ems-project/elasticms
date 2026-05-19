import { ContextMenuItem } from '../types.ts'

import IconTable from '@tabler/icons/outline/table.svg?raw'
import IconTableDelete from '@tabler/icons/outline/trash.svg?raw'
import IconCell from '@tabler/icons/outline/square.svg?raw'
import IconCellBefore from '@tabler/icons/outline/square-chevron-left.svg?raw'
import IconCellAfter from '@tabler/icons/outline/square-chevron-right.svg?raw'
import IconCellClear from '@tabler/icons/outline/square-off.svg?raw'
import IconCellMerge from '@tabler/icons/outline/squares.svg?raw'
import IconCellSplit from '@tabler/icons/outline/squares-diagonal.svg?raw'
import IconRow from '@tabler/icons/outline/table-row.svg?raw'
import IconRowBefore from '@tabler/icons/outline/row-insert-top.svg?raw'
import IconRowAfter from '@tabler/icons/outline/row-insert-bottom.svg?raw'
import IconRowDelete from '@tabler/icons/outline/row-remove.svg?raw'
import IconColumn from '@tabler/icons/outline/table-column.svg?raw'
import IconColumnBefore from '@tabler/icons/outline/column-insert-left.svg?raw'
import IconColumnAfter from '@tabler/icons/outline/column-insert-right.svg?raw'
import IconColumnDelete from '@tabler/icons/outline/column-remove.svg?raw'
import { Editor } from '@tiptap/core'
import { openTableDialog } from './dialogTable.ts'
import { openCellDialog } from './dialogCell.ts'

export const contextMenu: ContextMenuItem[] = [
    {
        label: 'table_cell_insert_before',
        icon: IconCellBefore,
        parentIcon: IconCell,
        parent: 'table_cell',
        order: 0,
        disabled: (e) => isInCaption(e),
        command: (e) => cellInsert(e.tiptap, 'before')
    },
    {
        label: 'table_cell_insert_after',
        icon: IconCellAfter,
        parent: 'table_cell',
        order: 1,
        disabled: (e) => isInCaption(e),
        command: (e) => cellInsert(e.tiptap, 'after')
    },
    {
        label: 'table_cell_clear',
        icon: IconCellClear,
        parent: 'table_cell',
        order: 99,
        disabled: (e) => isInCaption(e),
        command: (e) => {
            e.tiptap.chain().focus().deleteSelection().unsetAllMarks().run()
            cellClear(e.tiptap)
        }
    },
    {
        label: 'table_cell_merge',
        icon: IconCellMerge,
        parent: 'table_cell',
        order: 2,
        disabled: (e) => isInCaption(e) || !e.tiptap.can().mergeCells(),
        command: (e) => e.tiptap.chain().focus().mergeCells().run()
    },
    {
        label: 'table_cell_split',
        icon: IconCellSplit,
        parent: 'table_cell',
        order: 3,
        disabled: (e) => isInCaption(e) || !e.tiptap.can().splitCell(),
        command: (e) => e.tiptap.chain().focus().splitCell().run()
    },
    {
        label: 'table_cell_properties',
        icon: IconCell,
        parent: 'table_cell',
        order: 99,
        disabled: (e) => isInCaption(e),
        command: (e) => openCellDialog(e)
    },
    {
        label: 'table_row_insert_before',
        icon: IconRowBefore,
        parentIcon: IconRow,
        parent: 'table_row',
        order: 0,
        disabled: (e) => isInCaption(e),
        command: (e) => e.tiptap.chain().focus().addRowBefore().run()
    },
    {
        label: 'table_row_insert_after',
        icon: IconRowAfter,
        parent: 'table_row',
        order: 1,
        disabled: (e) => isInCaption(e),
        command: (e) => e.tiptap.chain().focus().addRowAfter().run()
    },
    {
        label: 'table_row_delete',
        icon: IconRowDelete,
        parent: 'table_row',
        order: 99,
        disabled: (e) => isInCaption(e),
        command: (e) => e.tiptap.chain().focus().deleteRow().run()
    },
    {
        label: 'table_column_insert_before',
        icon: IconColumnBefore,
        parent: 'table_column',
        parentIcon: IconColumn,
        order: 0,
        disabled: (e) => isInCaption(e),
        command: (e) => e.tiptap.chain().focus().addColumnBefore().run()
    },
    {
        label: 'table_column_insert_after',
        icon: IconColumnAfter,
        parent: 'table_column',
        order: 1,
        disabled: (e) => isInCaption(e),
        command: (e) => e.tiptap.chain().focus().addColumnAfter().run()
    },
    {
        label: 'table_column_delete',
        icon: IconColumnDelete,
        parent: 'table_column',
        order: 99,
        disabled: (e) => isInCaption(e),
        command: (e) => e.tiptap.chain().focus().deleteColumn().run()
    },
    {
        label: 'table_delete',
        icon: IconTableDelete,
        order: 98,
        command: (e) => deleteTable(e.tiptap)
    },
    {
        label: 'table_properties',
        icon: IconTable,
        order: 99,
        command: (e) => openTableDialog(e, 'edit')
    }
]

function isInCaption(e: { tiptap: Editor }): boolean {
    const { $from } = e.tiptap.state.selection
    for (let d = $from.depth; d > 0; d--) {
        if ($from.node(d).type.name === 'tableCaption') return true
    }
    return false
}

function deleteTable(tiptap: Editor) {
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

function cellInsert(editor: Editor, direction: 'before' | 'after') {
    const content = cellContent(editor)
    if (!content) return
    const json = content.toJSON()

    if (direction === 'before') {
        editor.chain().focus().addColumnAfter().goToNextCell().run()
        editor.chain().focus().insertContent(json).run()
        editor.chain().focus().goToPreviousCell().run()
    } else {
        editor.chain().focus().addColumnBefore().goToPreviousCell().run()
        editor.chain().focus().insertContent(json).run()
        editor.chain().focus().goToNextCell().run()
    }

    cellClear(editor)
}

function cellContent(editor: Editor) {
    const { $from } = editor.state.selection
    for (let d = $from.depth; d > 0; d--) {
        const role = $from.node(d).type.spec.tableRole
        if (role === 'cell' || role === 'header_cell') {
            return editor.state.doc.nodeAt($from.before(d))?.content ?? null
        }
    }
    return null
}

function cellClear(editor: Editor) {
    const { $from } = editor.state.selection
    for (let d = $from.depth; d > 0; d--) {
        const role = $from.node(d).type.spec.tableRole
        if (role === 'cell' || role === 'header_cell') {
            const pos = $from.before(d)
            const cell = editor.state.doc.nodeAt(pos)
            if (!cell) return
            editor.view.dispatch(
                editor.state.tr.replaceWith(
                    pos + 1,
                    pos + 1 + cell.content.size,
                    editor.state.schema.nodes.paragraph.create()
                )
            )
            return
        }
    }
}
