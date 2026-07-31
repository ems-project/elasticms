import '../../../../../css/core/components/tiptap/_notice.scss'

type NoticeType = 'error' | 'success'

export function renderNotice(
    doc: Document,
    message: string,
    type: NoticeType,
    parent?: null | HTMLElement
): void {
    const notice = doc.createElement('div')
    notice.className = `tiptap-notice tiptap-notice-${type}`

    const text = doc.createElement('span')
    text.textContent = message
    notice.appendChild(text)

    const close = doc.createElement('button')
    close.type = 'button'
    close.className = 'tiptap-notice-close'
    close.textContent = '×'
    close.addEventListener('click', () => notice.remove())
    notice.appendChild(close)

    if (parent) {
        parent.appendChild(notice)
    } else {
        doc.body.appendChild(notice)
    }

    setTimeout(() => notice.remove(), 2500)
}
