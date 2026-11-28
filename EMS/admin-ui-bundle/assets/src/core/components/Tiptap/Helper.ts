export function escapeHtml(s: unknown): string {
    if (s == null) return ''
    return String(s).replace(
        /[&<>"']/g,
        (c) =>
            ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
            })[c]!
    )
}
