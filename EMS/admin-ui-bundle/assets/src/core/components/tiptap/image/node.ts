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
        const wrapper = document.createElement('span')
        wrapper.className = 'tiptap-image'

        const img = document.createElement('img')
        img.className = 'tiptap-image-el'

        const handle = document.createElement('span')
        handle.className = 'tiptap-image-resize-handle'

        wrapper.append(img, handle)

        const sync = (attrs: ImageAttrs) => {
            img.src = attrs.src ?? ''
            if (attrs.alt) img.setAttribute('alt', attrs.alt)
            else img.removeAttribute('alt')
            if (attrs.width) {
                img.setAttribute('width', attrs.width)
                img.style.setProperty('width', `${attrs.width}px`, 'important')
            } else {
                img.removeAttribute('width')
                img.style.removeProperty('width')
            }
            if (attrs.height) {
                img.setAttribute('height', attrs.height)
                img.style.setProperty('height', `${attrs.height}px`, 'important')
            } else {
                img.removeAttribute('height')
                img.style.removeProperty('height')
            }
        }
        sync(node.attrs as ImageAttrs)

        img.addEventListener('dblclick', () => {
            const pos = getPos()
            if (typeof pos !== 'number') return
            editor.tiptap.chain().setNodeSelection(pos).run()
            openImageDialog(editor)
        })

        let startX = 0
        let startWidth = 0
        let ratio = 1

        handle.addEventListener('pointerdown', (e: PointerEvent) => {
            e.preventDefault()
            e.stopPropagation()
            handle.setPointerCapture(e.pointerId)
            startX = e.clientX
            const rect = img.getBoundingClientRect()
            startWidth = rect.width
            ratio = rect.width && rect.height ? rect.height / rect.width : 1
        })

        handle.addEventListener('pointermove', (e: PointerEvent) => {
            if (!handle.hasPointerCapture(e.pointerId)) return
            const newWidth = Math.max(20, Math.round(startWidth + (e.clientX - startX)))
            const newHeight = Math.round(newWidth * ratio)
            img.setAttribute('width', String(newWidth))
            img.setAttribute('height', String(newHeight))
            img.style.setProperty('width', `${newWidth}px`, 'important')
            img.style.setProperty('height', `${newHeight}px`, 'important')
        })

        handle.addEventListener('pointerup', (e: PointerEvent) => {
            if (!handle.hasPointerCapture(e.pointerId)) return
            handle.releasePointerCapture(e.pointerId)
            const pos = getPos()
            if (typeof pos !== 'number') return
            editor.tiptap
                .chain()
                .setNodeSelection(pos)
                .updateAttributes(typeName, {
                    width: img.getAttribute('width'),
                    height: img.getAttribute('height')
                })
                .run()
        })

        return {
            dom: wrapper,
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