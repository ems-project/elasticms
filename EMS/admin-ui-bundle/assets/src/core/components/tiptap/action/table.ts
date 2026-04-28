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

const CustomTable = Table.extend({
    addAttributes() {
        return {
            ...this.parent?.(),
            class: {
                default: null,
                parseHTML: (el) => el.getAttribute('class'),
                renderHTML: (attrs) => (attrs.class ? { class: attrs.class } : {})
            },
            id: {
                default: null,
                parseHTML: (el) => el.getAttribute('id'),
                renderHTML: (attrs) => (attrs.id ? { id: attrs.id } : {})
            },
            summary: {
                default: null,
                parseHTML: (el) => el.getAttribute('summary'),
                renderHTML: (attrs) => (attrs.summary ? { summary: attrs.summary } : {})
            }
        }
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
            TableCell,
            TableHeader,
            TableFigure,
            TableCaption
        ],
        htmlTransforms: [tableCaptionTransform, tableCleanupTransform],
        command: (e) => {
            const dialog = new Dialog('Table Properties', { draggable: true })

            dialog.setContent(`
                <div style="display: flex; gap: 15px;">
                    <div style="flex: 1; margin-bottom: 15px;">
                        <label for="table-cols">Columns</label>
                        <input type="number" id="table-cols" value="2" min="1" max="10">
                    </div>
                    <div style="flex: 1; margin-bottom: 15px;">
                        <label for="table-rows">Rows</label>
                        <input type="number" id="table-rows" value="3" min="1" max="20">
                    </div>
                </div>
                <div style="margin-bottom: 15px;">
                    <label for="table-caption">Caption</label>
                    <input type="text" id="table-caption" placeholder="Optional">
                </div>
                <div style="margin-bottom: 15px;">
                    <label for="table-summary">Summary</label>
                    <input type="text" id="table-summary" placeholder="Optional">
                </div>
                <div style="display: flex; gap: 15px;">
                    <div style="flex: 1; margin-bottom: 15px;">
                        <label for="table-id">ID</label>
                        <input type="text" id="table-id" placeholder="Optional">
                    </div>
                    <div style="flex: 1; margin-bottom: 15px;">
                        <label for="table-class">Class</label>
                        <input type="text" id="table-class" placeholder="Optional">
                    </div>
                </div>
            `)

            dialog.addButton({
                label: 'Apply',
                variant: 'primary',
                onClick: (d) => {
                    const rows = parseInt(d.getFieldValue('table-rows')) || 3
                    const cols = parseInt(d.getFieldValue('table-cols')) || 2
                    const caption = (d.getFieldValue('table-caption') || '').trim()
                    const tableId = (d.getFieldValue('table-id') || '').trim()
                    const tableClass = (d.getFieldValue('table-class') || '').trim()
                    const tableSummary = (d.getFieldValue('table-summary') || '').trim()

                    const tableAttrs: Record<string, string> = {}
                    if (tableId) tableAttrs.id = tableId
                    if (tableClass) tableAttrs.class = tableClass
                    if (tableSummary) tableAttrs.summary = tableSummary

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
                                    {
                                        type: 'table',
                                        attrs: tableAttrs,
                                        content: tableRows
                                    }
                                ]
                            })
                            .run()
                    } else {
                        e.tiptap
                            .chain()
                            .focus()
                            .insertContent({
                                type: 'table',
                                attrs: tableAttrs,
                                content: tableRows
                            })
                            .run()
                    }
                    d.close()
                }
            })

            dialog.addButton({
                label: 'Cancel',
                variant: 'secondary',
                onClick: (d) => d.close()
            })

            dialog.open()
        },
        isActive: (e) => e.tiptap.isActive('table') || e.tiptap.isActive('tableFigure')
    }
]