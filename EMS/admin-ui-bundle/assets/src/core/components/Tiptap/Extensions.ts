import { Extension, Mark, Node } from '@tiptap/core'
import Document from '@tiptap/extension-document'
import Paragraph from '@tiptap/extension-paragraph'
import Text from '@tiptap/extension-text'
import { Gapcursor } from '@tiptap/extension-gapcursor'
import { HardBreak } from '@tiptap/extension-hard-break'
import { Plugin, PluginKey } from '@tiptap/pm/state'
import { DOMParser, Fragment, Node as PMNode, Schema, Slice } from '@tiptap/pm/model'

export type ExtensionType = Extension | Mark | Node

export const DEFAULT_EXTENSIONS = [Document, Paragraph, Text, Gapcursor, HardBreak]

function createBlockNode(name: string, content = 'block+'): ExtensionType {
    return Node.create({
        name,
        group: 'block',
        content,
        parseHTML() {
            return [{ tag: name }]
        },
        renderHTML({ HTMLAttributes }) {
            return [name, HTMLAttributes, 0]
        }
    })
}

export const BLOCK_NODES = {
    div: createBlockNode('div'),
    pre: createBlockNode('pre', 'inline*'),
    address: createBlockNode('address', 'inline*')
} as const

const URL_REGEX = /https?:\/\/[^\s]+|(?<![:/])\bwww\.[^\s]+/g

function autoLinkFragment(fragment: Fragment, schema: Schema): Fragment {
    const linkType = schema.marks.link
    if (!linkType) return fragment

    const nodes: PMNode[] = []

    fragment.forEach((node) => {
        if (node.isText && node.text && !linkType.isInSet(node.marks)) {
            let lastIndex = 0
            let match: RegExpExecArray | null
            URL_REGEX.lastIndex = 0
            while ((match = URL_REGEX.exec(node.text))) {
                if (match.index > lastIndex) {
                    nodes.push(schema.text(node.text.slice(lastIndex, match.index), node.marks))
                }
                const href = match[0].startsWith('www.') ? `https://${match[0]}` : match[0]
                nodes.push(schema.text(match[0], [...node.marks, linkType.create({ href })]))
                lastIndex = match.index + match[0].length
            }
            if (lastIndex === 0) {
                nodes.push(node)
            } else if (lastIndex < node.text.length) {
                nodes.push(schema.text(node.text.slice(lastIndex), node.marks))
            }
        } else if (node.content.size) {
            nodes.push(node.copy(autoLinkFragment(node.content, schema)))
        } else {
            nodes.push(node)
        }
    })

    return Fragment.fromArray(nodes)
}

export const AjaxPaste = Extension.create({
    name: 'ajaxPaste',

    addOptions() {
        return {
            ajaxUrl: null as string | null
        }
    },

    addProseMirrorPlugins() {
        const { ajaxUrl } = this.options

        if (!ajaxUrl) return []

        return [
            new Plugin({
                key: new PluginKey('ajaxPaste'),
                props: {
                    handleDOMEvents: {
                        paste: (_view, event) => {
                            const html = event.clipboardData?.getData('text/html')
                            if (!html) return false

                            event.preventDefault()

                            fetch(ajaxUrl, {
                                method: 'POST',
                                body: JSON.stringify({ content: html }),
                                headers: { 'Content-Type': 'application/json' }
                            })
                                .then((response) => {
                                    if (!response.ok) return Promise.reject(response)
                                    return response.json()
                                })
                                .then((json) => {
                                    const { state, dispatch } = this.editor.view
                                    const dom = document.createElement('div')
                                    dom.innerHTML = json.content
                                    const parser = DOMParser.fromSchema(state.schema)
                                    const slice = parser.parseSlice(dom, {
                                        preserveWhitespace: false
                                    })
                                    const content = autoLinkFragment(slice.content, state.schema)
                                    const tr = state.tr.replaceSelection(
                                        new Slice(content, slice.openStart, slice.openEnd)
                                    )
                                    dispatch(tr)
                                })
                                .catch(() => console.error('error pasting'))

                            return true
                        }
                    }
                }
            })
        ]
    }
})
