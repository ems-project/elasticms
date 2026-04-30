import { Node, mergeAttributes, Editor } from '@tiptap/core'
import { HtmlTransform } from '../types.ts'

export const TableFigure: Node = Node.create({
    name: 'tableFigure',
    group: 'block',
    content: 'tableCaption? table',
    isolating: true,

    parseHTML() {
        return [{ tag: 'figure[data-type="table"]' }]
    },

    renderHTML({ HTMLAttributes }) {
        return ['figure', mergeAttributes(HTMLAttributes, { 'data-type': 'table' }), 0]
    }
})

export const TableCaption: Node = Node.create({
    name: 'tableCaption',
    content: 'inline*',
    defining: true,

    parseHTML() {
        return [{ tag: 'figcaption' }]
    },

    renderHTML({ HTMLAttributes }) {
        return ['figcaption', mergeAttributes(HTMLAttributes), 0]
    },

    addKeyboardShortcuts(): Record<string, () => boolean> {
        return {
            Backspace: () => {
                const { $from, empty } = this.editor.state.selection
                if (!empty) return false
                if ($from.parent.type.name !== this.name) return false
                if ($from.parent.content.size > 0) return false

                return this.editor
                    .chain()
                    .deleteRange({ from: $from.before(), to: $from.after() })
                    .focus()
                    .run()
            }
        }
    }
})

export const tableCaptionHtmlTransform: HtmlTransform = {
    name: 'tableCaption',
    toEditor(doc) {
        doc.querySelectorAll('table').forEach((table) => {
            const caption = table.querySelector(':scope > caption')
            if (!caption) return
            const figure = doc.createElement('figure')
            figure.setAttribute('data-type', 'table')
            const figcaption = doc.createElement('figcaption')
            figcaption.innerHTML = caption.innerHTML
            caption.remove()
            table.replaceWith(figure)
            figure.appendChild(figcaption)
            figure.appendChild(table)
        })
    },
    toOutput(doc) {
        doc.querySelectorAll('figure[data-type="table"]').forEach((fig) => {
            const figcaption = fig.querySelector(':scope > figcaption')
            const table = fig.querySelector(':scope > table')
            if (!table) {
                fig.remove()
                return
            }
            if (figcaption) {
                const caption = doc.createElement('caption')
                caption.innerHTML = figcaption.innerHTML
                table.insertBefore(caption, table.firstChild)
            }
            fig.replaceWith(table)
        })
    }
}

export function updateCaption(tiptap: Editor, caption: string) {
    const { $from } = tiptap.state.selection

    let figurePos: number | null = null
    let figureNode = null
    let tablePos: number | null = null

    for (let d = $from.depth; d > 0; d--) {
        const node = $from.node(d)
        if (node.type.name === 'tableFigure') {
            figurePos = $from.before(d)
            figureNode = node
            break
        }
        if (node.type.name === 'table') {
            tablePos = $from.before(d)
        }
    }

    if (figureNode && figurePos !== null) {
        const firstChild = figureNode.firstChild
        if (caption) {
            if (firstChild?.type.name === 'tableCaption') {
                const from = figurePos + 1
                const to = from + firstChild.nodeSize
                tiptap
                    .chain()
                    .focus()
                    .deleteRange({ from, to })
                    .insertContentAt(from, {
                        type: 'tableCaption',
                        content: [{ type: 'text', text: caption }]
                    })
                    .run()
            } else {
                tiptap
                    .chain()
                    .focus()
                    .insertContentAt(figurePos + 1, {
                        type: 'tableCaption',
                        content: [{ type: 'text', text: caption }]
                    })
                    .run()
            }
        } else if (firstChild?.type.name === 'tableCaption') {
            const from = figurePos + 1
            const to = from + firstChild.nodeSize
            tiptap.chain().focus().deleteRange({ from, to }).run()
        }
    } else if (caption && tablePos !== null) {
        const tableNode = tiptap.state.doc.nodeAt(tablePos)
        if (!tableNode) return
        const tableEnd = tablePos + tableNode.nodeSize
        tiptap
            .chain()
            .focus()
            .deleteRange({ from: tablePos, to: tableEnd })
            .insertContentAt(tablePos, {
                type: 'tableFigure',
                content: [
                    { type: 'tableCaption', content: [{ type: 'text', text: caption }] },
                    tableNode.toJSON()
                ]
            })
            .run()
    }
}
