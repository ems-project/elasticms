import { HtmlTransform } from '../types.ts'

export const tableCleanHtmlTransform: HtmlTransform = {
    name: 'tableCleanup',
    toEditor(doc) {
        doc.querySelectorAll('table').forEach((table) => {
            table.querySelectorAll('colgroup').forEach((cg) => cg.remove())
            table.querySelectorAll('td, th').forEach((cell) => {
                if (cell.getAttribute('colspan') === '1') cell.removeAttribute('colspan')
                if (cell.getAttribute('rowspan') === '1') cell.removeAttribute('rowspan')
            })

            const style = table.getAttribute('style')
            if (style) {
                table.setAttribute('data-user-style', style)
                table.removeAttribute('style')
            }
        })
    },
    toOutput(doc) {
        doc.querySelectorAll('table').forEach((table) => {
            const parts: string[] = []

            const userStyle = table.getAttribute('data-user-style')
            if (userStyle) parts.push(userStyle)

            const w = table.getAttribute('width')
            const h = table.getAttribute('height')
            if (w && !userStyle?.includes('width')) parts.push(`width: ${w}`)
            if (h && !userStyle?.includes('height')) parts.push(`height: ${h}`)

            if (parts.length) {
                table.setAttribute('style', parts.join('; '))
            }

            table.removeAttribute('data-user-style')
            table.querySelectorAll('colgroup').forEach((cg) => cg.remove())

            table.querySelectorAll('td, th').forEach((cell) => {
                if (cell.getAttribute('colspan') === '1') cell.removeAttribute('colspan')
                if (cell.getAttribute('rowspan') === '1') cell.removeAttribute('rowspan')

                const style = cell.getAttribute('style')
                if (style) {
                    const cleaned = style.replace(/min-width\s*:[^;]+;?/gi, '').trim()
                    if (cleaned) cell.setAttribute('style', cleaned)
                    else cell.removeAttribute('style')
                }
            })
        })
    }
}
