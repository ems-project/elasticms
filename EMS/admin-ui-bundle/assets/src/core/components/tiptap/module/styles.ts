import { TiptapModule } from '../types.ts'
import { TiptapEditor } from '../editor.ts'
import { CkeditorStyle } from '../../wysiwyg/ckeditorConfig.ts'
import { Extension } from '@tiptap/core'
import { ExtensionType } from './../extensions.ts'
import Heading from '@tiptap/extension-heading'
import CodeBlock from '@tiptap/extension-code-block'
import Blockquote from '@tiptap/extension-blockquote'

export const stylesModule: TiptapModule = {
    extensions: getExtensions(),
    toolbarGroup: 'styles',
    toolbar: [
        {
            create: (editor: TiptapEditor) => createStylesDropdown(editor)
        }
    ]
}

function getExtensions(): ExtensionType[] {
    return [
        Heading,
        Blockquote,
        CodeBlock,
        Extension.create({
            name: 'styleAttributes',
            addGlobalAttributes() {
                return [
                    {
                        types: ['heading', 'paragraph', 'blockquote'],
                        attributes: {
                            htmlStyle: {
                                default: null,
                                parseHTML: (el) => el.getAttribute('style') || null,
                                renderHTML: (attrs) =>
                                    attrs.htmlStyle ? { style: attrs.htmlStyle } : {}
                            },
                            htmlClass: {
                                default: null,
                                parseHTML: (el) => el.getAttribute('class') || null,
                                renderHTML: (attrs) =>
                                    attrs.htmlClass ? { class: attrs.htmlClass } : {}
                            }
                        }
                    }
                ]
            }
        })
    ]
}

type StyleGroup = {
    label: string
    styles: CkeditorStyle[]
}

const BLOCK_ELEMENTS = new Set([
    'p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
    'div', 'pre', 'address', 'blockquote',
])

const OBJECT_ELEMENTS = new Set([
    'table', 'ul', 'ol', 'img',
])

function isBlock(style: CkeditorStyle): boolean {
    return BLOCK_ELEMENTS.has(style.element)
}

function stylesToString(styles?: Record<string, string>): string {
    if (!styles) return ''
    return Object.entries(styles)
        .map(([k, v]) => `${k}:${v}`)
        .join(';')
}

function categorizeStyles(styles: CkeditorStyle[]): { block: CkeditorStyle[]; inline: CkeditorStyle[]; object: CkeditorStyle[] } {
    const block: CkeditorStyle[] = []
    const inline: CkeditorStyle[] = []
    const object: CkeditorStyle[] = []

    for (const s of styles) {
        if (OBJECT_ELEMENTS.has(s.element)) object.push(s)
        else if (BLOCK_ELEMENTS.has(s.element)) block.push(s)
        else inline.push(s)
    }

    return { block, inline, object }
}

function getActiveObjectElements(editor: TiptapEditor): Set<string> {
    const active = new Set<string>()
    const { $from } = editor.tiptap.state.selection

    for (let d = $from.depth; d > 0; d--) {
        const node = $from.node(d)
        if (node.type.name === 'table') active.add('table')
        if (node.type.name === 'bulletList') active.add('ul')
        if (node.type.name === 'orderedList') active.add('ol')
    }

    return active
}

function applyStyle(editor: TiptapEditor, style: CkeditorStyle): void {
    const chain = editor.tiptap.chain().focus()
    const cmds = editor.tiptap.commands
    const htmlStyle = stylesToString(style.styles) || null
    const htmlClass = style.attributes?.class || null

    if (/^h[1-6]$/.test(style.element) && 'setHeading' in cmds) {
        ;(chain as any)
            .setHeading({ level: parseInt(style.element[1]) })
            .updateAttributes('heading', { htmlStyle, htmlClass })
            .run()
    } else if (style.element === 'pre' && 'setCodeBlock' in cmds) {
        ;(chain as any).setCodeBlock().run()
    } else if (style.element === 'blockquote' && 'toggleBlockquote' in cmds) {
        ;(chain as any)
            .toggleBlockquote()
            .updateAttributes('blockquote', { htmlStyle, htmlClass })
            .run()
    } else if (isBlock(style)) {
        chain.setParagraph().updateAttributes('paragraph', { htmlStyle, htmlClass }).run()
    }
}

function syncActive(
    editor: TiptapEditor,
    iframe: HTMLIFrameElement,
    styles: CkeditorStyle[]
): void {
    const doc = iframe.contentDocument
    if (!doc) return

    const node = editor.tiptap.state.selection.$from.node()
    let activeElement = 'p'
    if (node.type.name === 'heading') activeElement = `h${node.attrs.level}`
    else if (node.type.name === 'codeBlock') activeElement = 'pre'
    else if (node.type.name === 'blockquote') activeElement = 'blockquote'

    doc.querySelectorAll('li').forEach((li, i) => {
        li.classList.toggle('active', styles[i]?.element === activeElement)
    })
}

function buildPreviewHtml(groups: StyleGroup[], contentCss?: string | null): string {
    const cssLink = contentCss ? `<link rel="stylesheet" href="${contentCss}">` : ''

    const html = groups.map((group) => {
        const items = group.styles.map((s) => {
            const tag = BLOCK_ELEMENTS.has(s.element) ? s.element : 'span'
            const cls = s.attributes?.class ? ` class="${s.attributes.class}"` : ''
            const dir = s.attributes?.dir ? ` dir="${s.attributes.dir}"` : ''
            const style = stylesToString(s.styles)
            const styleAttr = style ? ` style="${style}"` : ''
            return `<li data-name="${s.name}"><${tag}${cls}${dir}${styleAttr}>${s.name}</${tag}></li>`
        }).join('')

        return `<div class="style-group" data-group="${group.label}">
            <div class="style-group-label">${group.label}</div>
            <ul>${items}</ul>
        </div>`
    }).join('')

    return `<!DOCTYPE html><html><head>${cssLink}<style>
*{box-sizing:border-box}
body{margin:0;padding:0;font-family:sans-serif}
ul{list-style:none;margin:0;padding:0}
li{padding:4px 12px;cursor:pointer}
li:hover,li.active{background:#e9ecef}
h1,h2,h3,h4,h5,h6,p,div,pre,address,blockquote{margin:0}
.style-group-label{padding:4px 12px;font-size:11px;font-weight:bold;color:#888;text-transform:uppercase;border-bottom:1px solid #eee}
.style-group{display:none}
.style-group.visible{display:block}
.style-group+.style-group.visible{border-top:1px solid #dee2e6}
</style></head><body>${html}</body></html>`
}

function updateVisibleGroups(editor: TiptapEditor, doc: Document, categories: ReturnType<typeof categorizeStyles>): void {
    const activeObjects = getActiveObjectElements(editor)
    const visibleObjects = categories.object.filter((s) => activeObjects.has(s.element))

    doc.querySelectorAll('.style-group').forEach((group) => {
        const label = (group as HTMLElement).dataset.group
        let visible = false

        if (label === 'Block Styles') visible = categories.block.length > 0
        else if (label === 'Inline Styles') visible = categories.inline.length > 0
        else if (label === 'Object Styles') visible = visibleObjects.length > 0

        group.classList.toggle('visible', visible)

        if (label === 'Object Styles') {
            group.querySelectorAll('li').forEach((li) => {
                const style = categories.object.find((s) => s.name === li.dataset.name)
                ;(li as HTMLElement).style.display = style && activeObjects.has(style.element) ? '' : 'none'
            })
        }
    })
}

function createStylesDropdown(editor: TiptapEditor): HTMLElement {
    const allStyles: CkeditorStyle[] = editor.profile.config.stylesSet ?? []
    const contentCss = editor.getWysiwygOptions()?.contentCss ?? null
    const categories = categorizeStyles(allStyles)
    const styleMap = new Map(allStyles.map((s) => [s.name, s]))

    const groups: StyleGroup[] = [
        { label: 'Object Styles', styles: categories.object },
        { label: 'Block Styles', styles: categories.block },
        { label: 'Inline Styles', styles: categories.inline },
    ].filter((g) => g.styles.length > 0)

    const wrapper = document.createElement('div')
    wrapper.className = 'tiptap-styles-dropdown'

    const button = document.createElement('button')
    button.type = 'button'
    button.className = 'tiptap-styles-btn'
    button.innerHTML = '<span class="styles-label">Styles</span><span>▾</span>'

    const panel = document.createElement('div')
    panel.className = 'tiptap-styles-panel'
    panel.hidden = true

    const iframe = document.createElement('iframe')
    iframe.className = 'tiptap-styles-iframe'
    panel.appendChild(iframe)

    document.body.appendChild(panel)

    let initialized = false

    const positionPanel = () => {
        const rect = button.getBoundingClientRect()
        panel.style.top = `${rect.bottom}px`
        panel.style.left = `${rect.left}px`
    }

    const initIframe = () => {
        if (initialized) return
        initialized = true

        const doc = iframe.contentDocument!
        doc.open()
        doc.write(buildPreviewHtml(groups, contentCss))
        doc.close()

        doc.addEventListener('mousedown', (e) => {
            e.preventDefault()
            const li = (e.target as HTMLElement).closest('li')
            if (!li) return
            const style = styleMap.get(li.dataset.name!)
            if (style) applyStyle(editor, style)
            panel.hidden = true
        })
    }

    button.addEventListener('click', (e) => {
        e.stopPropagation()
        panel.hidden = !panel.hidden
        if (!panel.hidden) {
            positionPanel()
            initIframe()
            updateVisibleGroups(editor, iframe.contentDocument!, categories)
            syncActive(editor, iframe, allStyles)
        }
    })
    document.addEventListener('click', () => { panel.hidden = true })

    wrapper.appendChild(button)

    return wrapper
}
