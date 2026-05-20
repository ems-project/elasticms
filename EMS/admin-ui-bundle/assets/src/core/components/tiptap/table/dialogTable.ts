import { applyHeaders, headerScope, isHeaderCell } from './header.ts'
import { updateCaption } from './caption.ts'
import { TiptapEditor } from '../editor.ts'
import { Editor } from '@tiptap/core'
import { escapeHtml } from '../helper.ts'

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

function parseStyle(style: string, prop: string): string {
    if (!style) return ''
    const match = style.match(new RegExp(`${prop}\\s*:\\s*([^;]+)`))
    return match ? match[1].trim() : ''
}

function stripStyleProps(style: string, ...props: string[]): string {
    return style
        .split(';')
        .map((s) => s.trim())
        .filter((s) => s && !props.some((p) => new RegExp(`^${p}\\s*:`, 'i').test(s)))
        .join('; ')
}

export function openTableDialog(e: TiptapEditor, mode: 'insert' | 'edit') {
    const dialog = e.createDialog('table_properties')

    const current =
        mode === 'edit'
            ? getTableContext(e.tiptap)
            : {
                  attrs: { class: e.getWysiwygOptions()?.tableDefaultCss },
                  caption: '',
                  headers: 'none'
              }
    const a = current.attrs

    const userStyle = a.dataUserStyle || ''
    const width = parseStyle(userStyle, 'width') || a.width || ''
    const height = parseStyle(userStyle, 'height') || a.height || ''
    const displayStyle = stripStyleProps(userStyle, 'width', 'height')

    let html = ''

    if (mode === 'insert') {
        html += `
        <div style="display: flex; gap: 10px;">
            <div>
                <label for="table-cols">${e.trans('table_columns')}</label>
                <input type="number" id="table-cols" value="2" min="1" max="10" style="width: 60px;">
            </div>
            <div>
                <label for="table-rows">${e.trans('table_rows')}</label>
                <input type="number" id="table-rows" value="3" min="1" max="20"  style="width: 60px;">
            </div>
        </div>`
    }

    html += `
        <div style="display: flex; gap: 10px;">
            <div style="flex: 1">
                <label for="table-headers">${e.trans('table_headers')}</label>
                <select id="table-headers">
                    <option value="none"${current.headers === 'none' ? ' selected' : ''}>${e.trans('select')}</option>
                    <option value="row"${current.headers === 'row' ? ' selected' : ''}>${e.trans('table_headers_first_row')}</option>
                    <option value="column"${current.headers === 'column' ? ' selected' : ''}>${e.trans('table_headers_first_column')}</option>
                    <option value="both"${current.headers === 'both' ? ' selected' : ''}>${e.trans('table_headers_both')}</option>
                </select>
            </div>
             <div style="flex: 1; display: flex; gap: 10px;">        
                <div style="flex: 1">
                    <label for="table-width">${e.trans('width')}</label>
                    <input type="text" id="table-width" value="${escapeHtml(width)}" placeholder="50%, 300px">
                </div>
                <div style="flex: 1">
                    <label for="table-height">${e.trans('height')}</label>
                    <input type="text" id="table-height" value="${escapeHtml(height)}" placeholder="200px">
                </div>
            </div>
        </div>
        <div style="display: flex; gap: 10px;">
            <div style="flex: 1">
                <label for="table-align">${e.trans('alignment')}</label>
                <select id="table-align">
                    <option value=""${!a.align ? ' selected' : ''}>${e.trans('select')}</option>
                    <option value="left"${a.align === 'left' ? ' selected' : ''}>${e.trans('align_left')}</option>
                    <option value="center"${a.align === 'center' ? ' selected' : ''}>${e.trans('align_center')}</option>
                    <option value="right"${a.align === 'right' ? ' selected' : ''}>${e.trans('align_right')}</option>
                </select>
            </div>
            <div style="flex: 1; display: flex; gap: 10px;">            
                <div style="flex: 1">
                    <label for="table-border">${e.trans('border')}</label>
                    <input type="number" id="table-border" value="${escapeHtml(a.border)}" min="0" placeholder="0">
                </div>
                <div style="flex: 1">
                    <label for="table-cellpadding">${e.trans('padding')}</label>
                    <input type="number" id="table-cellpadding" value="${escapeHtml(a.cellpadding)}" min="0" placeholder="0">
                </div>
                <div style="flex: 1">
                    <label for="table-cellspacing">${e.trans('spacing')}</label>
                    <input type="number" id="table-cellspacing" value="${escapeHtml(a.cellspacing)}" min="0" placeholder="0">
                </div>
            </div>
        </div>
        <div style="display: flex; gap: 10px;">
            <div style="flex: 1">
                <label for="table-class">${e.trans('class')}</label>
                <input type="text" id="table-class" value="${escapeHtml(a.class)}">
            </div>
            <div style="flex: 1">
                <label for="table-id">${e.trans('id')}</label>
                <input type="text" id="table-id" value="${escapeHtml(a.id)}">
            </div>
            <div style="flex: 1">
                <label for="table-style">${e.trans('style')}</label>
                <input type="text" id="table-style" value="${escapeHtml(displayStyle)}">
            </div>
        </div>
        <div>
            <label for="table-caption">${e.trans('caption')}</label>
            <input type="text" id="table-caption" value="${escapeHtml(current.caption)}">
        </div>
        <div>
            <label for="table-summary">${e.trans('summary')}</label>
            <input type="text" id="table-summary" value="${escapeHtml(a.summary)}">      
        </div>`

    dialog.setContent(
        `<div style="display: flex; flex-direction: column; gap: 10px; width: 450px;">${html}</div>`
    )

    dialog.addButton({
        label: e.trans('button_apply'),
        variant: 'primary',
        onClick: (d) => {
            const field = (name: string) => (d.getFieldValue(`table-${name}`) || '').trim() || null

            const caption = field('caption') ?? ''
            const headers = field('headers') ?? 'none'

            const styleBase = stripStyleProps(field('style') || '', 'width', 'height')
            const w = field('width')
            const h = field('height')
            const styleParts = [styleBase, w && `width: ${w}`, h && `height: ${h}`].filter(
                Boolean
            ) as string[]

            const attrs: Record<string, string | null> = {
                id: field('id'),
                class: field('class'),
                summary: field('summary'),
                dataUserStyle: styleParts.length ? styleParts.join('; ') : null,
                align: field('align'),
                border: field('border'),
                cellpadding: field('cellpadding'),
                cellspacing: field('cellspacing'),
                width: null,
                height: null
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
        label: e.trans('button_cancel'),
        variant: 'secondary',
        onClick: (d) => d.close()
    })

    dialog.open()
}
