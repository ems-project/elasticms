import { Extension, Mark, Node } from '@tiptap/core'
import Document from '@tiptap/extension-document'
import Paragraph from '@tiptap/extension-paragraph'
import Text from '@tiptap/extension-text'
import { Gapcursor } from '@tiptap/extension-gapcursor'
import { HardBreak } from '@tiptap/extension-hard-break'
import { Plugin, PluginKey } from '@tiptap/pm/state'
import { DOMParser } from '@tiptap/pm/model'

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
                                    const tr = state.tr.replaceSelection(slice)
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
