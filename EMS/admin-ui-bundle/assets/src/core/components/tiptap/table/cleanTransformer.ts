import { HtmlTransform } from '../types.ts'

export const tableCleanHtmlTransform: HtmlTransform = {
    name: 'tableCleanup',
    toEditor(doc) {
        doc.querySelectorAll('table').forEach((table) => {
            removeColgroups(table)
            stashTableStyle(table)
        })
        doc.querySelectorAll('td, th').forEach((cell) => {
            removeRedundantSpans(cell)
            stashCellStyle(cell)
            clearNbspParagraphs(cell)
        })
    },
    toOutput(doc) {
        doc.querySelectorAll('table').forEach((table) => {
            restoreUserStyle(table)
            removeColgroups(table)
        })
        doc.querySelectorAll('td, th').forEach((cell) => {
            removeRedundantSpans(cell)
            stripMinWidth(cell)
            restoreUserStyle(cell)
            fillEmptyParagraphs(cell)
            if (isEmptyCell(cell)) cell.innerHTML = '&nbsp;'
        })
    }
}

function removeColgroups(table: Element): void {
    table.querySelectorAll('colgroup').forEach((cg) => cg.remove())
}

function removeRedundantSpans(cell: Element): void {
    if (cell.getAttribute('colspan') === '1') cell.removeAttribute('colspan')
    if (cell.getAttribute('rowspan') === '1') cell.removeAttribute('rowspan')
}

function stashTableStyle(table: Element): void {
    const parts: string[] = []
    const style = table.getAttribute('style')
    if (style) parts.push(style)
    for (const attr of ['width', 'height']) {
        const value = table.getAttribute(attr)
        if (!value) continue
        if (!style?.includes(attr)) parts.push(`${attr}: ${value}`)
        table.removeAttribute(attr)
    }
    if (parts.length) table.setAttribute('data-user-style', parts.join('; '))
    table.removeAttribute('style')
}

function stashCellStyle(cell: Element): void {
    const style = cell.getAttribute('style')
    if (!style) return
    cell.setAttribute('data-user-style', style)
    cell.removeAttribute('style')
}

function restoreUserStyle(el: Element): void {
    const userStyle = el.getAttribute('data-user-style')
    if (!userStyle) return
    const existing = el.getAttribute('style')
    el.setAttribute('style', existing ? `${existing}; ${userStyle}` : userStyle)
    el.removeAttribute('data-user-style')
}

function stripMinWidth(cell: Element): void {
    const style = cell.getAttribute('style')
    if (!style) return
    const cleaned = style.replace(/min-width\s*:[^;]+;?/gi, '').trim()
    if (cleaned) cell.setAttribute('style', cleaned)
    else cell.removeAttribute('style')
}

function clearNbspParagraphs(cell: Element): void {
    cell.querySelectorAll('p').forEach((p) => {
        if (p.innerHTML.trim() === '&nbsp;') p.innerHTML = ''
    })
}

function fillEmptyParagraphs(cell: Element): void {
    cell.querySelectorAll('p').forEach((p) => {
        if (!p.textContent?.trim() && !p.children.length) p.innerHTML = '&nbsp;'
    })
}

function isEmptyCell(cell: Element): boolean {
    if (cell.querySelector('img, iframe, table, hr, video, svg')) return false
    return (
        !cell.childNodes.length || (cell.childNodes.length === 1 && cell.textContent?.trim() === '')
    )
}
