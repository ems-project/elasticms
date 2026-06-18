import BulletList from '@tiptap/extension-bullet-list'
import OrderedList from '@tiptap/extension-ordered-list'
import ListItem from '@tiptap/extension-list-item'
import IconBulletedList from '@tabler/icons/outline/list.svg?raw'
import IconNumberedList from '@tabler/icons/outline/list-numbers.svg?raw'
import IconEdit from '@tabler/icons/outline/edit.svg?raw'
import { TiptapModule } from '../types.ts'
import { TiptapEditor } from '../editor.ts'
import { TranslationKey } from '../translation/en.ts'
import { escapeHtml } from '../helper.ts'

type BulletListType = 'circle' | 'disc' | 'square'
type NumberedListType = 'decimal' | 'lower-alpha' | 'upper-alpha' | 'lower-roman' | 'upper-roman'

const BULLET_LIST_TYPES: { label: TranslationKey; value: BulletListType | '' }[] = [
    { label: 'list_type_none', value: '' },
    { label: 'list_bulleted_type_circle', value: 'circle' },
    { label: 'list_bulleted_type_disc', value: 'disc' },
    { label: 'list_bulleted_type_square', value: 'square' }
]

const NUMBERED_LIST_TYPES: { label: TranslationKey; value: NumberedListType | '' }[] = [
    { label: 'list_type_none', value: '' },
    { label: 'list_numbered_decimal', value: 'decimal' },
    { label: 'list_numbered_lower_alpha', value: 'lower-alpha' },
    { label: 'list_numbered_upper_alpha', value: 'upper-alpha' },
    { label: 'list_numbered_lower_roman', value: 'lower-roman' },
    { label: 'list_numbered_upper_roman', value: 'upper-roman' }
]

export const listModule: TiptapModule[] = [
    {
        extensions: [ListItem, createCustomBulletList()],
        toolbar: {
            group: 'list',
            items: [
                {
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
                    icon: IconEdit,
                    order: 0,
                    command: (e) => openBulletListDialog(e)
                }
            ]
        }
    },
    {
        extensions: [createCustomOrderedList()],
        toolbar: {
            group: 'list',
            items: [
                {
                    name: 'NumberedList',
                    icon: IconNumberedList,
                    tooltip: 'list_numbered_create',
                    command: (e) => e.tiptap.chain().focus().toggleOrderedList().run(),
                    isActive: (e) => e.tiptap.isActive('orderedList')
                }
            ]
        },
        contextMenu: {
            node: 'orderedList',
            order: 6,
            items: [
                {
                    label: 'list_numbered_properties',
                    icon: IconEdit,
                    order: 0,
                    command: (e) => openNumberedListDialog(e)
                }
            ]
        }
    }
]

function getCurrentListNode(editor: TiptapEditor, type: 'bulletList' | 'orderedList') {
    const { $from } = editor.tiptap.state.selection
    for (let d = $from.depth; d >= 0; d--) {
        if ($from.node(d).type.name === type) {
            return { attrs: $from.node(d).attrs, pos: $from.before(d) }
        }
    }
    return null
}

function buildTypeOptions(
    editor: TiptapEditor,
    types: { label: TranslationKey; value: string }[],
    currentValue: string
): string {
    return types
        .map(({ label, value }) => {
            const selected = value === currentValue ? ' selected' : ''
            return `<option value="${value}"${selected}>${escapeHtml(editor.trans(label))}</option>`
        })
        .join('')
}

function openBulletListDialog(editor: TiptapEditor): void {
    const existing = getCurrentListNode(editor, 'bulletList')
    if (!existing) return

    const currentStyle = existing.attrs.dataUserStyle ?? ''
    const dialog = editor.createDialog('list_bulleted_properties', 'tiptap-dialog-list')

    dialog.setContent(`
            <div>
                <label for="listType">${editor.trans('list_bulleted_type')}</label>
                <select id="listType" name="listType">${buildTypeOptions(editor, BULLET_LIST_TYPES, currentStyle)}</select>
            </div>
        `)

    dialog
        .addButton({
            label: editor.trans('button_update'),
            variant: 'primary',
            onClick: (d) => {
                const value =
                    d.element.querySelector<HTMLSelectElement>('select[name="listType"]')?.value ??
                    ''
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

function openNumberedListDialog(editor: TiptapEditor): void {
    const existing = getCurrentListNode(editor, 'orderedList')
    if (!existing) return

    const currentStyle = existing.attrs.dataUserStyle ?? ''
    const currentStart = existing.attrs.start ?? 1

    const dialog = editor.createDialog('list_numbered_properties', 'tiptap-dialog-list')

    dialog.setContent(`
            <div style="width: 70px;">
                <label for="listStart">${editor.trans('list_numbered_start')}</label>
                <input type="number" id="listStart" name="listStart" value="${currentStart}" min="1" />
            </div>
            <div>
                <label for="listType">${editor.trans('list_numbered_type')}</label>
                <select id="listType" name="listType">${buildTypeOptions(editor, NUMBERED_LIST_TYPES, currentStyle)}</select>
            </div>
  `)

    dialog
        .addButton({
            label: editor.trans('button_update'),
            variant: 'primary',
            onClick: (d) => {
                const value =
                    d.element.querySelector<HTMLSelectElement>('select[name="listType"]')?.value ??
                    ''
                const start = parseInt(
                    d.element.querySelector<HTMLInputElement>('input[name="listStart"]')?.value ??
                        '1',
                    10
                )
                editor.tiptap.view.dispatch(
                    editor.tiptap.state.tr.setNodeMarkup(existing.pos, undefined, {
                        ...existing.attrs,
                        dataUserStyle: value || null,
                        start: start || 1
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
                            ? {
                                  'data-user-style': attrs.dataUserStyle,
                                  style: `list-style-type: ${attrs.dataUserStyle}`
                              }
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
                            ? {
                                  'data-user-style': attrs.dataUserStyle,
                                  style: `list-style-type: ${attrs.dataUserStyle}`
                              }
                            : {}
                }
            }
        }
    })
}
