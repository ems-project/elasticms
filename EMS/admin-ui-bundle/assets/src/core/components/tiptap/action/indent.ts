import { Extension } from '@tiptap/core'
import IconIndent from '@tabler/icons/outline/indent-increase.svg?raw'
import IconOutdent from '@tabler/icons/outline/indent-decrease.svg?raw'
import { ToolbarAction } from '../types.ts'

const INDENTABLE = ['paragraph', 'heading', 'bulletList', 'orderedList']

const IndentExtension = Extension.create({
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
                            return { style: `margin-left: ${(attributes.indent as number) * 20}px` }
                        },
                        parseHTML: (element: HTMLElement) =>
                            parseInt(element.style.marginLeft) / 20 || 0
                    }
                }
            }
        ]
    }
})

function changeIndent(delta: number) {
    return (e: { tiptap: any }) => {
        e.tiptap
            .chain()
            .focus()
            .command(({ tr, state }: { tr: any; state: any }) => {
                const { $from } = state.selection
                for (let d = $from.depth; d > 0; d--) {
                    const node = $from.node(d)
                    if (!INDENTABLE.includes(node.type.name)) continue
                    if (
                        (node.type.name === 'paragraph' || node.type.name === 'heading') &&
                        $from.node(d - 1)?.type.name === 'listItem'
                    )
                        continue
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

export const indentActions: ToolbarAction[] = [
    {
        name: 'Outdent',
        group: 'indent',
        icon: IconOutdent,
        tooltip: 'Decrease Indent',
        extensions: [IndentExtension],
        command: changeIndent(-1),
        isActive: () => false
    },
    {
        name: 'Indent',
        group: 'indent',
        icon: IconIndent,
        tooltip: 'Increase Indent',
        extensions: [IndentExtension],
        command: changeIndent(1),
        isActive: () => false
    }
]
