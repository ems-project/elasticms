import { Mark, mergeAttributes, markPasteRule, InputRule } from '@tiptap/core'
import { Plugin, PluginKey } from '@tiptap/pm/state'
import IconLink from '@tabler/icons/outline/link.svg?raw'
import IconLinkOff from '@tabler/icons/outline/link-off.svg?raw'
import { TiptapModule } from '../Types.ts'
import { linkDialog } from './Link/Dialog.ts'
import { TiptapEditor } from '../Editor.ts'
import { createLinkUploadPlugin } from './Link/Upload.ts'

export const LinkModule: TiptapModule = {
    extensions: (e) => getLinkExtension(e),
    toolbar: {
        group: 'links',
        items: [
            {
                name: 'Link',
                icon: IconLink,
                tooltip: 'link_tooltip',
                order: 1,
                command: (e) => openLinkDialog(e),
                isActive: (e) => e.tiptap.isActive('link')
            },
            {
                name: 'Unlink',
                icon: IconLinkOff,
                tooltip: 'link_unlink',
                order: 2,
                command: (e) =>
                    e.tiptap.chain().focus().extendMarkRange('link').unsetMark('link').run(),
                isActive: () => false,
                isDisabled: (e) => !e.tiptap.isActive('link')
            }
        ]
    },
    contextMenu: {
        node: 'link',
        order: 2,
        items: [
            {
                label: 'link_edit',
                icon: IconLink,
                order: 0,
                command: (e) => openLinkDialog(e)
            },
            {
                label: 'link_unlink',
                icon: IconLinkOff,
                order: 1,
                command: (e) =>
                    e.tiptap.chain().focus().extendMarkRange('link').unsetMark('link').run()
            }
        ]
    }
}

function getLinkExtension(e: TiptapEditor) {
    return [
        Mark.create({
            name: 'link',
            inclusive: false,
            priority: 1000,
            addAttributes() {
                return {
                    href: { default: null },
                    target: { default: null },
                    class: {
                        default: null,
                        parseHTML: (el) => el.getAttribute('class') || null,
                        renderHTML: (attrs) => (attrs.class ? { class: attrs.class } : {})
                    },
                    style: {
                        default: null,
                        parseHTML: (el) => el.getAttribute('style') || null,
                        renderHTML: (attrs) => (attrs.style ? { style: attrs.style } : {})
                    }
                }
            },
            parseHTML() {
                return [{ tag: 'a[href]' }]
            },
            renderHTML({ HTMLAttributes }) {
                return ['a', mergeAttributes(HTMLAttributes), 0]
            },
            addPasteRules() {
                return [
                    markPasteRule({
                        find: /https?:\/\/[^\s]+/g,
                        type: this.type,
                        getAttributes: (match) => ({ href: match[0], target: getDefaultTarget(e) })
                    }),
                    markPasteRule({
                        find: /(?<![:/])\bwww\.[^\s]+/g,
                        type: this.type,
                        getAttributes: (match) => ({
                            href: `https://${match[0]}`,
                            target: getDefaultTarget(e)
                        })
                    })
                ]
            },
            addInputRules() {
                return [
                    new InputRule({
                        find: /(https?:\/\/[^\s]+|www\.[^\s]+)\s$/,
                        handler: ({ state, range, match }) => {
                            const captured = match[1]
                            const start = range.from
                            const end = start + captured.length
                            if (state.doc.rangeHasMark(start, end, this.type)) return
                            const href = captured.startsWith('www.')
                                ? `https://${captured}`
                                : captured
                            const tr = state.tr.addMark(
                                start,
                                end,
                                this.type.create({ href, target: getDefaultTarget(e) })
                            )
                            tr.removeStoredMark(this.type)
                        }
                    })
                ]
            },
            addKeyboardShortcuts() {
                return {
                    'Mod-l': () => {
                        openLinkDialog(e)
                        return true
                    }
                }
            },
            addProseMirrorPlugins() {
                return [
                    createLinkUploadPlugin(e),
                    new Plugin({
                        key: new PluginKey('linkDoubleClick'),
                        props: {
                            ['handleDoubleClick']: (view, pos) => {
                                const marks = view.state.doc.resolve(pos).marks()
                                if (!marks.some((m) => m.type.name === 'link')) return false
                                openLinkDialog(e)
                                return true
                            }
                        }
                    }),
                    new Plugin({
                        key: new PluginKey('linkPasteOverSelection'),
                        props: {
                            handleDOMEvents: {
                                paste: (view, event) => {
                                    const { selection } = view.state
                                    if (selection.empty) return false
                                    const text = event.clipboardData?.getData('text/plain')?.trim()
                                    if (!text) return false
                                    const match = /^(https?:\/\/[^\s]+|www\.[^\s]+)$/.exec(text)
                                    if (!match) return false
                                    event.preventDefault()
                                    const href = match[1].startsWith('www.')
                                        ? `https://${match[1]}`
                                        : match[1]
                                    view.dispatch(
                                        view.state.tr.addMark(
                                            selection.from,
                                            selection.to,
                                            this.type.create({ href, target: getDefaultTarget(e) })
                                        )
                                    )
                                    return true
                                }
                            }
                        }
                    })
                ]
            }
        })
    ]
}

function openLinkDialog(e: TiptapEditor) {
    linkDialog(e, getDefaultTarget(e))
}

function getDefaultTarget(e: TiptapEditor): string | null {
    return e.profile.isUrlTargetDefaultBlank('url') ? '_blank' : null
}
