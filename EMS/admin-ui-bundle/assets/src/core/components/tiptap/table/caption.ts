import { Node, mergeAttributes, Editor } from '@tiptap/core'
import { Node as PMNode } from 'prosemirror-model'
import { Plugin } from 'prosemirror-state'
import { HtmlTransform } from '../types.ts'

function isTableEmpty(table: PMNode): boolean {
    let hasContent = false
    table.descendants((node) => {
        const role = node.type.spec.tableRole
        if (role !== 'cell' && role !== 'header_cell') return
        if (node.childCount > 1) { hasContent = true; return }
        if (node.firstChild && node.firstChild.childCount > 0) hasContent = true
    })
    return !hasContent
}

function isInsertingFigure(transactions: readonly any[]): boolean {
    return transactions.some((tr) =>
        tr.steps.some((step: any) => {
            const slice = step.slice
            if (!slice?.content) return false
            let found = false
            slice.content.forEach((node: PMNode) => {
                if (node.type.name === 'tableFigure') found = true
            })
            return found
        })
    )
}

export const TableFigure: Node = Node.create({
    name: 'tableFigure',
    group: 'block',
    content: 'table tableCaption?',
    isolating: true,

    parseHTML() {
        return [{ tag: 'figure[data-type="table"]' }]
    },

    renderHTML({ HTMLAttributes }) {
        return ['figure', mergeAttributes(HTMLAttributes, { 'data-type': 'table' }), 0]
    },

    addProseMirrorPlugins() {
        return [
            new Plugin({
                appendTransaction(transactions, _oldState, newState) {
                    if (!transactions.some((tr) => tr.docChanged)) return null
                    if (isInsertingFigure(transactions)) return null

                    const deletions: { from: number; to: number }[] = []

                    newState.doc.descendants((node, pos) => {
                        if (node.type.name !== 'tableFigure') return
                        const table = node.firstChild
                        if (!table || !isTableEmpty(table)) return
                        deletions.push({ from: pos, to: pos + node.nodeSize })
                    })

                    if (!deletions.length) return null

                    const tr = newState.tr
                    for (const { from, to } of [...deletions].reverse()) {
                        tr.delete(from, to)
                    }
                    return tr
                }
            })
        ]
    }
})

export const Caption: Node = Node.create({
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
            },
            Enter: () => {
                const { $from } = this.editor.state.selection
                if ($from.parent.type.name !== this.name) return false

                for (let d = $from.depth; d > 0; d--) {
                    if ($from.node(d).type.name === 'tableFigure') {
                        const after = $from.after(d)
                        return this.editor
                            .chain()
                            .insertContentAt(after, { type: 'paragraph' })
                            .focus(after + 1)
                            .run()
                    }
                }
                return false
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
            figure.appendChild(table)
            figure.appendChild(figcaption)
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
            if (figcaption && figcaption.textContent.trim()) {
                const caption = doc.createElement('caption')
                caption.innerHTML = figcaption.innerHTML
                table.appendChild(caption)
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
        const tableChild = figureNode.firstChild
        if (!tableChild) return
        const captionStart = figurePos + 1 + tableChild.nodeSize
        const lastChild = figureNode.lastChild
        const hasCaption = lastChild?.type.name === 'tableCaption'

        if (caption) {
            if (hasCaption && lastChild) {
                const from = captionStart
                const to = from + lastChild.nodeSize
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
                    .insertContentAt(captionStart, {
                        type: 'tableCaption',
                        content: [{ type: 'text', text: caption }]
                    })
                    .run()
            }
        } else if (hasCaption && lastChild) {
            const from = captionStart
            const to = from + lastChild.nodeSize
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
                    tableNode.toJSON(),
                    { type: 'tableCaption', content: [{ type: 'text', text: caption }] }
                ]
            })
            .run()
    }
}