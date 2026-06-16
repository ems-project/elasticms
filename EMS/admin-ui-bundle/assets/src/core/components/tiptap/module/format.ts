import { TiptapModule } from '../types.ts'
import { TiptapEditor } from '../editor.ts'
import { BLOCK_NODES } from './../extensions.ts'
import { createDropdown, Dropdown } from './../ui/dropdown.ts'
import formatIframeCss from './../../../../../css/core/components/tiptap/_menu_format.scss?inline'
import Heading from '@tiptap/extension-heading'

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

const NODE_TAGS = new Set(['pre', 'div', 'address'])

const FORMAT_COMMANDS: Record<string, (chain: any) => any> = {
    p: (c) => c.setParagraph(),
    pre: (c) => c.setNode('pre'),
    div: (c) => c.setNode('div'),
    address: (c) => c.setNode('address'),
    h1: (c) => c.setHeading({ level: 1 }),
    h2: (c) => c.setHeading({ level: 2 }),
    h3: (c) => c.setHeading({ level: 3 }),
    h4: (c) => c.setHeading({ level: 4 }),
    h5: (c) => c.setHeading({ level: 5 }),
    h6: (c) => c.setHeading({ level: 6 })
}

type EditorState = {
    dropdown: Dropdown
    cleanup: () => void
}

const editorState = new WeakMap<TiptapEditor, EditorState>()

export const formatModule: TiptapModule = {
    extensions: [Heading, BLOCK_NODES.div, BLOCK_NODES.pre, BLOCK_NODES.address],
    toolbar: {
        group: 'styles',
        items: [
            {
                name: 'Format',
                order: 2,
                create: (editor: TiptapEditor) => createFormatDropdown(editor),
                destroy: (editor: TiptapEditor) => {
                    const state = editorState.get(editor)
                    if (!state) return
                    state.dropdown.destroy()
                    state.cleanup()
                    editorState.delete(editor)
                }
            }
        ]
    }
}

function resolveActiveTag(editor: TiptapEditor): string {
    const node = editor.tiptap.state.selection.$from.node()
    if (node.type.name === 'heading') return `h${node.attrs.level}`
    return NODE_TAGS.has(node.type.name) ? node.type.name : 'p'
}

function applyFormat(editor: TiptapEditor, tag: string): void {
    const chain = editor.tiptap.chain().focus() as any
    const command = FORMAT_COMMANDS[tag] ?? FORMAT_COMMANDS.p
    command(chain).run()
}

function buildFormatItem(tag: string): string {
    const label = FORMAT_LABELS[tag] ?? tag
    return `<li data-name="${tag}"><${tag}>${label}</${tag}></li>`
}

function syncActive(root: HTMLElement, activeTag: string): void {
    root.querySelectorAll<HTMLLIElement>('.format-list li').forEach((li) => {
        li.classList.toggle('active', li.dataset.name === activeTag)
    })
}

function createFormatDropdown(editor: TiptapEditor): HTMLElement {
    const options = editor.getWysiwygOptions()
    const contentCss = options?.contentCss ?? null
    const formatTags = (options?.formatTags ?? DEFAULT_FORMAT_TAGS).split(';').filter(Boolean)

    const dropdown = createDropdown(editor, {
        prefix: 'format',
        css: formatIframeCss,
        contentCss,
        iframe: true,
        action: 'Format',
        buttonLabel: editor.trans('format'),
        buttonTooltip: editor.trans('format_paragraph'),
        buildBody: () => `<ul class="format-list">${formatTags.map(buildFormatItem).join('')}</ul>`,
        onItemClick: (name) => applyFormat(editor, name),
        onOpen: (root) => syncActive(root, resolveActiveTag(editor))
    })

    const updateLabel = () => {
        const tag = resolveActiveTag(editor)
        const label = formatTags.includes(tag) ? (FORMAT_LABELS[tag] ?? tag) : 'Format'
        dropdown.setLabel(label)
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
