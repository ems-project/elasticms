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
import { openTableDialog } from './tableDialog.ts'
import { openCellDialog } from './tableCellDialog.ts'

export const tableContextMenu: ContextMenuItem[] = [
    {
        label: 'Insert cell before',
        icon: IconCellBefore,
        parentIcon: IconCell,
        parent: 'Cell',
        order: 0,
        disabled: (e) => isInCaption(e),
        command: (e) => cellInsert(e.tiptap, 'before')
    },
    {
        label: 'Insert cell after',
        icon: IconCellAfter,
        parent: 'Cell',
        order: 1,
        disabled: (e) => isInCaption(e),
        command: (e) => cellInsert(e.tiptap, 'after')
    },
    {
        label: 'Clear cell(s)',
        icon: IconCellClear,
        parent: 'Cell',
        order: 99,
        disabled: (e) => isInCaption(e),
        command: (e) => {
            e.tiptap.chain().focus().deleteSelection().unsetAllMarks().run()
            cellClear(e.tiptap)
        }
    },
    {
        label: 'Merge cells',
        icon: IconCellMerge,
        parent: 'Cell',
        order: 2,
        disabled: (e) => isInCaption(e) || !e.tiptap.can().mergeCells(),
        command: (e) => e.tiptap.chain().focus().mergeCells().run()
    },
    {
        label: 'Split cell',
        icon: IconCellSplit,
        parent: 'Cell',
        order: 3,
        disabled: (e) => isInCaption(e) || !e.tiptap.can().splitCell(),
        command: (e) => e.tiptap.chain().focus().splitCell().run()
    },
    {
        label: 'Cell properties',
        icon: IconCell,
        parent: 'Cell',
        order: 99,
        disabled: (e) => isInCaption(e),
        command: (e) => openCellDialog(e)
    },
    {
        label: 'Insert row before',
        icon: IconRowBefore,
        parentIcon: IconRow,
        parent: 'Row',
        order: 0,
        disabled: (e) => isInCaption(e),
        command: (e) => e.tiptap.chain().focus().addRowBefore().run()
    },
    {
        label: 'Insert row after',
        icon: IconRowAfter,
        parent: 'Row',
        order: 1,
        disabled: (e) => isInCaption(e),
        command: (e) => e.tiptap.chain().focus().addRowAfter().run()
    },
    {
        label: 'Delete row(s)',
        icon: IconRowDelete,
        parent: 'Row',
        order: 99,
        disabled: (e) => isInCaption(e),
        command: (e) => e.tiptap.chain().focus().deleteRow().run()
    },
    {
        label: 'Insert column before',
        icon: IconColumnBefore,
        parent: 'Column',
        parentIcon: IconColumn,
        order: 0,
        disabled: (e) => isInCaption(e),
        command: (e) => e.tiptap.chain().focus().addColumnBefore().run()
    },
    {
        label: 'Insert column after',
        icon: IconColumnAfter,
        parent: 'Column',
        order: 1,
        disabled: (e) => isInCaption(e),
        command: (e) => e.tiptap.chain().focus().addColumnAfter().run()
    },
    {
        label: 'Delete column(s)',
        icon: IconColumnDelete,
        parent: 'Column',
        order: 99,
        disabled: (e) => isInCaption(e),
        command: (e) => e.tiptap.chain().focus().deleteColumn().run()
    },
    {
        label: 'Delete table',
        icon: IconTableDelete,
        order: 98,
        command: (e) => deleteTable(e.tiptap)
    },
    {
        label: 'Table properties',
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
