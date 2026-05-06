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

const BLOCK_ELEMENTS = new Set([
    'p',
    'h1',
    'h2',
    'h3',
    'h4',
    'h5',
    'h6',
    'div',
    'pre',
    'address',
    'blockquote',
    'table',
    'ul',
    'ol'
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

function buildPreviewHtml(styles: CkeditorStyle[], contentCss?: string | null): string {
    const cssLink = contentCss ? `<link rel="stylesheet" href="${contentCss}">` : ''
    const items = styles
        .map((s, i) => {
            const tag = isBlock(s) ? s.element : 'span'
            const cls = s.attributes?.class ? ` class="${s.attributes.class}"` : ''
            const dir = s.attributes?.dir ? ` dir="${s.attributes.dir}"` : ''
            const style = stylesToString(s.styles)
            const styleAttr = style ? ` style="${style}"` : ''
            return `<li data-index="${i}"><${tag}${cls}${dir}${styleAttr}>${s.name}</${tag}></li>`
        })
        .join('')

    return `<!DOCTYPE html><html><head>${cssLink}<style>
*{box-sizing:border-box}
body{margin:0;padding:0;font-family:sans-serif}
ul{list-style:none;margin:0;padding:0}
li{padding:4px 12px;cursor:pointer}
li:hover,li.active{background:#e9ecef}
h1,h2,h3,h4,h5,h6,p,div,pre,address,blockquote{margin:0}
</style></head><body><ul>${items}</ul></body></html>`
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

function createStylesDropdown(editor: TiptapEditor): HTMLElement {
    const styles: CkeditorStyle[] = editor.profile.config.stylesSet ?? []
    const contentCss = editor.getWysiwygOptions()?.contentCss ?? null

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

    let initialized = false

    const initIframe = () => {
        if (initialized) return
        initialized = true

        const doc = iframe.contentDocument!
        doc.open()
        doc.write(buildPreviewHtml(styles, contentCss))
        doc.close()

        doc.addEventListener('mousedown', (e) => {
            e.preventDefault()
            const li = (e.target as HTMLElement).closest('li')
            if (!li) return
            const index = parseInt(li.dataset.index!)
            if (styles[index]) applyStyle(editor, styles[index])
            panel.hidden = true
        })
    }

    button.addEventListener('click', (e) => {
        e.stopPropagation()
        panel.hidden = !panel.hidden
        if (!panel.hidden) {
            initIframe()
            syncActive(editor, iframe, styles)
        }
    })

    document.addEventListener('click', () => {
        panel.hidden = true
    })

    wrapper.appendChild(button)
    wrapper.appendChild(panel)

    return wrapper
}
