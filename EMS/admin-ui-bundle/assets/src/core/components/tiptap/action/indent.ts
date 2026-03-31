import { Extension } from '@tiptap/core'
import IconIndent from '@tabler/icons/outline/indent-increase.svg?raw'
import IconOutdent from '@tabler/icons/outline/indent-decrease.svg?raw'
import { ToolbarAction } from '../types.ts'

const IndentExtension = Extension.create({
    name: 'indent',
    addGlobalAttributes() {
        return [
            {
                types: ['paragraph', 'heading'],
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

export const indentActions: ToolbarAction[] = [
    {
        name: 'Outdent',
        group: 'indent',
        icon: IconOutdent,
        tooltip: 'Decrease Indent',
        extensions: [IndentExtension],
        command: (e) => {
            e.tiptap
                .chain()
                .focus()
                .command(({ tr, state }) => {
                    state.doc.nodesBetween(
                        state.selection.from,
                        state.selection.to,
                        (node, pos) => {
                            if (node.type.name === 'paragraph' || node.type.name === 'heading') {
                                const indent = node.attrs.indent || 0
                                if (indent > 0) {
                                    tr.setNodeMarkup(pos, undefined, {
                                        ...node.attrs,
                                        indent: indent - 1
                                    })
                                }
                            }
                        }
                    )
                    return true
                })
                .run()
        },
        isActive: () => false
    },
    {
        name: 'Indent',
        group: 'indent',
        icon: IconIndent,
        tooltip: 'Increase Indent',
        extensions: [IndentExtension],
        command: (e) => {
            e.tiptap
                .chain()
                .focus()
                .command(({ tr, state }) => {
                    state.doc.nodesBetween(
                        state.selection.from,
                        state.selection.to,
                        (node, pos) => {
                            if (node.type.name === 'paragraph' || node.type.name === 'heading') {
                                const indent = node.attrs.indent || 0
                                tr.setNodeMarkup(pos, undefined, {
                                    ...node.attrs,
                                    indent: indent + 1
                                })
                            }
                        }
                    )
                    return true
                })
                .run()
        },
        isActive: () => false
    }
]
