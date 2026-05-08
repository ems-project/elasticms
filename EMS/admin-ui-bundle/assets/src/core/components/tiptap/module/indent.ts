import { Extension } from '@tiptap/core'
import IconIndent from '@tabler/icons/outline/indent-increase.svg?raw'
import IconOutdent from '@tabler/icons/outline/indent-decrease.svg?raw'
import { TiptapModule } from '../types.ts'

const INDENTABLE = ['paragraph', 'heading']
const indentExtension = createIndentExtension()

export const indentModule: TiptapModule = {
    extensions: [indentExtension],
    toolbarGroup: 'indent',
    toolbar: [
        {
            name: 'Outdent',
            icon: IconOutdent,
            tooltip: 'Decrease Indent',
            command: changeIndent(-1)
        },
        {
            name: 'Indent',
            icon: IconIndent,
            tooltip: 'Increase Indent',
            command: changeIndent(1)
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
                                    style: `margin-left: ${(attributes.indent as number) * 20}px`
                                }
                            },
                            parseHTML: (element: HTMLElement) =>
                                parseInt(element.style.marginLeft) / 20 || 0
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
                for (let d = $from.depth; d > 0; d--) {
                    const node = $from.node(d)
                    if (!INDENTABLE.includes(node.type.name)) continue
                    const pos = $from.before(d)
                    const indent = node.attrs.indent || 0
                    const next = indent + delta
                    if (next >= 0) {
                        tr.setNodeMarkup(pos, undefined, {
                            ...node.attrs,
                            indent: next
                        })
                    }
                    break
                }
                return true
            })
            .run()
    }
}
