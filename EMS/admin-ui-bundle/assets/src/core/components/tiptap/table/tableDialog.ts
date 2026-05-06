import { applyHeaders, headerScope, isHeaderCell } from './tableHeader.ts'
import { updateCaption } from './tableCaption.ts'
import { Dialog } from '../../dialog.ts'
import { TiptapEditor } from '../editor.ts'
import { Editor } from '@tiptap/core'

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
            const last = node.lastChild
            if (last?.type.name === 'tableCaption') {
                caption = last.textContent
            }
            break
        }
    }

    return { attrs, caption, headers }
}

export function openTableDialog(e: TiptapEditor, mode: 'insert' | 'edit') {
    const dialog = new Dialog('Table Properties', { draggable: true })

    const current =
        mode === 'edit'
            ? getTableContext(e.tiptap)
            : {
                  attrs: { class: e.getWysiwygOptions()?.tableDefaultCss },
                  caption: '',
                  headers: 'none'
              }
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

    dialog.setContent(
        `<div style="display: flex; flex-direction: column; gap: 10px; width: 450px;">${html}</div>`
    )

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
                applyHeaders(e.tiptap, headers)
                updateCaption(e.tiptap, caption)
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
                                { type: 'table', attrs, content: tableRows },
                                {
                                    type: 'tableCaption',
                                    content: [{ type: 'text', text: caption }]
                                }
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
