import { Node, Editor } from '@tiptap/core'
import { Table, TableCell, TableHeader, TableRow } from '@tiptap/extension-table'
import IconTable from '@tabler/icons/outline/table.svg?raw'
import { ContextMenuItem, TiptapModule } from '../types.ts'
import { Dialog } from '../../dialog.ts'
import { TiptapEditor } from '../editor.ts'

import {
    TableCaption,
    tableCaptionHtmlTransform,
    TableFigure,
    updateCaption
} from '../table/tableCaption.ts'

import { tableCleanHtmlTransform } from '../table/table.ts'

export const tableModule: TiptapModule = {
    extensions: getExtensions(),
    htmlTransforms: [tableCaptionHtmlTransform, tableCleanHtmlTransform],
    toolbarGroup: 'insert',
    toolbar: [
        {
            name: 'Table',
            icon: IconTable,
            tooltip: 'Insert Table',
            command: (e) => openTableDialog(e, 'insert'),
            isActive: (e) => e.tiptap.isActive('table') || e.tiptap.isActive('tableFigure')
        }
    ],
    contextMenuNode: 'table',
    contextMenu: getContextMenuItems()
}

function getExtensions(): Node[] {
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
                },
                dataUserStyle: {
                    default: null,
                    parseHTML: (el) => el.getAttribute('data-user-style'),
                    renderHTML: (attrs) =>
                        attrs.dataUserStyle
                            ? { 'data-user-style': attrs.dataUserStyle, style: attrs.dataUserStyle }
                            : {}
                },
                align: {
                    default: null,
                    parseHTML: (el) => el.getAttribute('align'),
                    renderHTML: (attrs) => (attrs.align ? { align: attrs.align } : {})
                },
                border: {
                    default: null,
                    parseHTML: (el) => el.getAttribute('border'),
                    renderHTML: (attrs) => (attrs.border ? { border: attrs.border } : {})
                },
                cellpadding: {
                    default: null,
                    parseHTML: (el) => el.getAttribute('cellpadding'),
                    renderHTML: (attrs) =>
                        attrs.cellpadding ? { cellpadding: attrs.cellpadding } : {}
                },
                cellspacing: {
                    default: null,
                    parseHTML: (el) => el.getAttribute('cellspacing'),
                    renderHTML: (attrs) =>
                        attrs.cellspacing ? { cellspacing: attrs.cellspacing } : {}
                }
            }
        }
    })

    return [
        CustomTable.configure({ resizable: false, allowTableNodeSelection: true }),
        TableRow,
        TableCell,
        TableHeader,
        TableFigure,
        TableCaption
    ]
}

function getContextMenuItems(): ContextMenuItem[] {
    return [
        {
            label: 'Insert row above',
            parent: 'Row',
            order: 0,
            command: (e) => e.tiptap.chain().focus().addRowBefore().run()
        },
        {
            label: 'Insert row below',
            parent: 'Row',
            order: 1,
            command: (e) => e.tiptap.chain().focus().addRowAfter().run()
        },
        {
            label: 'Delete row',
            parent: 'Row',
            order: 2,
            command: (e) => e.tiptap.chain().focus().deleteRow().run()
        },
        {
            label: 'Table properties',
            order: 98,
            command: (e) => openTableDialog(e, 'edit')
        },
        {
            label: 'Delete table',
            order: 99,
            command: (e) => commandDeleteTable(e.tiptap)
        }
    ]
}

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

function getTableContext(tiptap: Editor): { attrs: Record<string, any>; caption: string } {
    const { $from } = tiptap.state.selection
    let attrs: Record<string, any> = {}
    let caption = ''

    for (let d = $from.depth; d > 0; d--) {
        const node = $from.node(d)
        if (node.type.name === 'table') {
            attrs = { ...node.attrs }
        }
        if (node.type.name === 'tableFigure') {
            const first = node.firstChild
            if (first?.type.name === 'tableCaption') {
                caption = first.textContent
            }
            break
        }
    }

    return { attrs, caption }
}

function openTableDialog(e: TiptapEditor, mode: 'insert' | 'edit') {
    const dialog = new Dialog('Table Properties', { draggable: true })

    const current = mode === 'edit' ? getTableContext(e.tiptap) : { attrs: {}, caption: '' }
    const a = current.attrs

    const esc = (v: any) => (v ?? '').toString().replace(/"/g, '&quot;')

    dialog.setContent(`
        ${
            mode === 'insert'
                ? `
        <div style="display: flex; gap: 15px;">
            <div style="flex: 1; margin-bottom: 15px;">
                <label for="table-cols">Columns</label>
                <input type="number" id="table-cols" value="2" min="1" max="10">
            </div>
            <div style="flex: 1; margin-bottom: 15px;">
                <label for="table-rows">Rows</label>
                <input type="number" id="table-rows" value="3" min="1" max="20">
            </div>
        </div>`
                : ''
        }
        <div style="display: flex; gap: 15px;">
            <div style="flex: 1; margin-bottom: 15px;">
                <label for="table-border">Border</label>
                <input type="number" id="table-border" value="${esc(a.border)}" min="0" placeholder="Optional">
            </div>
            <div style="flex: 1; margin-bottom: 15px;">
                <label for="table-cellpadding">Cell padding</label>
                <input type="number" id="table-cellpadding" value="${esc(a.cellpadding)}" min="0" placeholder="Optional">
            </div>
            <div style="flex: 1; margin-bottom: 15px;">
                <label for="table-cellspacing">Cell spacing</label>
                <input type="number" id="table-cellspacing" value="${esc(a.cellspacing)}" min="0" placeholder="Optional">
            </div>
        </div>
        <div style="margin-bottom: 15px;">
            <label for="table-caption">Caption</label>
            <input type="text" id="table-caption" value="${esc(current.caption)}" placeholder="Optional">
        </div>
        <div style="margin-bottom: 15px;">
            <label for="table-summary">Summary</label>
            <input type="text" id="table-summary" value="${esc(a.summary)}" placeholder="Optional">
        </div>
        <div style="margin-bottom: 15px;">
            <label for="table-style">Style</label>
            <input type="text" id="table-style" value="${esc(a.dataUserStyle)}" placeholder="Optional">
        </div>
        <div style="margin-bottom: 15px;">
            <label for="table-align">Align</label>
            <select id="table-align">
                <option value=""${!a.align ? ' selected' : ''}>Not defined</option>
                <option value="left"${a.align === 'left' ? ' selected' : ''}>Left</option>
                <option value="center"${a.align === 'center' ? ' selected' : ''}>Center</option>
                <option value="right"${a.align === 'right' ? ' selected' : ''}>Right</option>
            </select>
        </div>
        <div style="display: flex; gap: 15px;">
            <div style="flex: 1; margin-bottom: 15px;">
                <label for="table-id">ID</label>
                <input type="text" id="table-id" value="${esc(a.id)}" placeholder="Optional">
            </div>
            <div style="flex: 1; margin-bottom: 15px;">
                <label for="table-class">Class</label>
                <input type="text" id="table-class" value="${esc(a.class)}" placeholder="Optional">
            </div>
        </div>
    `)

    dialog.addButton({
        label: 'Apply',
        variant: 'primary',
        onClick: (d) => {
            const caption = (d.getFieldValue('table-caption') || '').trim()

            const attrs: Record<string, string | null> = {
                id: (d.getFieldValue('table-id') || '').trim() || null,
                class: (d.getFieldValue('table-class') || '').trim() || null,
                summary: (d.getFieldValue('table-summary') || '').trim() || null,
                dataUserStyle: (d.getFieldValue('table-style') || '').trim() || null,
                align: (d.getFieldValue('table-align') || '').trim() || null,
                border: (d.getFieldValue('table-border') || '').trim() || null,
                cellpadding: (d.getFieldValue('table-cellpadding') || '').trim() || null,
                cellspacing: (d.getFieldValue('table-cellspacing') || '').trim() || null
            }

            if (mode === 'edit') {
                e.tiptap.chain().focus().updateAttributes('table', attrs).run()
                updateCaption(e.tiptap, caption)
            } else {
                const rows = parseInt(d.getFieldValue('table-rows')) || 3
                const cols = parseInt(d.getFieldValue('table-cols')) || 2

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
                                { type: 'table', attrs, content: tableRows }
                            ]
                        })
                        .run()
                } else {
                    e.tiptap
                        .chain()
                        .focus()
                        .insertContent({ type: 'table', attrs, content: tableRows })
                        .run()
                }
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
}
