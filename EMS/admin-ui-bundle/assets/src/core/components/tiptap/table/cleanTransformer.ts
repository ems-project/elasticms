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

            const parts: string[] = []
            const style = table.getAttribute('style')
            if (style) parts.push(style)

            const w = table.getAttribute('width')
            const h = table.getAttribute('height')
            if (w && !style?.includes('width')) parts.push(`width: ${w}`)
            if (h && !style?.includes('height')) parts.push(`height: ${h}`)

            if (parts.length) table.setAttribute('data-user-style', parts.join('; '))
            table.removeAttribute('style')
            if (w) table.removeAttribute('width')
            if (h) table.removeAttribute('height')
        })

        doc.querySelectorAll('td, th').forEach((cell) => {
            const style = cell.getAttribute('style')
            if (style) {
                cell.setAttribute('data-user-style', style)
                cell.removeAttribute('style')
            }

            cell.querySelectorAll('p').forEach((p) => {
                if (p.innerHTML.trim() === '&nbsp;') p.innerHTML = ''
            })
        })
    },
    toOutput(doc) {
        doc.querySelectorAll('table').forEach((table) => {
            const userStyle = table.getAttribute('data-user-style')
            if (userStyle) table.setAttribute('style', userStyle)

            table.removeAttribute('data-user-style')
            table.querySelectorAll('colgroup').forEach((cg) => cg.remove())

            table.querySelectorAll('td, th').forEach((cell) => {
                if (cell.getAttribute('colspan') === '1') cell.removeAttribute('colspan')
                if (cell.getAttribute('rowspan') === '1') cell.removeAttribute('rowspan')

                const style = cell.getAttribute('style')
                if (style) {
                    const cleaned = style.replace(/min-width\s*:[^;]+;?/gi, '').trim()
                    if (cleaned) {
                        cell.setAttribute('style', cleaned)
                    } else {
                        cell.removeAttribute('style')
                    }
                }
            })
        })

        doc.querySelectorAll('td, th').forEach((cell) => {
            const userStyle = cell.getAttribute('data-user-style')
            if (userStyle) {
                const existing = cell.getAttribute('style')
                cell.setAttribute('style', existing ? `${existing}; ${userStyle}` : userStyle)
                cell.removeAttribute('data-user-style')
            }

            cell.querySelectorAll('p').forEach((p) => {
                if (!p.textContent?.trim() && !p.children.length) p.innerHTML = '&nbsp;'
            })
            if (!cell.childNodes.length || (cell.childNodes.length === 1 && cell.textContent?.trim() === '')) {
                cell.innerHTML = '&nbsp;'
            }
        })
    }
}