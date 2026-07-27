import IconDiv from '@tabler/icons/outline/layout-bottombar.svg?raw'
import IconEdit from '@tabler/icons/outline/edit.svg?raw'
import IconTrash from '@tabler/icons/outline/trash.svg?raw'
import { Extension } from '@tiptap/core'
import { TiptapModule } from '../Types.ts'
import { TiptapEditor } from '../Editor.ts'
import { BLOCK_NODES } from '../Extensions.ts'
import { escapeHtml } from '../Helper.ts'
import { TranslationKey } from '../Translations.ts'

const ATTR_MAP: Record<string, string> = {
    htmlClass: 'class',
    id: 'id',
    lang: 'lang',
    htmlStyle: 'style',
    title: 'title'
}

const FIELDS: { label: TranslationKey; name: keyof typeof ATTR_MAP }[] = [
    { label: 'classes', name: 'htmlClass' },
    { label: 'id', name: 'id' },
    { label: 'div_field_lang', name: 'lang' },
    { label: 'style_inline', name: 'htmlStyle' },
    { label: 'div_field_title', name: 'title' }
]

type DivAttrs = Record<keyof typeof ATTR_MAP, string | null>
type DivFormValues = Record<keyof typeof ATTR_MAP, string>

type ExistingDiv = {
    attrs: Record<string, any>
    pos: number
}

type StyleOption = { label: string; value: string }

export const DivModule: TiptapModule = {
    isEnabled: (wysiwygProfile) => wysiwygProfile.hasPlugin('div'),
    extensions: [
        BLOCK_NODES.div,
        Extension.create({
            name: 'divAttributes',
            addGlobalAttributes() {
                return [{ types: ['div'], attributes: buildAttributesConfig() }]
            }
        })
    ],
    toolbar: {
        group: 'blocks',
        items: [
            {
                name: 'Div',
                icon: IconDiv,
                tooltip: 'div_create',
                order: 99,
                command: (e) => openDivDialog(e, null),
                isActive: (e) => e.tiptap.isActive('div')
            }
        ]
    },
    contextMenu: {
        node: 'div',
        order: 4,
        items: [
            {
                label: 'div_edit',
                icon: IconEdit,
                order: 0,
                command: (e) => {
                    const existing = getCurrentDivNode(e)
                    if (existing) openDivDialog(e, existing)
                }
            },
            {
                label: 'div_remove',
                icon: IconTrash,
                order: 1,
                command: (e) => removeDiv(e)
            }
        ]
    }
}

function buildAttributesConfig() {
    const config: Record<string, any> = {}
    for (const [name, htmlAttr] of Object.entries(ATTR_MAP)) {
        config[name] = {
            default: null,
            parseHTML: (el: HTMLElement) => el.getAttribute(htmlAttr) || null,
            renderHTML: (attrs: Record<string, any>) =>
                attrs[name] ? { [htmlAttr]: attrs[name] } : {}
        }
    }
    return config
}

function getCurrentDivNode(editor: TiptapEditor): ExistingDiv | null {
    const { $from } = editor.tiptap.state.selection
    for (let d = $from.depth; d >= 0; d--) {
        if ($from.node(d).type.name === 'div') {
            return { attrs: $from.node(d).attrs, pos: $from.before(d) }
        }
    }
    return null
}

function getDivStyleOptions(editor: TiptapEditor): StyleOption[] {
    return editor
        .getWysiwygStyles()
        .filter((s) => s.element === 'div' && s.attributes?.class)
        .map((s) => ({ label: s.name, value: s.attributes!.class! }))
}

function getFormValues(el: HTMLElement): DivFormValues {
    const values = {} as DivFormValues
    for (const { name } of FIELDS) {
        values[name] =
            el.querySelector<HTMLInputElement>(`input[name="${name}"]`)?.value.trim() ?? ''
    }
    return values
}

function toAttrs(values: DivFormValues): DivAttrs {
    const attrs = {} as DivAttrs
    for (const { name } of FIELDS) {
        attrs[name] = values[name] || null
    }
    return attrs
}

function readExisting(existing: ExistingDiv | null): DivFormValues {
    const values = {} as DivFormValues
    for (const { name } of FIELDS) {
        values[name] = existing?.attrs[name] ?? ''
    }
    return values
}

function removeDiv(editor: TiptapEditor): void {
    const existing = getCurrentDivNode(editor)
    if (!existing) return
    const node = editor.tiptap.state.doc.nodeAt(existing.pos)
    if (!node) return
    editor.tiptap.view.dispatch(
        editor.tiptap.state.tr.replaceWith(existing.pos, existing.pos + node.nodeSize, node.content)
    )
}

function saveDiv(editor: TiptapEditor, existing: ExistingDiv | null, attrs: DivAttrs): void {
    if (existing) {
        editor.tiptap.view.dispatch(
            editor.tiptap.state.tr.setNodeMarkup(existing.pos, undefined, {
                ...existing.attrs,
                ...attrs
            })
        )
        return
    }
    editor.tiptap.chain().focus().wrapIn('div', attrs).setMeta('applyStyle', true).run()
}

function buildDialogContent(
    editor: TiptapEditor,
    styleOptions: StyleOption[],
    current: DivFormValues
): string {
    const presetRow =
        styleOptions.length === 0
            ? ''
            : `
        <div class="div-form-row">
            <label>${editor.trans('style')}</label>
            <select class="div-class-preset">
                <option value="">${editor.trans('select')}</option>
                ${styleOptions
                    .map((o) => {
                        const selected = o.value === current.htmlClass ? ' selected' : ''
                        return `<option value="${escapeHtml(o.value)}"${selected}>${escapeHtml(o.label)}</option>`
                    })
                    .join('')}
            </select>
        </div>`

    const fieldsHtml = FIELDS.map(
        ({ label, name }) => `
        <div class="div-form-row">
            <label>${editor.trans(label)}</label>
            <input type="text" name="${name}" value="${escapeHtml(current[name])}" />
        </div>`
    ).join('')

    return `
        <style>
            .div-dialog-form { display: flex; flex-direction: column; gap: 8px; min-width: 340px; }
            .div-form-row { display: flex; align-items: center; gap: 8px; }
            .div-form-row label { width: 110px; flex-shrink: 0; font-size: 13px; }
            .div-form-row input,
            .div-form-row select { flex: 1; padding: 4px 6px; font-size: 13px; border: 1px solid #ccc; border-radius: 3px; }
        </style>
        <div class="div-dialog-form">
            ${presetRow}
            ${fieldsHtml}
        </div>`
}

function bindPresetSync(el: HTMLElement): void {
    const select = el.querySelector<HTMLSelectElement>('.div-class-preset')
    const classInput = el.querySelector<HTMLInputElement>('input[name="htmlClass"]')
    if (!select || !classInput) return

    select.addEventListener('change', () => {
        if (select.value) classInput.value = select.value
    })
}

function openDivDialog(editor: TiptapEditor, existing: ExistingDiv | null): void {
    const styleOptions = getDivStyleOptions(editor)
    const current = readExisting(existing)

    const dialog = editor.createDialog(existing ? 'div_edit' : 'div_create')
    dialog.setContent(buildDialogContent(editor, styleOptions, current))

    dialog
        .addButton({
            label: editor.trans(existing ? 'button_update' : 'button_insert'),
            variant: 'primary',
            onClick: (d) => {
                saveDiv(editor, existing, toAttrs(getFormValues(d.element)))
                d.close()
            }
        })
        .addButton({
            label: editor.trans('button_cancel'),
            variant: 'secondary',
            onClick: (d) => d.close()
        })
        .open()

    bindPresetSync(dialog.element)
}
