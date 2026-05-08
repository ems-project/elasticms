import { Editor } from '@tiptap/core'
import { HtmlTransform } from '../types.ts'

import { TableHeader } from '@tiptap/extension-table'

export const CustomTableHeader = TableHeader.extend({
    addAttributes() {
        return {
            ...this.parent?.(),
            class: {
                default: null,
                parseHTML: (el) => el.getAttribute('class') || null,
                renderHTML: (attrs) => attrs.class ? { class: attrs.class } : {}
            },
            scope: {
                default: null,
                parseHTML: (el) => el.getAttribute('scope'),
                renderHTML: (attrs) => (attrs.scope ? { scope: attrs.scope } : {})
            }
        }
    }
})

export const tableTheadHtmlTransform: HtmlTransform = {
    name: 'tableThead',
    toEditor(doc) {
        doc.querySelectorAll('table').forEach((table) => {
            table
                .querySelectorAll(':scope > thead > tr, :scope > tbody > tr, :scope > tfoot > tr')
                .forEach((row) => {
                    table.appendChild(row)
                })
            table
                .querySelectorAll(':scope > thead, :scope > tbody, :scope > tfoot')
                .forEach((el) => el.remove())
        })
    },
    toOutput(doc) {
        doc.querySelectorAll('table').forEach((table) => {
            const rows = Array.from(table.querySelectorAll(':scope > tr, :scope > tbody > tr'))
            if (!rows.length) return

            table.querySelectorAll(':scope > tbody').forEach((el) => el.remove())

            const firstRow = rows[0]
            const allTh = Array.from(firstRow.children).every((c) => c.tagName === 'TH')

            if (allTh) {
                const thead = doc.createElement('thead')
                thead.appendChild(firstRow)
                const tbody = doc.createElement('tbody')
                rows.slice(1).forEach((r) => tbody.appendChild(r))
                table.appendChild(thead)
                table.appendChild(tbody)
            } else {
                const tbody = doc.createElement('tbody')
                rows.forEach((r) => tbody.appendChild(r))
                table.appendChild(tbody)
            }
        })
    }
}

export function isHeaderCell(headers: string, rowIdx: number, colIdx: number): boolean {
    switch (headers) {
        case 'both':
            return rowIdx === 0 || colIdx === 0
        case 'row':
            return rowIdx === 0
        case 'column':
            return colIdx === 0
        default:
            return false
    }
}

export function headerScope(headers: string, rowIdx: number): string | null {
    switch (headers) {
        case 'row':
            return 'col'
        case 'column':
            return 'row'
        case 'both':
            return rowIdx === 0 ? 'col' : 'row'
        default:
            return null
    }
}

export function applyHeaders(tiptap: Editor, headers: string) {
    const { $from } = tiptap.state.selection
    let tableNode = null
    let tablePos = 0

    for (let d = $from.depth; d > 0; d--) {
        if ($from.node(d).type.name === 'table') {
            tableNode = $from.node(d)
            tablePos = $from.before(d)
            break
        }
    }
    if (!tableNode) return

    const { tr } = tiptap.state
    const headerType = tiptap.schema.nodes.tableHeader
    const cellType = tiptap.schema.nodes.tableCell

    let offset = 1
    tableNode.forEach((row, _, rowIdx) => {
        let cellOffset = 0
        row.forEach((cell, _, colIdx) => {
            const shouldBeHeader = isHeaderCell(headers, rowIdx, colIdx)
            const targetType = shouldBeHeader ? headerType : cellType
            const scope = shouldBeHeader ? headerScope(headers, rowIdx) : null

            const pos = tablePos + offset + cellOffset + 1
            if (cell.type !== targetType || cell.attrs.scope !== scope) {
                tr.setNodeMarkup(pos, targetType, { ...cell.attrs, scope })
            }

            cellOffset += cell.nodeSize
        })
        offset += row.nodeSize
    })

    tiptap.view.dispatch(tr)
}
