import { Dialog } from '../../dialog.ts'
import { TiptapEditor } from '../editor.ts'
import { Editor } from '@tiptap/core'

function getCellContext(tiptap: Editor) {
    const { $from } = tiptap.state.selection
    for (let d = $from.depth; d > 0; d--) {
        const role = $from.node(d).type.spec.tableRole
        if (role === 'cell' || role === 'header_cell') {
            const pos = $from.before(d)
            const node = tiptap.state.doc.nodeAt(pos)
            if (!node) return null
            return { pos, attrs: { ...node.attrs } }
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

    const esc = (v: any) => (v ?? '').toString().replace(/"/g, '&quot;')

    const dialog = new Dialog('Cell Properties', { draggable: true })
    const a = cell.attrs
    const style = a.style || a.dataUserStyle || ''

    const width = parseStyle(style, 'width')
    const height = parseStyle(style, 'height')
    const wrap = parseStyle(style, 'white-space') === 'nowrap' ? 'nowrap' : 'wrap'
    const hAlign = parseStyle(style, 'text-align')
    const vAlign = parseStyle(style, 'vertical-align')

    const html = `
        <div style="display: flex; gap: 10px;">
            <div style="flex: 1">
                <label for="cell-colspan">Columns span</label>
                <input type="number" id="cell-colspan" value="${a.colspan || 1}" min="1">
            </div>
            <div style="flex: 1">
                <label for="cell-rowspan">Rows span</label>
                <input type="number" id="cell-rowspan" value="${a.rowspan || 1}" min="1">
            </div>
        </div>
        <div style="display: flex; gap: 10px;">
            <div style="flex: 1">
                <label for="cell-width">Width</label>
                <input type="text" id="cell-width" value="${esc(width)}" placeholder="100px, 25%">

            </div>
            <div style="flex: 1">
                <label for="cell-height">Height</label>
                <input type="text" id="cell-height" value="${esc(height)}" placeholder="50px">
            </div>
        </div>
        <div style="display: flex; gap: 10px;">
            <div style="flex: 1">
                <label for="cell-wrap">Word wrap</label>
                <select id="cell-wrap">
                    <option value="wrap"${wrap === 'wrap' ? ' selected' : ''}>Yes</option>
                    <option value="nowrap"${wrap === 'nowrap' ? ' selected' : ''}>No</option>
                </select>
            </div>
            <div style="flex: 1">
                <label for="cell-halign">Horizontal alignment</label>
                <select id="cell-halign">
                    <option value=""${!hAlign ? ' selected' : ''}>Not defined</option>
                    <option value="left"${hAlign === 'left' ? ' selected' : ''}>Left</option>
                    <option value="center"${hAlign === 'center' ? ' selected' : ''}>Center</option>
                    <option value="right"${hAlign === 'right' ? ' selected' : ''}>Right</option>
                    <option value="justify"${hAlign === 'justify' ? ' selected' : ''}>Justify</option>
                </select>
            </div>
            <div style="flex: 1">
                <label for="cell-valign">Vertical alignment</label>
                <select id="cell-valign">
                    <option value=""${!vAlign ? ' selected' : ''}>Not defined</option>
                    <option value="top"${vAlign === 'top' ? ' selected' : ''}>Top</option>
                    <option value="middle"${vAlign === 'middle' ? ' selected' : ''}>Middle</option>
                    <option value="bottom"${vAlign === 'bottom' ? ' selected' : ''}>Bottom</option>
                </select>
            </div>
        </div>`

    dialog.setContent(
        `<div style="display: flex; flex-direction: column; gap: 10px; width: 450px;">${html}</div>`
    )

    dialog.addButton({
        label: 'Apply',
        variant: 'primary',
        onClick: (d) => {
            const field = (name: string) => (d.getFieldValue(`cell-${name}`) || '').trim() || null

            const parts: string[] = []
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
