import { Editor, Node, mergeAttributes } from '@tiptap/core'
import { Node as PMNode } from 'prosemirror-model'
import { EditorState, NodeSelection, Plugin } from 'prosemirror-state'

export const ImageFigure: Node = Node.create({
    name: 'imageFigure',
    group: 'block',
    content: 'imageBlock imageCaption?',
    isolating: true,

    parseHTML() {
        return [{ tag: 'figure[data-type="image"]' }]
    },

    renderHTML({ HTMLAttributes }) {
        return ['figure', mergeAttributes(HTMLAttributes, { 'data-type': 'image' }), 0]
    },

    addProseMirrorPlugins() {
        return [orphanedFigureCleaner()]
    }
})

export const ImageCaption: Node = Node.create({
    name: 'imageCaption',
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
            Backspace: () => removeEmptyCaption(this.editor),
            Enter: () => exitCaption(this.editor)
        }
    }
})

export function getImageCaption(state: EditorState): string {
    const figure = findAncestor(state, 'imageFigure')
    if (!figure) return ''
    const last = figure.node.lastChild
    return last?.type.name === 'imageCaption' ? last.textContent : ''
}

export function updateImageCaption(editor: Editor, caption: string): void {
    const figure = findAncestor(editor.state, 'imageFigure')

    if (figure) {
        if (caption) {
            setCaptionInFigure(editor, figure.node, figure.pos, caption)
        } else {
            unwrapImageFigure(editor, figure.node, figure.pos)
        }
        return
    }

    if (!caption) return
    wrapImageInFigure(editor, caption)
}

export function removeImage(editor: Editor): void {
    const figure = findAncestor(editor.state, 'imageFigure')
    if (figure) {
        editor
            .chain()
            .focus()
            .deleteRange({ from: figure.pos, to: figure.pos + figure.node.nodeSize })
            .run()
        return
    }
    editor.chain().focus().deleteSelection().run()
}

export function findImageFigure(state: EditorState): Ancestor | null {
    return findAncestor(state, 'imageFigure')
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

function getSelectedImage(state: EditorState): { node: PMNode; pos: number } | null {
    const { selection } = state
    if (selection instanceof NodeSelection && selection.node.type.name === 'image') {
        return { node: selection.node, pos: selection.from }
    }
    return null
}

function captionContent(editor: Editor, text: string): PMNode {
    return editor.schema.nodes.imageCaption.create(null, editor.schema.text(text))
}

function setCaptionInFigure(
    editor: Editor,
    figureNode: PMNode,
    figurePos: number,
    caption: string
) {
    const imageChild = figureNode.firstChild
    if (!imageChild) return

    const captionStart = figurePos + 1 + imageChild.nodeSize
    const lastChild = figureNode.lastChild
    const existing = lastChild?.type.name === 'imageCaption' ? lastChild : null

    const tr = editor.state.tr
    if (existing) {
        tr.replaceWith(
            captionStart,
            captionStart + existing.nodeSize,
            captionContent(editor, caption)
        )
    } else {
        tr.insert(captionStart, captionContent(editor, caption))
    }
    editor.view.dispatch(tr)
    editor.commands.focus()
}

function unwrapImageFigure(editor: Editor, figureNode: PMNode, figurePos: number) {
    const blockImage = figureNode.firstChild
    if (!blockImage) return

    const inlineImage = editor.schema.nodes.image.create(blockImage.attrs)

    const tr = editor.state.tr
    tr.replaceWith(
        figurePos,
        figurePos + figureNode.nodeSize,
        editor.schema.nodes.paragraph.create(null, inlineImage)
    )
    editor.view.dispatch(tr)
    editor.commands.focus()
}

function wrapImageInFigure(editor: Editor, caption: string) {
    const selected = getSelectedImage(editor.state)
    if (!selected) return

    const { node: imageNode, pos: imagePos } = selected
    const $img = editor.state.doc.resolve(imagePos)
    const parent = $img.parent
    const parentPos = $img.before()
    const parentEnd = parentPos + parent.nodeSize
    const soleContent = parent.childCount === 1

    const blockImage = editor.schema.nodes.imageBlock.create(imageNode.attrs)
    const figureNode = editor.schema.nodes.imageFigure.create(null, [
        blockImage,
        captionContent(editor, caption)
    ])

    const tr = editor.state.tr
    if (soleContent) {
        tr.replaceWith(parentPos, parentEnd, figureNode)
    } else {
        tr.delete(imagePos, imagePos + imageNode.nodeSize)
        const insertPos = tr.mapping.map(parentEnd)
        tr.insert(insertPos, figureNode)
    }
    editor.view.dispatch(tr)
    editor.commands.focus()
}

function removeEmptyCaption(editor: Editor): boolean {
    const { $from, empty } = editor.state.selection
    if (!empty) return false
    if ($from.parent.type.name !== 'imageCaption') return false
    if ($from.parent.content.size > 0) return false

    const figure = findAncestor(editor.state, 'imageFigure')
    if (!figure) return false

    unwrapImageFigure(editor, figure.node, figure.pos)
    return true
}

function exitCaption(editor: Editor): boolean {
    const { $from } = editor.state.selection
    if ($from.parent.type.name !== 'imageCaption') return false

    const figure = findAncestor(editor.state, 'imageFigure')
    if (!figure) return false

    const after = $from.after(figure.depth)
    return editor
        .chain()
        .insertContentAt(after, { type: 'paragraph' })
        .focus(after + 1)
        .run()
}

function orphanedFigureCleaner(): Plugin {
    return new Plugin({
        appendTransaction(transactions, oldState, newState) {
            if (!transactions.some((tr) => tr.docChanged)) return null
            if (newState.doc.nodeSize >= oldState.doc.nodeSize) return null

            const deletions: { from: number; to: number }[] = []
            newState.doc.descendants((node, pos) => {
                if (node.type.name !== 'imageFigure') return
                if (node.firstChild?.type.name === 'imageBlock') return
                deletions.push({ from: pos, to: pos + node.nodeSize })
            })

            if (!deletions.length) return null
            const tr = newState.tr
            for (const { from, to } of deletions.reverse()) tr.delete(from, to)
            return tr
        }
    })
}
