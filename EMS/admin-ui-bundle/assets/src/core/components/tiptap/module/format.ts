import { TiptapModule } from '../types.ts'
import { TiptapEditor } from '../editor.ts'
import { BLOCK_NODES } from './../extensions.ts'
import { createIframeDropdown, IframeDropdown } from './../ui/iframeDropdown.ts'
import formatIframeCss from './../../../../../css/core/components/tiptap/_menu_format.scss?inline'
import Heading from '@tiptap/extension-heading'

const dropdowns = new WeakMap<TiptapEditor, IframeDropdown>()
const editorCleanups = new WeakMap<TiptapEditor, () => void>()

const DEFAULT_FORMAT_TAGS = 'p;h1;h2;h3;pre'

const FORMAT_LABELS: Record<string, string> = {
    p: 'Normal',
    h1: 'Heading 1',
    h2: 'Heading 2',
    h3: 'Heading 3',
    h4: 'Heading 4',
    h5: 'Heading 5',
    h6: 'Heading 6',
    pre: 'Formatted',
    address: 'Address',
    div: 'Normal (DIV)'
}

const NODE_TO_TAG: Record<string, string> = {
    pre: 'pre',
    div: 'div',
    address: 'address'
}

export const formatModule: TiptapModule = {
    extensions: [Heading, BLOCK_NODES.div, BLOCK_NODES.pre, BLOCK_NODES.address],
    toolbarGroup: 'format',
    toolbar: [
        {
            create: (editor: TiptapEditor) => createFormatDropdown(editor),
            destroy: (editor: TiptapEditor) => {
                dropdowns.get(editor)?.destroy()
                dropdowns.delete(editor)
                editorCleanups.get(editor)?.()
                editorCleanups.delete(editor)
            }
        }
    ]
}

// ─── Active format detection ─────────────────────────────────

function resolveActiveTag(editor: TiptapEditor): string {
    const node = editor.tiptap.state.selection.$from.node()
    if (node.type.name === 'heading') return `h${node.attrs.level}`
    return NODE_TO_TAG[node.type.name] ?? 'p'
}

// ─── Format application ──────────────────────────────────────

function applyFormat(editor: TiptapEditor, tag: string): void {
    const chain = editor.tiptap.chain().focus() as any
    const headingMatch = tag.match(/^h([1-6])$/)

    if (headingMatch && chain.setHeading) {
        chain.setHeading({ level: parseInt(headingMatch[1]) }).run()
    } else if (tag === 'pre') {
        chain.setNode('pre').run()
    } else if (tag === 'div') {
        chain.setNode('div').run()
    } else if (tag === 'address') {
        chain.setNode('address').run()
    } else {
        chain.setParagraph().run()
    }
}

// ─── Panel rendering ─────────────────────────────────────────

function buildFormatItem(tag: string): string {
    const label = FORMAT_LABELS[tag] ?? tag
    return `<li data-name="${tag}"><${tag}>${label}</${tag}></li>`
}

function syncActive(doc: Document, activeTag: string): void {
    doc.querySelectorAll('li').forEach((li) => {
        li.classList.toggle('active', li.dataset.name === activeTag)
    })
}

// ─── Dropdown ────────────────────────────────────────────────

function createFormatDropdown(editor: TiptapEditor): HTMLElement {
    const options = editor.getWysiwygOptions()
    const contentCss = options?.contentCss ?? null
    const formatTags = (options?.formatTags ?? DEFAULT_FORMAT_TAGS).split(';').filter(Boolean)

    const dropdown = createIframeDropdown(editor, {
        prefix: 'format',
        css: formatIframeCss,
        contentCss,
        buttonLabel: 'Format',
        buildBody: () => `<ul class="format-list">${formatTags.map(buildFormatItem).join('')}</ul>`,
        onItemClick(name) {
            applyFormat(editor, name)
        },
        onOpen(iframeDoc) {
            syncActive(iframeDoc, resolveActiveTag(editor))
        }
    })

    dropdowns.set(editor, dropdown)

    const updateLabel = () => {
        const tag = resolveActiveTag(editor)
        const label = formatTags.includes(tag) ? (FORMAT_LABELS[tag] ?? tag) : 'Format'
        dropdown.setLabel(label)
    }

    editor.tiptap.on('selectionUpdate', updateLabel)
    editor.tiptap.on('transaction', updateLabel)

    editorCleanups.set(editor, () => {
        editor.tiptap.off('selectionUpdate', updateLabel)
        editor.tiptap.off('transaction', updateLabel)
    })

    return dropdown.element
}
