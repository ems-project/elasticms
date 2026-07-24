import { Node, mergeAttributes } from '@tiptap/core'
import { Decoration, DecorationSet } from '@tiptap/pm/view'
import { Plugin, PluginKey } from '@tiptap/pm/state'
import { TiptapEditor } from '../editor.ts'
import { openImageDialog } from './dialog.ts'

export interface ImageAttrs {
    src: string | null
    alt: string | null
    width: string | null
    height: string | null
}

function stripFalsy(attrs: Record<string, unknown>): Record<string, string> {
    const result: Record<string, string> = {}
    for (const [k, v] of Object.entries(attrs)) {
        if (v !== null && v !== undefined && v !== false && v !== '') {
            result[k] = String(v)
        }
    }
    return result
}

function imageAttributes() {
    return {
        src: { default: null },
        alt: { default: null },
        width: { default: null },
        height: { default: null }
    }
}

function createImageNodeView(editor: TiptapEditor, typeName: string) {
    return ({ node, getPos }: { node: { attrs: unknown }; getPos: () => number | undefined }) => {
        const img = document.createElement('img')
        img.className = 'tiptap-image'

        const sync = (attrs: ImageAttrs) => {
            img.src = attrs.src ?? ''
            if (attrs.alt) img.setAttribute('alt', attrs.alt)
            else img.removeAttribute('alt')
            if (attrs.width) img.setAttribute('width', attrs.width)
            else img.removeAttribute('width')
            if (attrs.height) img.setAttribute('height', attrs.height)
            else img.removeAttribute('height')
        }
        sync(node.attrs as ImageAttrs)

        img.addEventListener('dblclick', () => {
            const pos = getPos()
            if (typeof pos !== 'number') return
            editor.tiptap.chain().setNodeSelection(pos).run()
            openImageDialog(editor)
        })

        return {
            dom: img,
            update: (updatedNode: { type: { name: string }; attrs: unknown }) => {
                if (updatedNode.type.name !== typeName) return false
                sync(updatedNode.attrs as ImageAttrs)
                return true
            }
        }
    }
}

export function createImageNode(editor: TiptapEditor) {
    return Node.create({
        name: 'image',
        group: 'inline',
        inline: true,
        atom: true,
        draggable: true,

        addAttributes() {
            return imageAttributes()
        },

        parseHTML() {
            return [{ tag: 'img[src]' }]
        },

        renderHTML({ HTMLAttributes }) {
            return ['img', mergeAttributes(stripFalsy(HTMLAttributes))]
        },

        addNodeView() {
            return createImageNodeView(editor, 'image')
        },

        addProseMirrorPlugins() {
            const key = new PluginKey('imageSelectionHighlight')
            return [
                new Plugin({
                    key,
                    props: {
                        decorations: (state) => {
                            const { from, to, empty } = state.selection
                            if (empty) return null

                            const decorations: Decoration[] = []
                            state.doc.nodesBetween(from, to, (node, pos) => {
                                if (node.type.name === 'image' || node.type.name === 'imageBlock') {
                                    decorations.push(
                                        Decoration.node(pos, pos + node.nodeSize, {
                                            class: 'is-in-selection'
                                        })
                                    )
                                }
                            })
                            return DecorationSet.create(state.doc, decorations)
                        }
                    }
                })
            ]
        }
    })
}

export function createImageBlockNode(editor: TiptapEditor) {
    return Node.create({
        name: 'imageBlock',
        group: 'block',
        inline: false,
        atom: true,
        draggable: true,

        addAttributes() {
            return imageAttributes()
        },

        parseHTML() {
            return [{ tag: 'figure[data-type="image"] > img[src]', priority: 60 }]
        },

        renderHTML({ HTMLAttributes }) {
            return ['img', mergeAttributes(stripFalsy(HTMLAttributes))]
        },

        addNodeView() {
            return createImageNodeView(editor, 'imageBlock')
        }
    })
}
