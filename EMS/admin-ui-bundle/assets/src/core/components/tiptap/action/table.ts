import { Table, TableCell, TableHeader, TableRow } from '@tiptap/extension-table'
import IconTable from '@tabler/icons/outline/table.svg?raw'
import { ToolbarAction } from '../types.ts'
import { Dialog } from '../../dialog.ts'

const CustomTable = Table.extend({
    renderHTML({ HTMLAttributes }) {
        const { ...rest } = HTMLAttributes
        return ['table', rest, ['tbody', 0]]
    }
})

const CustomTableCell = TableCell.extend({
    renderHTML({ HTMLAttributes }) {
        const { colspan, rowspan, ...rest } = HTMLAttributes as any
        const attrs: any = { ...rest }
        if (colspan && colspan > 1) attrs.colspan = colspan
        if (rowspan && rowspan > 1) attrs.rowspan = rowspan
        return ['td', attrs, 0]
    }
})

const CustomTableHeader = TableHeader.extend({
    renderHTML({ HTMLAttributes }) {
        const { colspan, rowspan, ...rest } = HTMLAttributes as any
        const attrs: any = { ...rest }
        if (colspan && colspan > 1) attrs.colspan = colspan
        if (rowspan && rowspan > 1) attrs.rowspan = rowspan
        return ['th', attrs, 0]
    }
})

export const tableActions: ToolbarAction[] = [
    {
        name: 'Table',
        group: 'insert',
        icon: IconTable,
        tooltip: 'Insert Table',
        extensions: [
            CustomTable.configure({ resizable: false, allowTableNodeSelection: true }),
            TableRow,
            CustomTableCell,
            CustomTableHeader
        ],
        command: (e) => {
            const dialog = new Dialog('Table Properties')

            dialog.setContent(`
                <div style="display: flex; gap: 15px;">
                    <div class="form-group" style="flex: 1; margin-bottom: 15px;">
                        <label for="table-cols" style="display: block; margin-bottom: 5px; font-weight: bold;">Columns</label>
                        <input type="number" id="table-cols" class="form-control" value="2" min="1" max="10">
                    </div>
                    <div class="form-group" style="flex: 1; margin-bottom: 15px;">
                        <label for="table-rows" style="display: block; margin-bottom: 5px; font-weight: bold;">Rows</label>
                        <input type="number" id="table-rows" class="form-control" value="3" min="1" max="20">
                    </div>
                </div>
            `)

            dialog.addButton({
                label: 'Cancel',
                className: 'btn-default btn-outline-secondary',
                onClick: (d) => d.close()
            })

            dialog.addButton({
                label: 'Insert table',
                className: 'btn-primary',
                onClick: (d) => {
                    const rows = parseInt(d.getFieldValue('table-rows')) || 3
                    const cols = parseInt(d.getFieldValue('table-cols')) || 2

                    e.tiptap.chain().focus().insertTable({ rows, cols, withHeaderRow: false }).run()
                    d.close()
                }
            })

            dialog.open()
        },
        isActive: (e) => e.tiptap.isActive('table')
    }
]
