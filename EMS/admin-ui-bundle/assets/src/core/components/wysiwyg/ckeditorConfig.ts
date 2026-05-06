type CkeditorToolbarGroup = { name: string; groups?: string[] } | '/'

export interface CkeditorStyle {
    name: string
    element: string
    styles?: Record<string, string>
    attributes?: Record<string, string>
}

const DEFAULT_STYLES: CkeditorStyle[] = [
    { name: 'Italic Title', element: 'h2', styles: { 'font-style': 'italic' } },
    { name: 'Subtitle', element: 'h3', styles: { color: '#aaa', 'font-style': 'italic' } },
    {
        name: 'Special Container',
        element: 'div',
        styles: { padding: '5px 10px', background: '#eee', border: '1px solid #ccc' }
    },
    { name: 'Marker', element: 'span', attributes: { class: 'marker' } },
    { name: 'Small', element: 'small' },
    { name: 'Computer Code', element: 'code' },
    { name: 'Keyboard Phrase', element: 'kbd' },
    { name: 'Sample Text', element: 'samp' },
    { name: 'Variable', element: 'var' },
    { name: 'Deleted Text', element: 'del' },
    { name: 'Inserted Text', element: 'ins' },
    { name: 'Cited Work', element: 'cite' },
    { name: 'Inline Quotation', element: 'q' },
    { name: 'Styled Image (left)', element: 'img', attributes: { class: 'left' } },
    { name: 'Styled Image (right)', element: 'img', attributes: { class: 'right' } },
    {
        name: 'Compact Table',
        element: 'table',
        attributes: { cellpadding: '5', cellspacing: '0', border: '1', bordercolor: '#ccc' },
        styles: { 'border-collapse': 'collapse' }
    },
    {
        name: 'Borderless Table',
        element: 'table',
        styles: { 'border-style': 'hidden', 'background-color': '#E6E6FA' }
    },
    { name: 'Square Bulleted List', element: 'ul', styles: { 'list-style-type': 'square' } }
]

export interface CkeditorConfig {
    extraPlugins?: string
    removeButtons?: string
    language: string
    toolbarGroups: CkeditorToolbarGroup[]
    stylesSet?: CkeditorStyle[]
}

export const DEFAULT_CK_VALUES: CkeditorConfig = {
    language: 'en',
    toolbarGroups: [
        { name: 'undo' },
        { name: 'insert' },
        { name: 'links' },
        { name: 'tools' },
        { name: 'document', groups: ['mode'] },
        '/',
        { name: 'basicstyles', groups: ['basicstyles', 'cleanup'] },
        { name: 'paragraph', groups: ['list', 'indent', 'blocks', 'align'] },
        { name: 'styles' },
        { name: 'colors' }
    ],
    stylesSet: DEFAULT_STYLES
}
