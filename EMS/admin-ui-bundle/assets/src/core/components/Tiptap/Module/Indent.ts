import { Extension } from '@tiptap/core'
import IconIndent from '@tabler/icons/outline/indent-increase.svg?raw'
import IconOutdent from '@tabler/icons/outline/indent-decrease.svg?raw'
import { TiptapModule } from '../Types.ts'
import { TiptapEditor } from '../Editor.ts'

const INDENTABLE = ['paragraph', 'heading', 'div']
const indentExtension = createIndentExtension()

export const IndentModule: TiptapModule = {
    extensions: [indentExtension],
    toolbar: {
        group: 'indent',
        items: [
            {
                name: 'Outdent',
                icon: IconOutdent,
                tooltip: 'indent_decrease',
                command: changeIndent(-1),
                isDisabled: (e: TiptapEditor) => {
                    if (isInList(e.tiptap.state)) return false
                    const { $from } = e.tiptap.state.selection
                    for (let d = 1; d <= $from.depth; d++) {
                        const node = $from.node(d)
                        if (INDENTABLE.includes(node.type.name))
                            return (node.attrs.indent || 0) === 0
                    }
                    return true
                }
            },
            {
                name: 'Indent',
                icon: IconIndent,
                tooltip: 'indent_increase',
                command: changeIndent(1),
                isActive: (e: TiptapEditor) => {
                    const { $from } = e.tiptap.state.selection
                    for (let d = 1; d <= $from.depth; d++) {
                        const node = $from.node(d)
                        if (INDENTABLE.includes(node.type.name)) return (node.attrs.indent || 0) > 0
                    }
                    return false
                }
            }
        ]
    },
    htmlTransforms: [
        {
            name: 'indent',
            toEditor(doc) {
                doc.querySelectorAll('p, h1, h2, h3, h4, h5, h6, div').forEach((el) => {
                    const htmlEl = el as HTMLElement
                    const dataIndent = htmlEl.getAttribute('data-indent')
                    const marginLeft = parseInt(htmlEl.style.marginLeft) || 0

                    if (dataIndent !== null) {
                        htmlEl.style.removeProperty('margin-left')
                    } else if (marginLeft > 0 && marginLeft % 20 === 0) {
                        htmlEl.setAttribute('data-indent', String(marginLeft / 20))
                        htmlEl.style.removeProperty('margin-left')
                    }
                })
            },
            toOutput(doc) {
                doc.querySelectorAll('[data-indent]').forEach((el) =>
                    el.removeAttribute('data-indent')
                )
            }
        }
    ]
}

function createIndentExtension(): Extension {
    return Extension.create({
        name: 'indent',
        addGlobalAttributes() {
            return [
                {
                    types: INDENTABLE,
                    attributes: {
                        indent: {
                            default: 0,
                            renderHTML: (attributes: Record<string, unknown>) => {
                                if (attributes.indent === 0) return {}
                                return {
                                    'data-indent': attributes.indent,
                                    style: `margin-left: ${(attributes.indent as number) * 20}px`
                                }
                            },
                            parseHTML: (element: HTMLElement) =>
                                parseInt(element.getAttribute('data-indent') ?? '0') || 0
                        }
                    }
                }
            ]
        }
    })
}

function isInList(state: any): boolean {
    const { $from } = state.selection
    for (let d = $from.depth; d > 0; d--) {
        if ($from.node(d).type.name === 'listItem') return true
    }
    return false
}

function changeIndent(delta: number) {
    return (e: { tiptap: any }) => {
        const editor = e.tiptap
        if (isInList(editor.state)) {
            if (delta > 0) {
                editor.chain().focus().sinkListItem('listItem').run()
            } else {
                editor.chain().focus().liftListItem('listItem').run()
            }
            return
        }
        editor
            .chain()
            .focus()
            .command(({ tr, state }: { tr: any; state: any }) => {
                const { $from } = state.selection
                for (let d = 1; d <= $from.depth; d++) {
                    const node = $from.node(d)
                    if (!INDENTABLE.includes(node.type.name)) continue
                    const pos = $from.before(d)
                    const indent = node.attrs.indent || 0
                    const next = indent + delta
                    if (next >= 0) {
                        tr.setNodeMarkup(pos, undefined, { ...node.attrs, indent: next })
                    }
                    break
                }
                return true
            })
            .run()
    }
}
