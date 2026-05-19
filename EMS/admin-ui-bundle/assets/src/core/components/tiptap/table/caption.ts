import { Node, mergeAttributes, Editor } from '@tiptap/core'
import { Node as PMNode } from 'prosemirror-model'
import { EditorState, Plugin } from 'prosemirror-state'
import { HtmlTransform } from '../types.ts'

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
        return [emptyFigureCleaner()]
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
            Backspace: () => removeEmptyCaption(this.editor, this.name),
            Enter: () => exitCaption(this.editor, this.name)
        }
    }
})

export const tableCaptionHtmlTransform: HtmlTransform = {
    name: 'tableCaption',
    toEditor(doc) {
        doc.querySelectorAll('table').forEach(wrapTableWithFigure)
    },
    toOutput(doc) {
        doc.querySelectorAll('figure[data-type="table"]').forEach(unwrapFigure)
    }
}

export function updateCaption(tiptap: Editor, caption: string) {
    const figure = findAncestor(tiptap.state, 'tableFigure')
    if (figure) {
        setCaptionInFigure(tiptap, figure.node, figure.pos, caption)
        return
    }
    if (!caption) return
    const table = findAncestor(tiptap.state, 'table')
    if (table) wrapTableInFigure(tiptap, table.node, table.pos, caption)
}

type Ancestor = { node: PMNode; pos: number; depth: number }

function findAncestor(state: EditorState, typeName: string): Ancestor | null {
    const { $from } = state.selection
    for (let d = $from.depth; d > 0; d--) {
        const node = $from.node(d)
        if (node.type.name === typeName) {
            return { node, pos: $from.before(d), depth: d }
        }
    }
    return null
}

function isTableEmpty(table: PMNode): boolean {
    let hasContent = false
    table.descendants((node) => {
        const role = node.type.spec.tableRole
        if (role !== 'cell' && role !== 'header_cell') return
        if (node.childCount > 1) {
            hasContent = true
            return
        }
        if (node.firstChild && node.firstChild.childCount > 0) hasContent = true
    })
    return !hasContent
}

function sliceContainsType(slice: any, typeName: string): boolean {
    if (!slice?.content) return false
    let found = false
    slice.content.forEach((node: PMNode) => {
        if (node.type.name === typeName) found = true
    })
    return found
}

function isInsertingFigure(transactions: readonly any[]): boolean {
    return transactions.some((tr) =>
        tr.steps.some((step: any) => sliceContainsType(step.slice, 'tableFigure'))
    )
}

function emptyFigureCleaner(): Plugin {
    return new Plugin({
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
            for (const { from, to } of deletions.reverse()) tr.delete(from, to)
            return tr
        }
    })
}

function removeEmptyCaption(editor: Editor, captionType: string): boolean {
    const { $from, empty } = editor.state.selection
    if (!empty) return false
    if ($from.parent.type.name !== captionType) return false
    if ($from.parent.content.size > 0) return false

    return editor.chain().deleteRange({ from: $from.before(), to: $from.after() }).focus().run()
}

function exitCaption(editor: Editor, captionType: string): boolean {
    const { $from } = editor.state.selection
    if ($from.parent.type.name !== captionType) return false

    const figure = findAncestor(editor.state, 'tableFigure')
    if (!figure) return false

    const after = $from.after(figure.depth)
    return editor
        .chain()
        .insertContentAt(after, { type: 'paragraph' })
        .focus(after + 1)
        .run()
}

function captionContent(text: string) {
    return { type: 'tableCaption', content: [{ type: 'text', text }] }
}

function setCaptionInFigure(
    tiptap: Editor,
    figureNode: PMNode,
    figurePos: number,
    caption: string
) {
    const tableChild = figureNode.firstChild
    if (!tableChild) return

    const captionStart = figurePos + 1 + tableChild.nodeSize
    const lastChild = figureNode.lastChild
    const existing = lastChild?.type.name === 'tableCaption' ? lastChild : null

    if (!caption) {
        if (!existing) return
        tiptap
            .chain()
            .focus()
            .deleteRange({
                from: captionStart,
                to: captionStart + existing.nodeSize
            })
            .run()
        return
    }

    const chain = tiptap.chain().focus()
    if (existing) {
        chain.deleteRange({ from: captionStart, to: captionStart + existing.nodeSize })
    }
    chain.insertContentAt(captionStart, captionContent(caption)).run()
}

function wrapTableInFigure(tiptap: Editor, tableNode: PMNode, tablePos: number, caption: string) {
    const tableEnd = tablePos + tableNode.nodeSize
    tiptap
        .chain()
        .focus()
        .deleteRange({ from: tablePos, to: tableEnd })
        .insertContentAt(tablePos, {
            type: 'tableFigure',
            content: [tableNode.toJSON(), captionContent(caption)]
        })
        .run()
}

function wrapTableWithFigure(table: Element) {
    const caption = table.querySelector(':scope > caption')
    if (!caption) return
    const doc = table.ownerDocument
    const figure = doc.createElement('figure')
    figure.setAttribute('data-type', 'table')
    const figcaption = doc.createElement('figcaption')
    figcaption.innerHTML = caption.innerHTML
    caption.remove()
    table.replaceWith(figure)
    figure.appendChild(table)
    figure.appendChild(figcaption)
}

function unwrapFigure(fig: Element) {
    const figcaption = fig.querySelector(':scope > figcaption')
    const table = fig.querySelector(':scope > table')
    if (!table) {
        fig.remove()
        return
    }
    if (figcaption && figcaption.textContent.trim()) {
        const doc = fig.ownerDocument
        const caption = doc.createElement('caption')
        caption.innerHTML = figcaption.innerHTML
        table.appendChild(caption)
    }
    fig.replaceWith(table)
}
