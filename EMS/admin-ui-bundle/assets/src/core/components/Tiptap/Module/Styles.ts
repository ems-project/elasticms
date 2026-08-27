import { TiptapModule } from '../Types.ts'
import { TiptapEditor } from '../Editor.ts'
import { CkeditorStyle } from '../../Wysiwyg/CKEditorConfig.ts'
import { Extension, Mark, mergeAttributes } from '@tiptap/core'
import { BLOCK_NODES, ExtensionType } from './../Extensions.ts'
import { createDropdown, Dropdown } from './../UI/Dropdown.ts'
import stylesIframeCss from '@css/core/components/tiptap/_menu_styles.scss?inline'
import Heading from '@tiptap/extension-heading'
import { Plugin, PluginKey } from '@tiptap/pm/state'

type StyleGroup = {
    key: string
    label: string
    styles: CkeditorStyle[]
}

type StyleCategories = {
    block: CkeditorStyle[]
    inline: CkeditorStyle[]
    object: CkeditorStyle[]
}

type EditorState = {
    dropdown: Dropdown
    cleanup: () => void
}

const editorState = new WeakMap<TiptapEditor, EditorState>()

const INLINE_MARK_PREFIX = 'inlineStyle_'
const inlineMarkName = (element: string) => `${INLINE_MARK_PREFIX}${element}`

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
    pre: 'pre',
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

const NODE_TO_OBJECT_ELEMENT: Record<string, string> = Object.fromEntries(
    Object.entries(OBJECT_NODE_MAP).map(([el, node]) => [node, el])
)

export const StylesModule: TiptapModule = {
    extensions: [
        Heading,
        BLOCK_NODES.div,
        BLOCK_NODES.pre,
        BLOCK_NODES.address,
        ...STYLE_ELEMENTS.inline.map(createInlineStyleMark),
        getStyleExtension()
    ],
    toolbar: {
        group: 'styles',
        items: [
            {
                name: 'Styles',
                order: 1,
                create: (editor: TiptapEditor) => createStylesDropdown(editor),
                destroy: (editor: TiptapEditor) => {
                    const state = editorState.get(editor)
                    if (!state) return
                    state.dropdown.destroy()
                    state.cleanup()
                    editorState.delete(editor)
                }
            }
        ]
    },
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

function getStyleExtension(): ExtensionType {
    return Extension.create({
        name: 'styleAttributes',
        addGlobalAttributes() {
            return [
                {
                    types: ['heading', 'paragraph', 'div', 'pre'],
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
                return newState.tr
                    .insert(newState.doc.content.size, newState.schema.nodes.paragraph.create())
                    .setMeta('trailingParagraph', true)
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
            if (transactions.some((t) => t.getMeta('trailingParagraph'))) return null
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
                m.type.name.startsWith(INLINE_MARK_PREFIX)
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
        name: inlineMarkName(element),
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
    const { $from } = editor.tiptap.state.selection

    if (style.element === 'div') {
        for (let d = $from.depth; d > 0; d--) {
            const node = $from.node(d)
            if (node.type.name !== 'div') continue
            const cls = style.attributes?.class || null
            const st = stylesToString(style.styles) || null
            if (
                node.attrs.htmlClass === cls &&
                normalizeStyle(node.attrs.htmlStyle) === normalizeStyle(st)
            ) {
                return true
            }
        }
        return false
    }

    const node = $from.node()
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
        const el = NODE_TO_OBJECT_ELEMENT[$from.node(d).type.name]
        if (el) active.add(el)
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
        const cls = style.attributes?.class || null
        const st = stylesToString(style.styles) || null
        return (
            (mark.class || null) === cls &&
            normalizeStyle(mark.style || null) === normalizeStyle(st)
        )
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

function isAnyStyleActive(editor: TiptapEditor, style: CkeditorStyle): boolean {
    if (isBlock(style)) return isStyleActive(editor, style)
    if (STYLE_ELEMENTS.object.has(style.element)) return isObjectStyleActive(editor, style)
    return editor.tiptap.isActive(inlineMarkName(style.element))
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
    const st = isActive ? null : stylesToString(style.styles) || null
    editor.tiptap
        .chain()
        .focus()
        .extendMarkRange('link')
        .updateAttributes('link', { class: cls, style: st })
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
    const attrs: Record<string, any> = {}
    const htmlStyle = stylesToString(style.styles) || null
    const htmlClass = style.attributes?.class || null
    if (htmlStyle) attrs.style = htmlStyle
    if (htmlClass) attrs.class = htmlClass
    editor.tiptap.chain().focus().toggleMark(inlineMarkName(style.element), attrs).run()
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
            .setHeading({ level: parseInt(headingMatch[1], 10) })
            .updateAttributes('heading', attrs)
            .setMeta('applyStyle', true)
            .run()
    } else if (style.element === 'pre') {
        chain.setNode('pre', attrs).setMeta('applyStyle', true).run()
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
        return
    }

    const { $from } = editor.tiptap.state.selection
    for (let d = $from.depth; d > 0; d--) {
        if ($from.node(d).type.name === 'div') {
            editor.tiptap.view.dispatch(
                editor.tiptap.state.tr
                    .setNodeMarkup($from.before(d), undefined, {
                        ...$from.node(d).attrs,
                        htmlStyle,
                        htmlClass
                    })
                    .setMeta('applyStyle', true)
            )
            return
        }
    }

    editor.tiptap
        .chain()
        .focus()
        .wrapIn('div', { htmlStyle, htmlClass })
        .setMeta('applyStyle', true)
        .run()
}

// ─── Panel rendering ─────────────────────────────────────────

function buildStyleItem(s: CkeditorStyle): string {
    if (STYLE_ELEMENTS.object.has(s.element)) {
        return `<li data-name="${s.name}"><span>${s.name}</span></li>`
    }

    const attrs = Object.entries(s.attributes ?? {})
        .filter(([k]) => k !== 'style')
        .map(([k, v]) => ` ${k}="${v}"`)
        .join('')

    const styleParts = [stylesToString(s.styles), s.attributes?.style ?? '']
        .filter(Boolean)
        .join(';')
    const styleAttr = styleParts ? ` style="${styleParts}"` : ''

    return `<li data-name="${s.name}"><${s.element}${attrs}${styleAttr}>${s.name}</${s.element}></li>`
}

function buildStyleGroup(group: StyleGroup): string {
    const items = group.styles.map(buildStyleItem).join('')
    return `
        <div class="style-group" data-group="${group.key}">
            <div class="style-group-label">${group.label}</div>
            <ul class="style-list">${items}</ul>
        </div>`
}

function syncActive(editor: TiptapEditor, root: HTMLElement, styles: CkeditorStyle[]): void {
    root.querySelectorAll<HTMLLIElement>('li').forEach((li) => {
        const style = styles.find((s) => s.name === li.dataset.name)
        if (!style) return
        li.classList.toggle('active', isAnyStyleActive(editor, style))
    })
}

function updateVisibleGroups(
    editor: TiptapEditor,
    root: HTMLElement,
    categories: StyleCategories
): void {
    const activeObjects = getActiveObjectElements(editor)

    root.querySelectorAll('.style-group').forEach((group) => {
        const label = (group as HTMLElement).dataset.group
        let visible = false

        if (label === 'styles_block') visible = categories.block.length > 0
        else if (label === 'styles_inline') visible = categories.inline.length > 0
        else if (label === 'styles_object')
            visible = categories.object.some((s) => activeObjects.has(s.element))

        group.classList.toggle('visible', visible)

        if (label === 'styles_object') {
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

    const groups: StyleGroup[] = [
        { key: 'styles_object', label: editor.trans('styles_object'), styles: categories.object },
        { key: 'styles_block', label: editor.trans('styles_block'), styles: categories.block },
        { key: 'styles_inline', label: editor.trans('styles_inline'), styles: categories.inline }
    ].filter((g) => g.styles.length > 0)

    const dropdown = createDropdown(editor, {
        prefix: 'styles',
        css: stylesIframeCss,
        contentCss,
        iframe: true,
        action: 'Styles',
        buttonLabel: editor.trans('style'),
        buttonTooltip: editor.trans('styles_format'),
        buildBody: () => groups.map(buildStyleGroup).join(''),
        onItemClick(name) {
            const matched = styleMap.get(name)
            if (matched) applyStyle(editor, matched)
        },
        onOpen(root) {
            updateVisibleGroups(editor, root, categories)
            syncActive(editor, root, allStyles)
        }
    })

    const updateLabel = () => {
        const names = getActiveStyleNames(editor, categories)
        dropdown.setLabel(names.length > 0 ? names.join(', ') : 'Styles')
    }

    editor.tiptap.on('selectionUpdate', updateLabel)
    editor.tiptap.on('transaction', updateLabel)

    editorState.set(editor, {
        dropdown,
        cleanup: () => {
            editor.tiptap.off('selectionUpdate', updateLabel)
            editor.tiptap.off('transaction', updateLabel)
        }
    })

    return dropdown.element
}

function getActiveStyleNames(editor: TiptapEditor, categories: StyleCategories): string[] {
    const activeBlocks = categories.block.filter((s) => isStyleActive(editor, s))
    const activeDivStyles = activeBlocks.filter((s) => s.element === 'div')
    const filteredBlocks =
        activeDivStyles.length > 0 ? activeBlocks.filter((s) => s.element !== 'p') : activeBlocks
    const activeObjects = categories.object.filter((s) => isObjectStyleActive(editor, s))
    const activeInlines = categories.inline.filter((s) =>
        editor.tiptap.isActive(inlineMarkName(s.element))
    )

    return [
        ...filteredBlocks.map((s) => s.name),
        ...activeObjects.map((s) => s.name),
        ...activeInlines.map((s) => s.name)
    ]
}
