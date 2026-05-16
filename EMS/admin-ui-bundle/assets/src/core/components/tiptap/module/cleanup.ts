import IconClear from '@tabler/icons/outline/eraser.svg?raw'
import { EditorState, Transaction } from '@tiptap/pm/state'
import { Node as PMNode } from '@tiptap/pm/model'
import { liftTarget } from '@tiptap/pm/transform'
import { TiptapModule } from '../types.ts'
import { TiptapEditor } from '../editor.ts'

const PRESERVED_MARKS = new Set(['anchor', 'link'])
const PRESERVED_NODES = new Set([
    'heading',
    'div',
    'blockquote',
    'bulletList',
    'orderedList',
    'listItem'
])

export const cleanupModule: TiptapModule = {
    toolbarGroup: 'cleanup',
    toolbar: [
        {
            name: 'RemoveFormat',
            icon: IconClear,
            tooltip: 'Remove Format',
            command: (e) => removeFormatting(e),
            isDisabled: (e) => !hasRemovableFormatting(e)
        }
    ]
}

function isRemovableMark(name: string): boolean {
    return !PRESERVED_MARKS.has(name)
}

function isRemovableNode(node: PMNode, paragraphType: PMNode['type']): boolean {
    if (node.isText) return false
    if (node.type === paragraphType) return false
    return !PRESERVED_NODES.has(node.type.name)
}

function removeFormatting(editor: TiptapEditor): void {
    editor.tiptap
        .chain()
        .focus()
        .command(({ state, tr, dispatch }) => {
            stripMarks(state, tr)
            stripNodes(state, tr)
            if (dispatch) dispatch(tr)
            return true
        })
        .run()
}

function stripMarks(state: EditorState, tr: Transaction): void {
    const { from, to } = state.selection
    for (const [name, type] of Object.entries(state.schema.marks)) {
        if (isRemovableMark(name)) tr.removeMark(from, to, type)
    }
}

function stripNodes(state: EditorState, tr: Transaction): void {
    const { from, to } = state.selection

    state.doc.nodesBetween(from, to, (node, pos) => {
        if (node.isText || PRESERVED_NODES.has(node.type.name)) return

        const $from = tr.doc.resolve(tr.mapping.map(pos))
        const $to = tr.doc.resolve(tr.mapping.map(pos + node.nodeSize))
        const range = $from.blockRange($to)
        if (!range) return

        if (node.type.isTextblock) {
            const { defaultType } = $from.parent.contentMatchAt($from.index())
            tr.setNodeMarkup(range.start, defaultType)
        }

        const target = liftTarget(range)
        if (target !== null) tr.lift(range, target)
    })
}

function hasRemovableFormatting(editor: TiptapEditor): boolean {
    const { state } = editor.tiptap
    if (state.selection.empty) return false

    const paragraphType = state.schema.nodes.paragraph
    const { from, to } = state.selection
    let found = false

    state.doc.nodesBetween(from, to, (node) => {
        if (found) return false
        if (node.marks.some((m) => isRemovableMark(m.type.name))) found = true
        else if (isRemovableNode(node, paragraphType)) found = true
    })

    return found
}
