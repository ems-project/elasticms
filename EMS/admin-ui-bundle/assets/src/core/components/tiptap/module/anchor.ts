import { Mark, mergeAttributes } from '@tiptap/core'
import IconAnchor from '@tabler/icons/outline/anchor.svg?raw'
import IconAnchorOff from '@tabler/icons/outline/anchor-off.svg?raw'
import { TiptapModule } from '../types.ts'
import { Dialog } from '../../dialog.ts'
import { TiptapEditor } from '../editor.ts'

const FIELD_NAME = 'tiptap-anchor-name'

export const anchorModule: TiptapModule = {
    extensions: getAnchorExtension(),
    toolbarGroup: 'links',
    toolbar: [
        {
            name: 'Anchor',
            icon: IconAnchor,
            tooltip: 'Anchor',
            order: 3,
            command: (e) => openAnchorDialog(e),
            isActive: (e) => e.tiptap.isActive('anchor')
        }
    ],
    contextMenuNode: 'anchor',
    contextMenu: [
        {
            label: 'Edit Anchor',
            icon: IconAnchor,
            order: 0,
            command: (e) => openAnchorDialog(e)
        },
        {
            label: 'Remove Anchor',
            icon: IconAnchorOff,
            order: 1,
            command: (e) =>
                e.tiptap.chain().focus().extendMarkRange('anchor').unsetMark('anchor').run()
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
    const isEdit = e.tiptap.isActive('anchor')
    const existing = e.tiptap.getAttributes('anchor')?.id ?? ''

    dialog.setContent(
        `<div style="display: flex; flex-direction: column; gap: 10px; width: 300px;">
            <div>
                <label for="${FIELD_NAME}">Anchor Name <span style="color: red">*</span></label>
                <input type="text" id="${FIELD_NAME}" value="${(existing ?? '').toString().replace(/"/g, '&quot;')}" required>
            </div>
        </div>`
    )

    const apply = () => {
        const input = document.getElementById(FIELD_NAME) as HTMLInputElement
        if (!input.reportValidity()) return
        const name = input.value.trim()
        const chain = e.tiptap.chain().focus()
        if (isEdit) {
            chain.extendMarkRange('anchor').setMark('anchor', { id: name, name }).run()
        } else if (from === to) {
            chain
                .insertContent({
                    type: 'text',
                    text: '\u200B',
                    marks: [{ type: 'anchor', attrs: { id: name, name } }]
                })
                .run()
        } else {
            chain.setTextSelection({ from, to }).setMark('anchor', { id: name, name }).run()
        }
        dialog.close()
    }

    dialog
        .addButton({ label: 'Apply', variant: 'primary', onClick: () => apply() })
        .addButton({ label: 'Cancel', variant: 'secondary', onClick: (d) => d.close() })
        .open()

    const input = document.getElementById(FIELD_NAME) as HTMLInputElement
    if (input) {
        input.focus()
        input.select()

        input.addEventListener('keydown', (ev) => {
            if (ev.key === 'Enter') apply()
        })
    }
}
