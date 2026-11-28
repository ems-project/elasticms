export function escapeForHtmlAttribute(str) {
    return str
        .replace(/&/g, '&amp;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
}

export function stripHtmlTags(str) {
    const doc = new DOMParser().parseFromString(str, 'text/html')
    return doc.body.textContent || doc.body.innerText || ''
}
