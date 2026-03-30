import Paragraph from '@tiptap/extension-paragraph'
import Heading from '@tiptap/extension-heading'
import IconIdent from '@tabler/icons/outline/indent-increase.svg?raw'
import IconOutdent from '@tabler/icons/outline/indent-decrease.svg?raw'
import { TiptapModule } from '../types.ts'

const indentExtension = {
    indent: {
        default: 0,
        renderHTML: (attributes: Record<string, unknown>) => {
            if (attributes.indent === 0) return {}
            return { style: `margin-left: ${(attributes.indent as number) * 20}px` }
        },
        parseHTML: (element: HTMLElement) => parseInt(element.style.marginLeft) / 20 || 0
    }
}

const CustomParagraph = Paragraph.extend({
    addAttributes() {
        return indentExtension
    }
})

const CustomHeading = Heading.extend({
    addAttributes() {
        return {
            ...this.parent?.(),
            ...indentExtension
        }
    }
})

export const IndentModule: TiptapModule = {
    name: 'indent',
    extensions: [CustomParagraph, CustomHeading],
    groups: {
        indent: ['outdent', 'indent']
    },
    actions: {
        indent: {
            icon: IconIdent,
            tooltip: 'Increase Indent',
            command: (e) => {
                return e.tiptap
                    .chain()
                    .focus()
                    .command(({ tr, state }) => {
                        const { selection } = state
                        tr.doc.nodesBetween(selection.from, selection.to, (node, pos) => {
                            if (node.type.name === 'paragraph' || node.type.name === 'heading') {
                                const currentIndent = node.attrs.indent || 0
                                tr.setNodeMarkup(pos, undefined, {
                                    ...node.attrs,
                                    indent: currentIndent + 1
                                })
                            }
                        })
                        return true
                    })
                    .run()
            },
            isActive: () => false
        },
        outdent: {
            icon: IconOutdent,
            tooltip: 'Decrease Indent',
            command: (e) => {
                return e.tiptap
                    .chain()
                    .focus()
                    .command(({ tr, state }) => {
                        const { selection } = state
                        tr.doc.nodesBetween(selection.from, selection.to, (node, pos) => {
                            if (node.type.name === 'paragraph' || node.type.name === 'heading') {
                                const currentIndent = node.attrs.indent || 0
                                if (currentIndent > 0) {
                                    tr.setNodeMarkup(pos, undefined, {
                                        ...node.attrs,
                                        indent: currentIndent - 1
                                    })
                                }
                            }
                        })
                        return true
                    })
                    .run()
            },
            isActive: () => false
        }
    }
}
