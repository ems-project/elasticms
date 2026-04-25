import { Node, mergeAttributes } from '@tiptap/core'
import { Table, TableCell, TableHeader, TableRow } from '@tiptap/extension-table'
import IconTable from '@tabler/icons/outline/table.svg?raw'
import { HtmlTransform, ToolbarAction } from '../types.ts'
import { Dialog } from '../../dialog.ts'

const TableFigure = Node.create({
    name: 'tableFigure',
    group: 'block',
    content: 'tableCaption? table',
    isolating: true,

    parseHTML() {
        return [{ tag: 'figure[data-type="table"]' }]
    },

    renderHTML({ HTMLAttributes }) {
        return ['figure', mergeAttributes(HTMLAttributes, { 'data-type': 'table' }), 0]
    }
})

const TableCaption = Node.create({
    name: 'tableCaption',
    content: 'inline*',
    defining: true,

    parseHTML() {
        return [{ tag: 'figcaption' }]
    },

    renderHTML({ HTMLAttributes }) {
        return ['figcaption', mergeAttributes(HTMLAttributes), 0]
    },

    addKeyboardShortcuts() {
        return {
            Backspace: () => {
                const { $from, empty } = this.editor.state.selection
                if (!empty) return false
                if ($from.parent.type.name !== this.name) return false
                if ($from.parent.content.size > 0) return false

                return this.editor
                    .chain()
                    .deleteRange({ from: $from.before(), to: $from.after() })
                    .focus()
                    .run()
            }
        }
    }
})

const tableCaptionTransform: HtmlTransform = {
    name: 'tableCaption',

    toEditor(doc) {
        doc.querySelectorAll('table').forEach((table) => {
            const caption = table.querySelector(':scope > caption')
            if (!caption) return
            const figure = doc.createElement('figure')
            figure.setAttribute('data-type', 'table')
            const figcaption = doc.createElement('figcaption')
            figcaption.innerHTML = caption.innerHTML
            caption.remove()
            table.replaceWith(figure)
            figure.appendChild(figcaption)
            figure.appendChild(table)
        })
    },

    toOutput(doc) {
        doc.querySelectorAll('figure[data-type="table"]').forEach((fig) => {
            const figcaption = fig.querySelector(':scope > figcaption')
            const table = fig.querySelector(':scope > table')
            if (!table) {
                fig.remove()
                return
            }
            if (figcaption) {
                const caption = doc.createElement('caption')
                caption.innerHTML = figcaption.innerHTML
                table.insertBefore(caption, table.firstChild)
            }
            fig.replaceWith(table)
        })
    }
}

const tableCleanupTransform: HtmlTransform = {
    name: 'tableCleanup',

    toOutput(doc) {
        doc.querySelectorAll('table').forEach((table) => {
            table.removeAttribute('style')
            table.querySelector(':scope > colgroup')?.remove()
            table.querySelectorAll('td, th').forEach((cell) => {
                const style = cell.getAttribute('style')
                if (style) {
                    const cleaned = style.replace(/min-width\s*:[^;]+;?/gi, '').trim()
                    if (cleaned) cell.setAttribute('style', cleaned)
                    else cell.removeAttribute('style')
                }
                if (cell.getAttribute('colwidth')) cell.removeAttribute('colwidth')
                if (cell.getAttribute('colspan') === '1') cell.removeAttribute('colspan')
                if (cell.getAttribute('rowspan') === '1') cell.removeAttribute('rowspan')
            })
        })
    }
}

export const tableActions: ToolbarAction[] = [
    {
        name: 'Table',
        group: 'insert',
        icon: IconTable,
        tooltip: 'Insert Table',
        extensions: [
            Table.configure({ resizable: false, allowTableNodeSelection: true }),
            TableRow,
            TableCell,
            TableHeader,
            TableFigure,
            TableCaption
        ],
        htmlTransforms: [tableCaptionTransform, tableCleanupTransform],
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
                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="table-caption" style="display: block; margin-bottom: 5px; font-weight: bold;">Caption</label>
                    <input type="text" id="table-caption" class="form-control" placeholder="Optional">
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
                    const caption = (d.getFieldValue('table-caption') || '').trim()

                    const tableRows = Array.from({ length: rows }, () => ({
                        type: 'tableRow',
                        content: Array.from({ length: cols }, () => ({
                            type: 'tableCell',
                            content: [{ type: 'paragraph' }]
                        }))
                    }))

                    if (caption) {
                        e.tiptap
                            .chain()
                            .focus()
                            .insertContent({
                                type: 'tableFigure',
                                content: [
                                    {
                                        type: 'tableCaption',
                                        content: [{ type: 'text', text: caption }]
                                    },
                                    { type: 'table', content: tableRows }
                                ]
                            })
                            .run()
                    } else {
                        e.tiptap
                            .chain()
                            .focus()
                            .insertTable({ rows, cols, withHeaderRow: false })
                            .run()
                    }
                    d.close()
                }
            })

            dialog.open()
        },
        isActive: (e) => e.tiptap.isActive('table') || e.tiptap.isActive('tableFigure')
    }
]
