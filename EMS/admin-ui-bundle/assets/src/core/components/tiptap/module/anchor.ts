import { Mark, mergeAttributes } from '@tiptap/core'
import IconAnchor from '@tabler/icons/outline/anchor.svg?raw'
import IconAnchorOff from '@tabler/icons/outline/anchor-off.svg?raw'
import { TiptapModule } from '../types.ts'
import { TiptapEditor } from '../editor.ts'
import { escapeHtml } from '../helper.ts'

const ANCHOR_SELECTOR = 'a[id]:not([href])'
const FIELD_NAME = 'tiptap-anchor-name'

export const anchorModule: TiptapModule = {
    extensions: getAnchorExtension(),
    toolbarGroup: 'links',
    toolbar: [
        {
            name: 'Anchor',
            icon: IconAnchor,
            tooltip: 'link_anchor',
            order: 3,
            command: (e) => openAnchorDialog(e),
            isActive: (e) => e.tiptap.isActive('anchor')
        }
    ],
    contextMenu: {
        node: 'anchor',
        selector: ANCHOR_SELECTOR,
        order: 3,
        items: [
            {
                label: 'link_anchor_edit',
                icon: IconAnchor,
                order: 0,
                command: (e, ctx) => openAnchorDialog(e, ctx?.target)
            },
            {
                label: 'link_anchor_remove',
                icon: IconAnchorOff,
                order: 1,
                command: (e, ctx) => {
                    selectAnchorEl(e, ctx?.target)
                    e.tiptap.chain().focus().extendMarkRange('anchor').unsetMark('anchor').run()
                }
            }
        ]
    }
}

function getAnchorExtension() {
    return [
        Mark.create({
            name: 'anchor',
            inclusive: false,
            addAttributes() {
                return { id: { default: null } }
            },
            parseHTML() {
                return [{ tag: ANCHOR_SELECTOR }]
            },
            renderHTML({ HTMLAttributes }) {
                return ['a', mergeAttributes(HTMLAttributes), 0]
            }
        })
    ]
}

function selectAnchorEl(e: TiptapEditor, target?: Element | null) {
    const el = (target as HTMLElement | null)?.closest(ANCHOR_SELECTOR) as HTMLAnchorElement | null
    if (!el) return null
    const pos = e.tiptap.view.posAtDOM(el.firstChild ?? el, 0)
    const size = Math.max(el.textContent?.length ?? 1, 1)
    e.tiptap.commands.setTextSelection({ from: pos, to: pos + size })
    return el
}

function applyAnchor(e: TiptapEditor, name: string, isEdit: boolean, from: number, to: number) {
    const chain = e.tiptap.chain().focus()
    if (isEdit) {
        chain.extendMarkRange('anchor').setMark('anchor', { id: name }).run()
        return
    }
    if (from === to) {
        chain
            .insertContent({
                type: 'text',
                text: '\u200B',
                marks: [{ type: 'anchor', attrs: { id: name } }]
            })
            .run()
        return
    }
    chain.setTextSelection({ from, to }).setMark('anchor', { id: name }).run()
}

function openAnchorDialog(e: TiptapEditor, target?: Element | null) {
    const dialog = e.createDialog('link_anchor_properties')
    const { from, to } = e.tiptap.state.selection

    const el = selectAnchorEl(e, target)
    const isEdit = !!el || e.tiptap.isActive('anchor')
    const existing = el?.getAttribute('id') ?? e.tiptap.getAttributes('anchor')?.id ?? ''

    dialog.setContent(
        `<div style="display: flex; flex-direction: column; gap: 10px; width: 300px;">
            <div>
                <label for="${FIELD_NAME}">${e.trans('link_anchor_name')} <span style="color: red">*</span></label>
                <input type="text" id="${FIELD_NAME}" value="${escapeHtml(existing)}" required>
            </div>
        </div>`
    )

    const getInput = () => dialog.element.querySelector<HTMLInputElement>(`#${FIELD_NAME}`)!

    const apply = () => {
        const input = getInput()
        if (!input.reportValidity()) return
        applyAnchor(e, input.value.trim(), isEdit, from, to)
        dialog.close()
    }

    dialog
        .addButton({ label: e.trans('button_apply'), variant: 'primary', onClick: apply })
        .addButton({
            label: e.trans('button_cancel'),
            variant: 'secondary',
            onClick: (d) => d.close()
        })
        .open()

    const input = getInput()
    input.focus()
    input.select()
    input.addEventListener('keydown', (ev) => {
        if (ev.key === 'Enter') apply()
    })
}
