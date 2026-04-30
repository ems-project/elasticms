import { HtmlTransform } from '../types.ts'

export const tableCleanHtmlTransform: HtmlTransform = {
    name: 'tableCleanup',
    toEditor(doc) {
        doc.querySelectorAll('table').forEach((table) => {
            const style = table.getAttribute('style')
            if (style && !table.getAttribute('data-user-style')) {
                table.setAttribute('data-user-style', style)
                table.removeAttribute('style')
            }
        })
    },
    toOutput(doc) {
        doc.querySelectorAll('table').forEach((table) => {
            table.removeAttribute('style')
            const userStyle = table.getAttribute('data-user-style')
            if (userStyle) {
                table.setAttribute('style', userStyle)
                table.removeAttribute('data-user-style')
            }
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
