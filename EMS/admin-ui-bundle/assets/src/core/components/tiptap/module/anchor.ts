import { Mark, mergeAttributes } from '@tiptap/core'
import IconAnchor from '@tabler/icons/outline/anchor.svg?raw'
import { TiptapModule } from '../types.ts'
import { Dialog } from '../../dialog.ts'
import { TiptapEditor } from '../editor.ts'

export const anchorModule: TiptapModule = {
    extensions: getAnchorExtension(),
    toolbarGroup: 'insert',
    toolbar: [
        {
            name: 'Anchor',
            icon: IconAnchor,
            tooltip: 'Anchor',
            command: (e) => openAnchorDialog(e),
            isActive: (e) => e.tiptap.isActive('anchor')
        }
    ]
}

function getAnchorExtension() {
    return [
        Mark.create({
            name: 'anchor',
            inclusive: false,

            addAttributes() {
                return {
                    id: { default: null },
                    name: { default: null }
                }
            },

            parseHTML() {
                return [{ tag: 'a[name]:not([href])' }]
            },

            renderHTML({ HTMLAttributes }) {
                return ['a', mergeAttributes(HTMLAttributes), 0]
            }
        })
    ]
}

function openAnchorDialog(e: TiptapEditor) {
    const dialog = new Dialog('Anchor Properties', { draggable: true })

    const { from, to } = e.tiptap.state.selection
    const existing = e.tiptap.getAttributes('anchor')?.id ?? ''

    dialog.setContent(
        `<div style="display: flex; flex-direction: column; gap: 10px; width: 300px;">
            <div>
                <label for="anchor-name">Anchor Name <span style="color: red">*</span></label>
                <input type="text" id="anchor-name" value="${(existing ?? '').toString().replace(/"/g, '&quot;')}" required>
            </div>
        </div>`
    )

    dialog.addButton({
        label: 'Apply',
        variant: 'primary',
        onClick: (d) => {
            const input = document.getElementById('anchor-name') as HTMLInputElement
            if (!input.reportValidity()) return
            const name = input.value.trim()
            const chain = e.tiptap.chain().focus().setTextSelection({ from, to })
            if (from === to) {
                chain.insertContent({ type: 'text', text: '\u200B', marks: [{ type: 'anchor', attrs: { id: name, name } }] }).run()
            } else {
                chain.setMark('anchor', { id: name, name }).run()
            }
            d.close()
        }
    })

    dialog.addButton({
        label: 'Cancel',
        variant: 'secondary',
        onClick: (d) => d.close()
    })

    dialog.open()
}