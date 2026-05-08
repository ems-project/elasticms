import { TiptapModule } from '../types.ts'
import { TiptapEditor } from '../editor.ts'
import { CkeditorStyle } from '../../wysiwyg/ckeditorConfig.ts'
import { Extension, Mark, mergeAttributes, Node as TiptapNode } from '@tiptap/core'
import { ExtensionType } from './../extensions.ts'
import stylesIframeCss from './../../../../../css/core/components/tiptap/_styles_menu.scss?inline'
import Heading from '@tiptap/extension-heading'
import { Plugin, PluginKey } from '@tiptap/pm/state'

const panels = new WeakMap<TiptapEditor, HTMLDivElement>()
const cleanups = new WeakMap<TiptapEditor, () => void>()

type StyleGroup = {
    label: string
    styles: CkeditorStyle[]
}

type StyleCategories = {
    block: CkeditorStyle[]
    inline: CkeditorStyle[]
    object: CkeditorStyle[]
}

const STYLE_ELEMENTS = {
    inline: ['span', 'small', 'code', 'kbd', 'samp', 'var', 'del', 'ins', 'cite', 'q'],
    block: new Set([
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
    ]),
    object: new Set(['table', 'ul', 'ol', 'img', 'td', 'th', 'a'])
} as const

const NODE_TO_ELEMENT: Record<string, string> = {
    heading: 'heading',
    codeBlock: 'pre',
    blockquote: 'blockquote',
    div: 'div'
}

const ELEMENT_TO_APPLIED: Record<string, string> = {
    pre: 'pre',
    blockquote: 'blockquote',
    div: 'div'
}

const OBJECT_NODE_MAP: Record<string, string> = {
    table: 'table',
    ul: 'bulletList',
    ol: 'orderedList',
    td: 'tableCell',
    th: 'tableHeader'
}

export const stylesModule: TiptapModule = {
    extensions: [
        Heading,
        getDivExtension(),
        ...STYLE_ELEMENTS.inline.map(createInlineStyleMark),
        getStyleExtension()
    ],
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

// ─── Extensions ──────────────────────────────────────────────

function getDivExtension(): ExtensionType {
    return TiptapNode.create({
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
}

function getStyleExtension(): ExtensionType {
    return Extension.create({
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
                                if (style && STYLE_ELEMENTS.block.has(el.tagName.toLowerCase())) {
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
            return [trailingParagraphPlugin(), clearStyleOnSplitPlugin()]
        }
    })
}

function trailingParagraphPlugin() {
    return new Plugin({
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
    })
}

function clearStyleOnSplitPlugin() {
    return new Plugin({
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

            const storedMarks = newState.storedMarks ?? newState.selection.$from.marks()
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
            if (element === 'span') return [{ tag: 'span[class]' }]
            return [{ tag: element }]
        },
        renderHTML({ HTMLAttributes }) {
            return [element, mergeAttributes(HTMLAttributes), 0]
        }
    })
}

// ─── Style resolution helpers ────────────────────────────────

function resolveActiveElement(editor: TiptapEditor): string {
    const node = editor.tiptap.state.selection.$from.node()
    if (node.type.name === 'heading') return `h${node.attrs.level}`
    return NODE_TO_ELEMENT[node.type.name] ?? 'p'
}

function resolveAppliedAs(element: string): string {
    if (/^h[1-6]$/.test(element)) return element
    return ELEMENT_TO_APPLIED[element] ?? 'p'
}

function isBlock(style: CkeditorStyle): boolean {
    return STYLE_ELEMENTS.block.has(style.element)
}

function stylesToString(styles?: Record<string, string>): string {
    if (!styles) return ''
    return Object.entries(styles)
        .map(([k, v]) => `${k}:${v}`)
        .join(';')
}

function normalizeStyle(s: string | null): string {
    if (!s) return ''
    const el = document.createElement('div')
    el.style.cssText = s
    return el.style.cssText
}

function categorizeStyles(styles: CkeditorStyle[]): StyleCategories {
    const block: CkeditorStyle[] = []
    const inline: CkeditorStyle[] = []
    const object: CkeditorStyle[] = []

    for (const s of styles) {
        if (STYLE_ELEMENTS.object.has(s.element)) object.push(s)
        else if (STYLE_ELEMENTS.block.has(s.element)) block.push(s)
        else inline.push(s)
    }

    return { block, inline, object }
}

function isInsideList(editor: TiptapEditor): boolean {
    const { $from } = editor.tiptap.state.selection
    for (let d = $from.depth; d > 0; d--) {
        const name = $from.node(d).type.name
        if (name === 'bulletList' || name === 'orderedList') return true
    }
    return false
}

// ─── Active state detection ──────────────────────────────────

function isStyleActive(editor: TiptapEditor, style: CkeditorStyle): boolean {
    const node = editor.tiptap.state.selection.$from.node()
    const activeElement = resolveActiveElement(editor)
    const appliedAs = resolveAppliedAs(style.element)
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

    if (editor.tiptap.isActive('link')) active.add('a')

    return active
}

function findMatchingObjectNode(
    editor: TiptapEditor,
    element: string
): { node: any; pos: number } | null {
    const expectedNodeType = OBJECT_NODE_MAP[element]
    if (!expectedNodeType) return null

    const { $from } = editor.tiptap.state.selection
    for (let d = $from.depth; d > 0; d--) {
        if ($from.node(d).type.name === expectedNodeType) {
            return { node: $from.node(d), pos: $from.before(d) }
        }
    }
    return null
}

function isObjectStyleActive(editor: TiptapEditor, style: CkeditorStyle): boolean {
    if (style.element === 'a') {
        const mark = editor.tiptap.getAttributes('link')
        return mark.class === (style.attributes?.class || null)
    }

    const match = findMatchingObjectNode(editor, style.element)
    if (!match) return false

    const styleStr = stylesToString(style.styles) || null
    const cls = style.attributes?.class || null

    if (styleStr) {
        return (
            !!match.node.attrs.dataUserStyle &&
            normalizeStyle(match.node.attrs.dataUserStyle) === normalizeStyle(styleStr)
        )
    }
    return match.node.attrs.class === cls
}

// ─── Style application ───────────────────────────────────────

function applyStyle(editor: TiptapEditor, style: CkeditorStyle): void {
    if (style.element === 'a') return applyLinkStyle(editor, style)
    if (STYLE_ELEMENTS.object.has(style.element)) return applyObjectStyle(editor, style)
    if (!isBlock(style)) return applyInlineStyle(editor, style)
    if (isStyleActive(editor, style)) return clearBlockStyle(editor)
    applyBlockStyle(editor, style)
}

function applyLinkStyle(editor: TiptapEditor, style: CkeditorStyle): void {
    const isActive = isObjectStyleActive(editor, style)
    const cls = isActive ? null : style.attributes?.class || null
    editor.tiptap
        .chain()
        .focus()
        .extendMarkRange('link')
        .updateAttributes('link', { class: cls })
        .run()
}

function applyObjectStyle(editor: TiptapEditor, style: CkeditorStyle): void {
    const match = findMatchingObjectNode(editor, style.element)
    if (!match) return

    const isActive = isObjectStyleActive(editor, style)
    const styleStr = stylesToString(style.styles) || null

    if (isActive) {
        editor.tiptap.view.dispatch(
            editor.tiptap.state.tr.setNodeMarkup(match.pos, undefined, {
                ...match.node.attrs,
                dataUserStyle: null,
                class: null
            })
        )
    } else {
        const newAttrs: Record<string, any> = { ...match.node.attrs, dataUserStyle: styleStr }
        if (style.attributes) {
            Object.entries(style.attributes).forEach(([k, v]) => {
                newAttrs[k] = v
            })
        }
        editor.tiptap.view.dispatch(
            editor.tiptap.state.tr.setNodeMarkup(match.pos, undefined, newAttrs)
        )
    }
}

function applyInlineStyle(editor: TiptapEditor, style: CkeditorStyle): void {
    const markName = `inlineStyle_${style.element}`
    const attrs: Record<string, any> = {}
    const htmlStyle = stylesToString(style.styles) || null
    const htmlClass = style.attributes?.class || null
    if (htmlStyle) attrs.style = htmlStyle
    if (htmlClass) attrs.class = htmlClass
    editor.tiptap.chain().focus().toggleMark(markName, attrs).run()
}

function clearBlockStyle(editor: TiptapEditor): void {
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
}

function applyBlockStyle(editor: TiptapEditor, style: CkeditorStyle): void {
    const chain = editor.tiptap.chain().focus() as any
    const htmlStyle = stylesToString(style.styles) || null
    const htmlClass = style.attributes?.class || null
    const attrs = { htmlStyle, htmlClass }

    if (style.element === 'div') {
        applyDivStyle(editor, htmlStyle, htmlClass)
        return
    }

    const headingMatch = style.element.match(/^h([1-6])$/)

    if (headingMatch && chain.setHeading) {
        chain
            .setHeading({ level: parseInt(headingMatch[1]) })
            .updateAttributes('heading', attrs)
            .setMeta('applyStyle', true)
            .run()
    } else if (style.element === 'pre' && chain.setCodeBlock) {
        chain.setCodeBlock().setMeta('applyStyle', true).run()
    } else if (style.element === 'blockquote' && chain.toggleBlockquote) {
        chain
            .toggleBlockquote()
            .updateAttributes('blockquote', attrs)
            .setMeta('applyStyle', true)
            .run()
    } else {
        chain.setParagraph().updateAttributes('paragraph', attrs).setMeta('applyStyle', true).run()
    }
}

function applyDivStyle(
    editor: TiptapEditor,
    htmlStyle: string | null,
    htmlClass: string | null
): void {
    if (isInsideList(editor)) {
        editor.tiptap
            .chain()
            .focus()
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
}

// ─── Panel rendering ─────────────────────────────────────────

function buildStyleItem(s: CkeditorStyle): string {
    if (STYLE_ELEMENTS.object.has(s.element)) {
        return `<li data-name="${s.name}"><span>${s.name}</span></li>`
    }

    const cls = s.attributes?.class ? ` class="${s.attributes.class}"` : ''
    const style = stylesToString(s.styles)
    const styleAttr = style ? ` style="${style}"` : ''

    return `<li data-name="${s.name}"><${s.element}${cls}${styleAttr}>${s.name}</${s.element}></li>`
}

function buildStyleGroup(group: StyleGroup): string {
    const items = group.styles.map(buildStyleItem).join('')

    return `
        <div class="style-group" data-group="${group.label}">
            <div class="style-group-label">${group.label}</div>
            <ul class="style-list">${items}</ul>
        </div>`
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
        if (isBlock(style)) active = isStyleActive(editor, style)
        else if (STYLE_ELEMENTS.object.has(style.element))
            active = isObjectStyleActive(editor, style)
        else active = editor.tiptap.isActive(`inlineStyle_${style.element}`)

        li.classList.toggle('active', active)
    })
}

function updateVisibleGroups(
    editor: TiptapEditor,
    doc: Document,
    categories: StyleCategories
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

// ─── Dropdown ────────────────────────────────────────────────

function createStylesDropdown(editor: TiptapEditor): HTMLElement {
    const allStyles = editor.getWysiwygStyles()
    const contentCss = editor.getWysiwygOptions()?.contentCss ?? null
    const categories = categorizeStyles(allStyles)
    const styleMap = new Map(allStyles.map((s) => [s.name, s]))
    const doc = editor.docParent

    const groups: StyleGroup[] = [
        { label: 'Object Styles', styles: categories.object },
        { label: 'Block Styles', styles: categories.block },
        { label: 'Inline Styles', styles: categories.inline }
    ].filter((g) => g.styles.length > 0)

    const wrapper = doc.createElement('div')
    wrapper.className = 'tiptap-styles-dropdown'

    const button = createDropdownButton(doc)

    let panel: HTMLDivElement | null = null
    let onOpen: (() => void) | null = null

    const initPanel = () => {
        if (panel) return

        panel = doc.createElement('div')
        panel.className = 'tiptap-styles-panel'
        panels.set(editor, panel)
        doc.body.appendChild(panel)

        const iframe = doc.createElement('iframe')
        iframe.className = 'tiptap-styles-iframe'
        panel.appendChild(iframe)

        const iframeDoc = iframe.contentDocument!

        if (contentCss) {
            const link = iframeDoc.createElement('link')
            link.rel = 'stylesheet'
            link.href = contentCss
            iframeDoc.head.appendChild(link)
        }

        const s = iframeDoc.createElement('style')
        s.textContent = stylesIframeCss
        iframeDoc.head.appendChild(s)

        iframeDoc.body.innerHTML = groups.map(buildStyleGroup).join('')

        iframeDoc.addEventListener('mousedown', (e) => {
            e.preventDefault()
            const li = (e.target as HTMLElement).closest('li')
            if (!li) return
            const matched = styleMap.get(li.dataset.name!)
            if (matched) applyStyle(editor, matched)
            hide()
        })

        iframeDoc.addEventListener('click', (e) => {
            if (!(e.target as HTMLElement).closest('li')) hide()
        })

        onOpen = () => {
            updateVisibleGroups(editor, iframeDoc, categories)
            syncActive(editor, iframe, allStyles)
        }
    }

    const hide = () => {
        if (panel) panel.hidden = true
    }

    const positionPanel = () => {
        if (!panel) return
        const rect = button.getBoundingClientRect()
        panel.style.top = `${rect.bottom}px`
        panel.style.left = `${rect.left}px`
    }

    const handleOutsideClick = (e: MouseEvent) => {
        if (panel && !panel.contains(e.target as Node) && !button.contains(e.target as Node)) hide()
    }

    button.addEventListener('click', (e) => {
        e.stopPropagation()
        if (panel && !panel.hidden) {
            hide()
            return
        }
        initPanel()
        panel!.hidden = false
        window.focus()
        positionPanel()
        onOpen?.()
    })

    const label = button.querySelector('.styles-label')!

    const updateLabel = () => {
        const names = getActiveStyleNames(editor, categories)
        const text = names.length > 0 ? names.join(', ') : 'Styles'
        label.textContent = text
        button.title = text !== 'Styles' ? text : ''
    }

    editor.tiptap.on('selectionUpdate', updateLabel)
    editor.tiptap.on('transaction', updateLabel)
    window.addEventListener('blur', hide)
    doc.addEventListener('mousedown', handleOutsideClick)
    window.addEventListener('resize', hide)
    window.addEventListener('scroll', hide, true)

    cleanups.set(editor, () => {
        editor.tiptap.off('selectionUpdate', updateLabel)
        editor.tiptap.off('transaction', updateLabel)
        window.removeEventListener('blur', hide)
        doc.removeEventListener('mousedown', handleOutsideClick)
        window.removeEventListener('resize', hide)
        window.removeEventListener('scroll', hide, true)
    })

    wrapper.appendChild(button)

    return wrapper
}

function createDropdownButton(doc: Document): HTMLButtonElement {
    const button = doc.createElement('button')
    button.type = 'button'
    button.dataset.action = 'Styles'
    button.className = 'tiptap-styles-btn'
    button.innerHTML = '<span class="styles-label">Styles</span><span>▾</span>'
    return button
}

function getActiveStyleNames(editor: TiptapEditor, categories: StyleCategories): string[] {
    const activeBlock = categories.block.find((s) => isStyleActive(editor, s))
    const activeObjects = categories.object.filter((s) => isObjectStyleActive(editor, s))
    const activeInlines = categories.inline.filter((s) =>
        editor.tiptap.isActive(`inlineStyle_${s.element}`)
    )

    return [
        ...(activeBlock ? [activeBlock.name] : []),
        ...activeObjects.map((s) => s.name),
        ...activeInlines.map((s) => s.name)
    ]
}
