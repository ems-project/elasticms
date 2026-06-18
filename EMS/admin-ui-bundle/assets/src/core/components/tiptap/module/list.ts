import BulletList from '@tiptap/extension-bullet-list'
import OrderedList from '@tiptap/extension-ordered-list'
import ListItem from '@tiptap/extension-list-item'
import IconBulletedList from '@tabler/icons/outline/list.svg?raw'
import IconNumberedList from '@tabler/icons/outline/list-numbers.svg?raw'
import { TiptapModule } from '../types.ts'
import { TiptapEditor } from '../editor.ts'
import { TranslationKey } from '../translation/en.ts'
import { escapeHtml } from '../helper.ts'

type BulletListType = 'circle' | 'disc' | 'square'

const BULLET_LIST_TYPES: { label: TranslationKey; value: BulletListType | '' }[] = [
    { label: 'list_bulleted_type_none', value: '' },
    { label: 'list_bulleted_type_circle', value: 'circle' },
    { label: 'list_bulleted_type_disc', value: 'disc' },
    { label: 'list_bulleted_type_square', value: 'square' }
]

export const listModule: TiptapModule = {
    extensions: [ListItem],
    toolbar: {
        group: 'list',
        items: [
            {
                extensions: [createCustomOrderedList()],
                name: 'NumberedList',
                icon: IconNumberedList,
                tooltip: 'list_numbered_create',
                command: (e) => e.tiptap.chain().focus().toggleOrderedList().run(),
                isActive: (e) => e.tiptap.isActive('orderedList')
            },
            {
                extensions: [createCustomBulletList()],
                name: 'BulletedList',
                icon: IconBulletedList,
                tooltip: 'list_bulleted_create',
                command: (e) => e.tiptap.chain().focus().toggleBulletList().run(),
                isActive: (e) => e.tiptap.isActive('bulletList')
            }
        ]
    },
    contextMenu: {
        node: 'bulletList',
        order: 5,
        items: [
            {
                label: 'list_bulleted_properties',
                icon: IconBulletedList,
                order: 0,
                command: (e) => openBulletListDialog(e)
            }
        ]
    }
}

function getCurrentBulletListNode(editor: TiptapEditor) {
    const { $from } = editor.tiptap.state.selection
    for (let d = $from.depth; d >= 0; d--) {
        if ($from.node(d).type.name === 'bulletList') {
            return { attrs: $from.node(d).attrs, pos: $from.before(d) }
        }
    }
    return null
}

function buildDialogContent(editor: TiptapEditor, currentStyle: string): string {
    const options = BULLET_LIST_TYPES.map(({ label, value }) => {
        const selected = value === currentStyle ? ' selected' : ''
        return `<option value="${value}"${selected}>${escapeHtml(editor.trans(label))}</option>`
    }).join('')

    return `
        <style>
            .bulletlist-form { display: flex; flex-direction: column; gap: 8px; min-width: 260px; }
            .bulletlist-form-row { display: flex; align-items: center; gap: 8px; }
            .bulletlist-form-row label { width: 110px; flex-shrink: 0; font-size: 13px; }
            .bulletlist-form-row select { flex: 1; padding: 4px 6px; font-size: 13px; border: 1px solid #ccc; border-radius: 3px; }
        </style>
        <div class="bulletlist-form">
            <div class="bulletlist-form-row">
                <label>${editor.trans('list_bulleted_type')}</label>
                <select name="listType">${options}</select>
            </div>
        </div>`
}

function openBulletListDialog(editor: TiptapEditor): void {
    const existing = getCurrentBulletListNode(editor)
    if (!existing) return

    const currentStyle = existing.attrs.dataUserStyle ?? ''

    const dialog = editor.createDialog('list_bulleted_properties')
    dialog.setContent(buildDialogContent(editor, currentStyle))

    dialog
        .addButton({
            label: editor.trans('button_update'),
            variant: 'primary',
            onClick: (d) => {
                const select = d.element.querySelector<HTMLSelectElement>('select[name="listType"]')
                const value = select?.value ?? ''
                editor.tiptap.view.dispatch(
                    editor.tiptap.state.tr.setNodeMarkup(existing.pos, undefined, {
                        ...existing.attrs,
                        dataUserStyle: value || null
                    })
                )
                d.close()
            }
        })
        .addButton({
            label: editor.trans('button_cancel'),
            variant: 'secondary',
            onClick: (d) => d.close()
        })
        .open()
}

function createCustomBulletList() {
    return BulletList.extend({
        addAttributes() {
            return {
                ...this.parent?.(),
                dataUserStyle: {
                    default: null,
                    parseHTML: (el) => el.getAttribute('data-user-style') || null,
                    renderHTML: (attrs) =>
                        attrs.dataUserStyle
                            ? { 'data-user-style': attrs.dataUserStyle, style: `list-style-type: ${attrs.dataUserStyle}` }
                            : {}
                }
            }
        }
    })
}

function createCustomOrderedList() {
    return OrderedList.extend({
        addAttributes() {
            return {
                ...this.parent?.(),
                dataUserStyle: {
                    default: null,
                    parseHTML: (el) => el.getAttribute('data-user-style') || null,
                    renderHTML: (attrs) =>
                        attrs.dataUserStyle
                            ? { 'data-user-style': attrs.dataUserStyle, style: attrs.dataUserStyle }
                            : {}
                }
            }
        }
    })
}