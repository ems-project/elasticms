import { TiptapModule } from '../types.ts'
import { TiptapEditor } from '../editor.ts'
import { CkeditorStyle } from '../../wysiwyg/ckeditorConfig.ts'
import { Extension, Mark, mergeAttributes, Node as TiptapNode } from '@tiptap/core'
import { ExtensionType } from './../extensions.ts'
import Heading from '@tiptap/extension-heading'
import { Plugin, PluginKey } from '@tiptap/pm/state'

const panels = new WeakMap<TiptapEditor, HTMLDivElement>()
const cleanups = new WeakMap<TiptapEditor, () => void>()
const INLINE_ELEMENTS = ['span', 'small', 'code', 'kbd', 'samp', 'var', 'del', 'ins', 'cite', 'q']

export const stylesModule: TiptapModule = {
    extensions: getExtensions(),
    toolbarGroup: 'styles',
    toolbar: [
        {
            create: (editor: TiptapEditor) => createStylesDropdown(editor),
            destroy: (editor: TiptapEditor) => {
                panels.get(editor)?.remove()
                panels.delete(editor)
                cleanups.get(editor)?.()
                cleanups.delete(editor)
            }
        }
    ],
    htmlTransforms: [
        {
            name: 'trailingParagraph',
            toOutput(doc) {
                doc.querySelectorAll('p:last-child:empty').forEach((p) => p.remove())
            }
        }
    ]
}

function getExtensions(): ExtensionType[] {
    const Div = TiptapNode.create({
        name: 'div',
        group: 'block',
        content: 'inline*',
        parseHTML() {
            return [{ tag: 'div' }]
        },
        renderHTML({ HTMLAttributes }) {
            return ['div', HTMLAttributes, 0]
        }
    })

    return [
        Heading,
        Div,
        ...INLINE_ELEMENTS.map(createInlineStyleMark),
        Extension.create({
            name: 'styleAttributes',
            addGlobalAttributes() {
                return [
                    {
                        types: ['heading', 'paragraph', 'div'],
                        attributes: {
                            htmlStyle: {
                                default: null,
                                parseHTML: (el) => {
                                    const style = el.getAttribute('style')
                                    if (style && BLOCK_ELEMENTS.has(el.tagName.toLowerCase())) {
                                        el.removeAttribute('style')
                                    }
                                    return style || null
                                },
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
            },
            addProseMirrorPlugins() {
                return [
                    new Plugin({
                        key: new PluginKey('trailingParagraph'),
                        appendTransaction(_, __, newState) {
                            const lastChild = newState.doc.lastChild
                            if (lastChild && lastChild.type.name !== 'paragraph') {
                                return newState.tr.insert(
                                    newState.doc.content.size,
                                    newState.schema.nodes.paragraph.create()
                                )
                            }
                            return null
                        }
                    }),
                    new Plugin({
                        key: new PluginKey('clearStyleOnSplit'),
                        appendTransaction(transactions, oldState, newState) {
                            if (!transactions.some((t) => t.docChanged)) return null
                            if (transactions.some((t) => t.getMeta('applyStyle'))) return null
                            if (newState.doc.childCount <= oldState.doc.childCount) return null

                            const { $from } = newState.selection
                            const node = $from.parent
                            let tr = null

                            if (node.attrs.htmlStyle || node.attrs.htmlClass) {
                                const pos = $from.before($from.depth)
                                tr = newState.tr.setNodeMarkup(pos, undefined, {
                                    ...node.attrs,
                                    htmlStyle: null,
                                    htmlClass: null
                                })
                            }

                            const storedMarks =
                                newState.storedMarks ?? newState.selection.$from.marks()
                            const inlineStyleMarks = storedMarks.filter((m) =>
                                m.type.name.startsWith('inlineStyle_')
                            )

                            if (inlineStyleMarks.length > 0) {
                                tr = tr ?? newState.tr
                                for (const mark of inlineStyleMarks) {
                                    tr = tr.removeStoredMark(mark)
                                }
                            }

                            return tr
                        }
                    })
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
    'blockquote'
])

const OBJECT_ELEMENTS = new Set(['table', 'ul', 'ol', 'img', 'td', 'th'])

function isBlock(style: CkeditorStyle): boolean {
    return BLOCK_ELEMENTS.has(style.element)
}

function createInlineStyleMark(element: string) {
    return Mark.create({
        name: `inlineStyle_${element}`,
        addAttributes() {
            return {
                style: {
                    default: null,
                    parseHTML: (el) => el.getAttribute('style') || null,
                    renderHTML: (attrs) => (attrs.style ? { style: attrs.style } : {})
                },
                class: {
                    default: null,
                    parseHTML: (el) => el.getAttribute('class') || null,
                    renderHTML: (attrs) => (attrs.class ? { class: attrs.class } : {})
                }
            }
        },
        parseHTML() {
            if (element === 'span') {
                return [{ tag: 'span[class]' }]
            }
            return [{ tag: element }]
        },
        renderHTML({ HTMLAttributes }) {
            return [element, mergeAttributes(HTMLAttributes), 0]
        }
    })
}

function stylesToString(styles?: Record<string, string>): string {
    if (!styles) return ''
    return Object.entries(styles)
        .map(([k, v]) => `${k}:${v}`)
        .join(';')
}

function categorizeStyles(styles: CkeditorStyle[]): {
    block: CkeditorStyle[]
    inline: CkeditorStyle[]
    object: CkeditorStyle[]
} {
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
        if (node.type.name === 'tableCell') active.add('td')
        if (node.type.name === 'tableHeader') active.add('th')
    }

    return active
}

function isInsideList(editor: TiptapEditor): boolean {
    const { $from } = editor.tiptap.state.selection
    for (let d = $from.depth; d > 0; d--) {
        const name = $from.node(d).type.name
        if (name === 'bulletList' || name === 'orderedList') return true
    }
    return false
}

function isObjectStyleActive(editor: TiptapEditor, style: CkeditorStyle): boolean {
    const { $from } = editor.tiptap.state.selection
    for (let d = $from.depth; d > 0; d--) {
        const node = $from.node(d)
        const nodeType = node.type.name
        const matches =
            (style.element === 'table' && nodeType === 'table') ||
            (style.element === 'ul' && nodeType === 'bulletList') ||
            (style.element === 'ol' && nodeType === 'orderedList') ||
            (style.element === 'td' && nodeType === 'tableCell') ||
            (style.element === 'th' && nodeType === 'tableHeader')
        if (matches) {
            const styleStr = stylesToString(style.styles) || null
            const cls = style.attributes?.class || null
            if (styleStr) {
                return (
                    !!node.attrs.dataUserStyle &&
                    normalizeStyle(node.attrs.dataUserStyle) === normalizeStyle(styleStr)
                )
            }
            return node.attrs.class === cls
        }
    }
    return false
}

function applyStyle(editor: TiptapEditor, style: CkeditorStyle): void {
    const chain = editor.tiptap.chain().focus()
    const cmds = editor.tiptap.commands
    const htmlStyle = stylesToString(style.styles) || null
    const htmlClass = style.attributes?.class || null

    if (OBJECT_ELEMENTS.has(style.element)) {
        const { $from } = editor.tiptap.state.selection
        for (let d = $from.depth; d > 0; d--) {
            const node = $from.node(d)
            const nodeType = node.type.name
            const matches =
                (style.element === 'table' && nodeType === 'table') ||
                (style.element === 'ul' && nodeType === 'bulletList') ||
                (style.element === 'ol' && nodeType === 'orderedList') ||
                (style.element === 'td' && nodeType === 'tableCell') ||
                (style.element === 'th' && nodeType === 'tableHeader')

            if (matches) {
                const pos = $from.before(d)
                const styleStr = stylesToString(style.styles) || null

                const isActive = isObjectStyleActive(editor, style)

                if (isActive) {
                    const resetAttrs: Record<string, any> = {
                        ...node.attrs,
                        dataUserStyle: null,
                        class: null
                    }
                    editor.tiptap.view.dispatch(
                        editor.tiptap.state.tr.setNodeMarkup(pos, undefined, resetAttrs)
                    )
                } else {
                    const newAttrs: Record<string, any> = { ...node.attrs, dataUserStyle: styleStr }
                    if (style.attributes) {
                        Object.entries(style.attributes).forEach(([k, v]) => {
                            newAttrs[k] = v
                        })
                    }
                    editor.tiptap.view.dispatch(
                        editor.tiptap.state.tr.setNodeMarkup(pos, undefined, newAttrs)
                    )
                }
                break
            }
        }
        return
    }

    if (isBlock(style) && isStyleActive(editor, style)) {
        if (isInsideList(editor)) {
            editor.tiptap
                .chain()
                .focus()
                .updateAttributes('paragraph', { htmlStyle: null, htmlClass: null })
                .setMeta('applyStyle', true)
                .run()
        } else {
            editor.tiptap
                .chain()
                .focus()
                .setParagraph()
                .updateAttributes('paragraph', { htmlStyle: null, htmlClass: null })
                .setMeta('applyStyle', true)
                .run()
        }
        return
    }

    if (!isBlock(style) && !OBJECT_ELEMENTS.has(style.element)) {
        const markName = `inlineStyle_${style.element}`
        const attrs: Record<string, any> = {}
        if (htmlStyle) attrs.style = htmlStyle
        if (htmlClass) attrs.class = htmlClass
        chain.toggleMark(markName, attrs).run()
        return
    } else if (/^h[1-6]$/.test(style.element) && 'setHeading' in cmds) {
        ;(chain as any)
            .setHeading({ level: parseInt(style.element[1]) })
            .updateAttributes('heading', { htmlStyle, htmlClass })
            .setMeta('applyStyle', true)
            .run()
    } else if (style.element === 'div') {
        const inList = isInsideList(editor)
        if (inList) {
            chain
                .updateAttributes('paragraph', { htmlStyle, htmlClass })
                .setMeta('applyStyle', true)
                .run()
        } else {
            editor.tiptap
                .chain()
                .focus()
                .setNode('div', { htmlStyle, htmlClass })
                .setMeta('applyStyle', true)
                .run()
        }
    } else if (style.element === 'pre' && 'setCodeBlock' in cmds) {
        ;(chain as any).setCodeBlock().setMeta('applyStyle', true).run()
    } else if (style.element === 'blockquote' && 'toggleBlockquote' in cmds) {
        ;(chain as any)
            .toggleBlockquote()
            .updateAttributes('blockquote', { htmlStyle, htmlClass })
            .setMeta('applyStyle', true)
            .run()
    } else if (isBlock(style)) {
        chain
            .setParagraph()
            .updateAttributes('paragraph', { htmlStyle, htmlClass })
            .setMeta('applyStyle', true)
            .run()
    }
}

function syncActive(
    editor: TiptapEditor,
    iframe: HTMLIFrameElement,
    styles: CkeditorStyle[]
): void {
    const doc = iframe.contentDocument
    if (!doc) return

    doc.querySelectorAll('li').forEach((li) => {
        const style = styles.find((s) => s.name === li.dataset.name)
        if (!style) return

        let active
        if (isBlock(style)) {
            active = isStyleActive(editor, style)
        } else if (OBJECT_ELEMENTS.has(style.element)) {
            active = isObjectStyleActive(editor, style)
        } else {
            active = editor.tiptap.isActive(`inlineStyle_${style.element}`)
        }

        li.classList.toggle('active', active)
    })
}

function buildPreviewHtml(groups: StyleGroup[], contentCss?: string | null): string {
    const cssLink = contentCss ? `<link rel="stylesheet" href="${contentCss}">` : ''

    const html = groups
        .map((group) => {
            const items = group.styles
                .map((s) => {
                    if (OBJECT_ELEMENTS.has(s.element)) {
                        return `<li data-name="${s.name}"><span>${s.name}</span></li>`
                    }
                    const tag = s.element
                    const cls = s.attributes?.class ? ` class="${s.attributes.class}"` : ''
                    const style = stylesToString(s.styles)
                    const styleAttr = style ? ` style="${style}"` : ''
                    return `<li data-name="${s.name}"><${tag}${cls}${styleAttr}>${s.name}</${tag}></li>`
                })
                .join('')

            return `<div class="style-group" data-group="${group.label}">
            <div class="style-group-label">${group.label}</div>
            <ul>${items}</ul>
        </div>`
        })
        .join('')

    return `<!DOCTYPE html><html><head>${cssLink}<style>
*{box-sizing:border-box}
body{margin:0;padding:0;font-family:sans-serif}
ul{list-style:none;margin:0;padding:0}
li{padding:4px 12px;cursor:pointer}
li:hover,li.active{background:#e9ecef}
h1,h2,h3,h4,h5,h6,p,div,pre,address,blockquote{margin:0}
.style-group-label{padding:4px 12px;font-size:11px;font-weight:bold;color:#888;text-transform:uppercase;border-bottom:1px solid #eee;cursor: default;}
.style-group{display:none}
.style-group.visible{display:block}
.style-group.visible~.style-group.visible{border-top:1px solid #dee2e6}
.marker { background-color: #ffff00;}
</style></head><body>${html}</body></html>`
}

function updateVisibleGroups(
    editor: TiptapEditor,
    doc: Document,
    categories: ReturnType<typeof categorizeStyles>
): void {
    const activeObjects = getActiveObjectElements(editor)

    doc.querySelectorAll('.style-group').forEach((group) => {
        const label = (group as HTMLElement).dataset.group
        let visible = false

        if (label === 'Block Styles') visible = categories.block.length > 0
        else if (label === 'Inline Styles') visible = categories.inline.length > 0
        else if (label === 'Object Styles')
            visible = categories.object.some((s) => activeObjects.has(s.element))

        group.classList.toggle('visible', visible)

        if (label === 'Object Styles') {
            group.querySelectorAll('li').forEach((li) => {
                const style = categories.object.find((s) => s.name === li.dataset.name)
                ;(li as HTMLElement).style.display =
                    style && activeObjects.has(style.element) ? '' : 'none'
            })
        }
    })
}

function normalizeStyle(s: string | null): string {
    if (!s) return ''
    const el = document.createElement('div')
    el.style.cssText = s
    return el.style.cssText
}

function isStyleActive(editor: TiptapEditor, style: CkeditorStyle): boolean {
    const node = editor.tiptap.state.selection.$from.node()
    let activeElement = 'p'
    if (node.type.name === 'heading') activeElement = `h${node.attrs.level}`
    else if (node.type.name === 'codeBlock') activeElement = 'pre'
    else if (node.type.name === 'blockquote') activeElement = 'blockquote'
    else if (node.type.name === 'div') activeElement = 'div'

    const appliedAs = /^h[1-6]$/.test(style.element)
        ? style.element
        : style.element === 'pre'
          ? 'pre'
          : style.element === 'blockquote'
            ? 'blockquote'
            : style.element === 'div'
              ? 'div'
              : 'p'

    const inList = isInsideList(editor)
    const matches =
        appliedAs === activeElement || (inList && appliedAs === 'div' && activeElement === 'p')

    if (!matches) return false
    const cls = style.attributes?.class || null
    const st = stylesToString(style.styles) || null
    return (
        node.attrs.htmlClass === cls && normalizeStyle(node.attrs.htmlStyle) === normalizeStyle(st)
    )
}

function createStylesDropdown(editor: TiptapEditor): HTMLElement {
    const allStyles = editor.getWysiwygStyles()
    const contentCss = editor.getWysiwygOptions()?.contentCss ?? null
    const categories = categorizeStyles(allStyles)
    const styleMap = new Map(allStyles.map((s) => [s.name, s]))

    const groups: StyleGroup[] = [
        { label: 'Object Styles', styles: categories.object },
        { label: 'Block Styles', styles: categories.block },
        { label: 'Inline Styles', styles: categories.inline }
    ].filter((g) => g.styles.length > 0)

    const wrapper = document.createElement('div')
    wrapper.className = 'tiptap-styles-dropdown'

    const button = document.createElement('button')
    button.type = 'button'
    button.dataset.action = 'Styles'
    button.className = 'tiptap-styles-btn'
    button.innerHTML = '<span class="styles-label">Styles</span><span>▾</span>'

    const panel = document.createElement('div')
    panel.className = 'tiptap-styles-panel'
    panel.hidden = true
    panels.set(editor, panel)

    const iframe = document.createElement('iframe')
    iframe.className = 'tiptap-styles-iframe'
    panel.appendChild(iframe)
    document.body.appendChild(panel)

    const hide = () => {
        panel.hidden = true
    }

    const positionPanel = () => {
        const rect = button.getBoundingClientRect()
        panel.style.top = `${rect.bottom}px`
        panel.style.left = `${rect.left}px`
    }

    let initialized = false
    let onOpen: (() => void) | null = null

    const initIframe = () => {
        if (initialized) return
        initialized = true

        iframe.srcdoc = buildPreviewHtml(groups, contentCss)
        iframe.addEventListener('load', () => {
            const doc = iframe.contentDocument!

            doc.addEventListener('mousedown', (e) => {
                e.preventDefault()
                const li = (e.target as HTMLElement).closest('li')
                if (!li) return
                const style = styleMap.get(li.dataset.name!)
                if (style) applyStyle(editor, style)
                hide()
            })

            doc.addEventListener('click', (e) => {
                if (!(e.target as HTMLElement).closest('li')) hide()
            })

            onOpen = () => {
                updateVisibleGroups(editor, doc, categories)
                syncActive(editor, iframe, allStyles)
            }

            if (!panel.hidden) onOpen()
        })
    }

    const handleOutsideClick = (e: MouseEvent) => {
        if (!panel.contains(e.target as Node) && !button.contains(e.target as Node)) hide()
    }

    button.addEventListener('click', (e) => {
        e.stopPropagation()
        panel.hidden = !panel.hidden
        if (!panel.hidden) {
            window.focus()
            positionPanel()
            initIframe()
            onOpen?.()
        }
    })

    const label = button.querySelector('.styles-label')!

    const updateLabel = () => {
        const node = editor.tiptap.state.selection.$from.node()
        let activeElement = 'p'
        if (node.type.name === 'heading') activeElement = `h${node.attrs.level}`
        else if (node.type.name === 'codeBlock') activeElement = 'pre'
        else if (node.type.name === 'blockquote') activeElement = 'blockquote'
        else if (node.type.name === 'div') activeElement = 'div'

        const activeBlock = categories.block.find((s) => {
            const appliedAs = /^h[1-6]$/.test(s.element)
                ? s.element
                : s.element === 'pre'
                  ? 'pre'
                  : s.element === 'blockquote'
                    ? 'blockquote'
                    : s.element === 'div'
                      ? 'div'
                      : 'p'
            const inList = isInsideList(editor)
            const matches =
                appliedAs === activeElement ||
                (inList && appliedAs === 'div' && activeElement === 'p')
            if (!matches) return false
            const cls = s.attributes?.class || null
            const style = stylesToString(s.styles) || null
            return (
                node.attrs.htmlClass === cls &&
                normalizeStyle(node.attrs.htmlStyle) === normalizeStyle(style)
            )
        })

        const activeObjects = categories.object.filter((s) => isObjectStyleActive(editor, s))

        const activeInlines = categories.inline.filter((s) =>
            editor.tiptap.isActive(`inlineStyle_${s.element}`)
        )

        const names = [
            ...(activeBlock ? [activeBlock.name] : []),
            ...activeObjects.map((s) => s.name),
            ...activeInlines.map((s) => s.name)
        ]

        const text = names.length > 0 ? names.join(', ') : 'Styles'
        label.textContent = text
        button.title = text !== 'Styles' ? text : ''
    }

    editor.tiptap.on('selectionUpdate', updateLabel)
    editor.tiptap.on('transaction', updateLabel)
    window.addEventListener('blur', hide)
    document.addEventListener('mousedown', handleOutsideClick)
    window.addEventListener('resize', hide)
    window.addEventListener('scroll', hide, true)

    cleanups.set(editor, () => {
        editor.tiptap.off('selectionUpdate', updateLabel)
        editor.tiptap.off('transaction', updateLabel)
        window.removeEventListener('blur', hide)
        document.removeEventListener('mousedown', handleOutsideClick)
        window.removeEventListener('resize', hide)
        window.removeEventListener('scroll', hide, true)
    })

    wrapper.appendChild(button)

    return wrapper
}
