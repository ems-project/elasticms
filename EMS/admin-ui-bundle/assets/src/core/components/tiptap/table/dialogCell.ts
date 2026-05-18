import { TiptapEditor } from '../editor.ts'
import { Editor } from '@tiptap/core'
import { escapeHtml } from '../helper.ts'

function getCellContext(tiptap: Editor) {
    const { $from } = tiptap.state.selection
    for (let d = $from.depth; d > 0; d--) {
        const role = $from.node(d).type.spec.tableRole
        if (role === 'cell' || role === 'header_cell') {
            const pos = $from.before(d)
            const node = tiptap.state.doc.nodeAt(pos)
            if (!node) return null
            return { pos, node, attrs: { ...node.attrs } }
        }
    }
    return null
}

function parseStyle(style: string | null | undefined, prop: string): string {
    if (!style) return ''
    const match = style.match(new RegExp(`${prop}\\s*:\\s*([^;]+)`))
    return match ? match[1].trim() : ''
}

export function openCellDialog(e: TiptapEditor) {
    const cell = getCellContext(e.tiptap)
    if (!cell) return

    const dialog = e.createDialog('table_cell_properties')
    const a = cell.attrs
    const style = a.style || a.dataUserStyle || ''

    const width = parseStyle(style, 'width')
    const height = parseStyle(style, 'height')
    const wrap = parseStyle(style, 'white-space') === 'nowrap' ? 'nowrap' : 'wrap'
    const hAlign = parseStyle(style, 'text-align')
    const vAlign = parseStyle(style, 'vertical-align')

    const isHeader = cell.node.type.name === 'tableHeader'

    const html = `
        <div style="display: flex; gap: 10px;">
            <div style="flex: 1">
                <label for="cell-type">${e.trans('table_cell_type')}</label>
                <select id="cell-type">
                    <option value="data"${!isHeader ? ' selected' : ''}>${e.trans('table_cell_type_data')}</option>
                    <option value="header"${isHeader ? ' selected' : ''}>${e.trans('table_cell_type_header')}</option>
                </select>
            </div>
        </div>
        <div style="display: flex; gap: 10px;">
            <div style="flex: 1">
                <label for="cell-colspan">${e.trans('table_cell_span_columns')}</label>
                <input type="number" id="cell-colspan" value="${a.colspan || 1}" min="1">
            </div>
            <div style="flex: 1">
                <label for="cell-rowspan">${e.trans('table_cell_span_rows')}</label>
                <input type="number" id="cell-rowspan" value="${a.rowspan || 1}" min="1">
            </div>
        </div>
        <div style="display: flex; gap: 10px;">
            <div style="flex: 1">
                <label for="cell-width">${e.trans('width')}</label>
                <input type="text" id="cell-width" value="${escapeHtml(width)}" placeholder="100px, 25%">

            </div>
            <div style="flex: 1">
                <label for="cell-height">${e.trans('height')}</label>
                <input type="text" id="cell-height" value="${escapeHtml(height)}" placeholder="50px">
            </div>
        </div>
        <div style="display: flex; gap: 10px;">
            <div style="flex: 1">
                <label for="cell-wrap">${e.trans('word_wrap')}</label>
                <select id="cell-wrap">
                    <option value="wrap"${wrap === 'wrap' ? ' selected' : ''}>${e.trans('choice_yes')}</option>
                    <option value="nowrap"${wrap === 'nowrap' ? ' selected' : ''}>${e.trans('choice_no')}</option>
                </select>
            </div>
            <div style="flex: 1">
                <label for="cell-halign">${e.trans('align_horizontal')}</label>
                <select id="cell-halign">
                    <option value=""${!hAlign ? ' selected' : ''}>${e.trans('select')}</option>
                    <option value="left"${hAlign === 'left' ? ' selected' : ''}>${e.trans('align_left')}</option>
                    <option value="center"${hAlign === 'center' ? ' selected' : ''}>${e.trans('align_center')}</option>
                    <option value="right"${hAlign === 'right' ? ' selected' : ''}>${e.trans('align_right')}</option>
                    <option value="justify"${hAlign === 'justify' ? ' selected' : ''}>${e.trans('align_justify')}</option>
                </select>
            </div>
            <div style="flex: 1">
                <label for="cell-valign">${e.trans('align_vertical')}</label>
                <select id="cell-valign">
                    <option value=""${!vAlign ? ' selected' : ''}>${e.trans('select')}</option>
                    <option value="top"${vAlign === 'top' ? ' selected' : ''}>${e.trans('align_top')}</option>
                    <option value="middle"${vAlign === 'middle' ? ' selected' : ''}>${e.trans('align_middle')}</option>
                    <option value="bottom"${vAlign === 'bottom' ? ' selected' : ''}>${e.trans('align_bottom')}</option>
                </select>
            </div>
        </div>`

    dialog.setContent(
        `<div style="display: flex; flex-direction: column; gap: 10px; width: 450px;">${html}</div>`
    )

    dialog.addButton({
        label: e.trans('button_apply'),
        variant: 'primary',
        onClick: (d) => {
            const field = (name: string) => (d.getFieldValue(`cell-${name}`) || '').trim() || null

            const parts: string[] = []
            const typeVal = field('type')
            const wrapVal = field('wrap')
            const hVal = field('halign')
            const vVal = field('valign')
            const width = field('width')
            const height = field('height')

            if (wrapVal === 'nowrap') parts.push('white-space: nowrap')
            if (hVal) parts.push(`text-align: ${hVal}`)
            if (vVal) parts.push(`vertical-align: ${vVal}`)
            if (width) parts.push(`width: ${width}`)
            if (height) parts.push(`height: ${height}`)

            const attrs: Record<string, any> = {
                colspan: parseInt(field('colspan') ?? '1') || 1,
                rowspan: parseInt(field('rowspan') ?? '1') || 1,
                dataUserStyle: parts.length ? parts.join('; ') : null
            }

            e.tiptap.chain().focus().updateAttributes('tableCell', attrs).run()
            e.tiptap.chain().focus().updateAttributes('tableHeader', attrs).run()

            const currentIsHeader = cell.node.type.name === 'tableHeader'
            const wantHeader = typeVal === 'header'

            if (wantHeader !== currentIsHeader) {
                e.tiptap.chain().focus().toggleHeaderCell().run()
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
