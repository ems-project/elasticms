import { Node, Editor } from '@tiptap/core'
import { Table, TableCell, TableRow } from '@tiptap/extension-table'
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
import {
    applyHeaders,
    CustomTableHeader,
    headerScope,
    isHeaderCell,
    tableTheadHtmlTransform
} from '../table/tableHeader.ts'

export const tableModule: TiptapModule = {
    extensions: getExtensions(),
    htmlTransforms: [tableCaptionHtmlTransform, tableCleanHtmlTransform, tableTheadHtmlTransform],
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
                width: {
                    default: null,
                    parseHTML: (el) => {
                        if (el.style.width) return el.style.width
                        const us = el.getAttribute('data-user-style')
                        return us?.match(/(?:^|;\s*)width\s*:\s*([^;]+)/i)?.[1]?.trim() || null
                    },
                    renderHTML: () => ({})
                },
                height: {
                    default: null,
                    parseHTML: (el) => {
                        if (el.style.height) return el.style.height
                        const us = el.getAttribute('data-user-style')
                        return us?.match(/(?:^|;\s*)height\s*:\s*([^;]+)/i)?.[1]?.trim() || null
                    },
                    renderHTML: () => ({})
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
                },
                dataUserStyle: {
                    default: null,
                    parseHTML: (el) => {
                        const s = el.getAttribute('data-user-style')
                        if (!s) return null
                        const cleaned = s
                            .replace(/\b(width|height)\s*:[^;]+;?/gi, '')
                            .trim()
                            .replace(/;$/, '')
                        return cleaned || null
                    },
                    renderHTML: (attrs) => {
                        const parts: string[] = []
                        if (attrs.width) parts.push(`width: ${attrs.width}`)
                        if (attrs.height) parts.push(`height: ${attrs.height}`)
                        if (attrs.dataUserStyle) parts.push(attrs.dataUserStyle)
                        const style = parts.join('; ')
                        return style
                            ? { 'data-user-style': attrs.dataUserStyle || null, style }
                            : {}
                    }
                }
            }
        }
    })

    return [
        CustomTable.configure({ resizable: false, allowTableNodeSelection: true }),
        TableRow,
        TableCell,
        CustomTableHeader,
        TableFigure,
        TableCaption
    ]
}

function getContextMenuItems(): ContextMenuItem[] {
    return [
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
        },
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

function getTableContext(tiptap: Editor): {
    attrs: Record<string, any>
    caption: string
    headers: string
} {
    const { $from } = tiptap.state.selection
    let attrs: Record<string, any> = {}
    let caption = ''
    let headers = 'none'

    for (let d = $from.depth; d > 0; d--) {
        const node = $from.node(d)
        if (node.type.name === 'table') {
            attrs = { ...node.attrs }

            let firstRowTh = false
            let firstColTh = false
            const firstRow = node.firstChild
            if (firstRow) {
                firstRowTh = true
                firstRow.forEach((cell) => {
                    if (cell.type.name !== 'tableHeader') firstRowTh = false
                })
            }
            node.forEach((row, _, i) => {
                if (i === 0) return
                const first = row.firstChild
                if (!first || first.type.name !== 'tableHeader') firstColTh = false
            })
            if (node.childCount > 1) {
                firstColTh = true
                node.forEach((row, _, i) => {
                    if (i === 0) return
                    const first = row.firstChild
                    if (!first || first.type.name !== 'tableHeader') firstColTh = false
                })
            }

            if (firstRowTh && firstColTh) headers = 'both'
            else if (firstRowTh) headers = 'row'
            else if (firstColTh) headers = 'column'
        }
        if (node.type.name === 'tableFigure') {
            const first = node.firstChild
            if (first?.type.name === 'tableCaption') {
                caption = first.textContent
            }
            break
        }
    }

    return { attrs, caption, headers }
}

function openTableDialog(e: TiptapEditor, mode: 'insert' | 'edit') {
    const dialog = new Dialog('Table Properties', { draggable: true })

    const current =
        mode === 'edit' ? getTableContext(e.tiptap) : { attrs: {}, caption: '', headers: 'none' }
    const a = current.attrs

    const esc = (v: any) => (v ?? '').toString().replace(/"/g, '&quot;')

    let html = ''

    if (mode === 'insert') {
        html += `
        <div style="display: flex; gap: 10px;">
            <div>
                <label for="table-cols">Columns</label>
                <input type="number" id="table-cols" value="2" min="1" max="10" style="width: 60px;">
            </div>
            <div>
                <label for="table-rows">Rows</label>
                <input type="number" id="table-rows" value="3" min="1" max="20"  style="width: 60px;">
            </div>
        </div>`
    }

    html += `
        <div style="display: flex; gap: 10px;">
            <div style="flex: 1">
                <label for="table-headers">Headers</label>
                <select id="table-headers">
                    <option value="none"${current.headers === 'none' ? ' selected' : ''}>None</option>
                    <option value="row"${current.headers === 'row' ? ' selected' : ''}>First row</option>
                    <option value="column"${current.headers === 'column' ? ' selected' : ''}>First column</option>
                    <option value="both"${current.headers === 'both' ? ' selected' : ''}>Both</option>
                </select>
            </div>
             <div style="flex: 1; display: flex; gap: 10px;">        
                <div style="flex: 1">
                    <label for="table-width">Width</label>
                    <input type="text" id="table-width" value="${esc(a.width)}" placeholder="50%, 300px">
                </div>
                <div style="flex: 1">
                    <label for="table-height">Height</label>
                    <input type="text" id="table-height" value="${esc(a.height)}" placeholder="200px">
                </div>
            </div>
        </div>
        <div style="display: flex; gap: 10px;">
            <div style="flex: 1">
                <label for="table-align">Alignment</label>
                <select id="table-align">
                    <option value=""${!a.align ? ' selected' : ''}>Not defined</option>
                    <option value="left"${a.align === 'left' ? ' selected' : ''}>Left</option>
                    <option value="center"${a.align === 'center' ? ' selected' : ''}>Center</option>
                    <option value="right"${a.align === 'right' ? ' selected' : ''}>Right</option>
                </select>
            </div>
            <div style="flex: 1; display: flex; gap: 10px;">            
                <div style="flex: 1">
                    <label for="table-border">Border</label>
                    <input type="number" id="table-border" value="${esc(a.border)}" min="0" placeholder="0">
                </div>
                <div style="flex: 1">
                    <label for="table-cellpadding">Padding</label>
                    <input type="number" id="table-cellpadding" value="${esc(a.cellpadding)}" min="0" placeholder="0">
                </div>
                <div style="flex: 1">
                    <label for="table-cellspacing">Spacing</label>
                    <input type="number" id="table-cellspacing" value="${esc(a.cellspacing)}" min="0" placeholder="0">
                </div>
            </div>
        </div>
        <div style="display: flex; gap: 10px;">
            <div style="flex: 1">
                <label for="table-class">Class</label>
                <input type="text" id="table-class" value="${esc(a.class)}">
            </div>
            <div style="flex: 1">
                <label for="table-id">ID</label>
                <input type="text" id="table-id" value="${esc(a.id)}">
            </div>
            <div style="flex: 1">
                <label for="table-style">Style</label>
                <input type="text" id="table-style" value="${esc(a.dataUserStyle)}">
            </div>
        </div>
        <div>
            <label for="table-caption">Caption</label>
            <input type="text" id="table-caption" value="${esc(current.caption)}">
        </div>
        <div>
            <label for="table-summary">Summary</label>
            <input type="text" id="table-summary" value="${esc(a.summary)}">      
        </div>`

    dialog.setContent(`<div style="display: flex; flex-direction: column; gap: 10px; width: 450px;">${html}</div>`)

    dialog.addButton({
        label: 'Apply',
        variant: 'primary',
        onClick: (d) => {
            const field = (name: string) => (d.getFieldValue(`table-${name}`) || '').trim() || null

            const caption = field('caption') ?? ''
            const headers = field('headers') ?? 'none'

            const attrs: Record<string, string | null> = {
                id: field('id'),
                class: field('class'),
                summary: field('summary'),
                dataUserStyle: field('style'),
                align: field('align'),
                border: field('border'),
                cellpadding: field('cellpadding'),
                cellspacing: field('cellspacing'),
                width: field('width'),
                height: field('height')
            }

            if (mode === 'edit') {
                e.tiptap.chain().focus().updateAttributes('table', attrs).run()
                updateCaption(e.tiptap, caption)
                applyHeaders(e.tiptap, headers)
            } else {
                const rows = parseInt(d.getFieldValue('table-rows')) || 3
                const cols = parseInt(d.getFieldValue('table-cols')) || 2

                const tableRows = Array.from({ length: rows }, (_, rowIdx) => ({
                    type: 'tableRow',
                    content: Array.from({ length: cols }, (_, colIdx) => {
                        const isHeader = isHeaderCell(headers, rowIdx, colIdx)
                        return {
                            type: isHeader ? 'tableHeader' : 'tableCell',
                            attrs: isHeader ? { scope: headerScope(headers, rowIdx) } : {},
                            content: [{ type: 'paragraph' }]
                        }
                    })
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
