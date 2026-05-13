import IconDiv from '@tabler/icons/outline/layout-bottombar.svg?raw'
import IconEdit from '@tabler/icons/outline/edit.svg?raw'
import IconTrash from '@tabler/icons/outline/trash.svg?raw'
import { Extension } from '@tiptap/core'
import { TiptapModule } from '../types.ts'
import { Dialog } from '../../dialog.ts'
import { TiptapEditor } from '../editor.ts'
import { BLOCK_NODES } from '../extensions.ts'

export const divModule: TiptapModule = {
    isEnabled: (wysiwygProfile) =>
        wysiwygProfile.config.extraPlugins?.includes('div') ?? false,
    extensions: [
        BLOCK_NODES.div,
        Extension.create({
            name: 'divAttributes',
            addGlobalAttributes() {
                return [
                    {
                        types: ['div'],
                        attributes: {
                            htmlClass: {
                                default: null,
                                parseHTML: (el) => el.getAttribute('class') || null,
                                renderHTML: (attrs) =>
                                    attrs.htmlClass ? { class: attrs.htmlClass } : {}
                            },
                            htmlStyle: {
                                default: null,
                                parseHTML: (el) => el.getAttribute('style') || null,
                                renderHTML: (attrs) =>
                                    attrs.htmlStyle ? { style: attrs.htmlStyle } : {}
                            },
                            id: {
                                default: null,
                                parseHTML: (el) => el.getAttribute('id') || null,
                                renderHTML: (attrs) => (attrs.id ? { id: attrs.id } : {})
                            },
                            lang: {
                                default: null,
                                parseHTML: (el) => el.getAttribute('lang') || null,
                                renderHTML: (attrs) => (attrs.lang ? { lang: attrs.lang } : {})
                            },
                            title: {
                                default: null,
                                parseHTML: (el) => el.getAttribute('title') || null,
                                renderHTML: (attrs) => (attrs.title ? { title: attrs.title } : {})
                            }
                        }
                    }
                ]
            }
        })
    ],
    toolbarGroup: 'blocks',
    toolbar: [
        {
            name: 'Div',
            icon: IconDiv,
            tooltip: 'Create Div Container',
            command: (e) => openInsertDivDialog(e)
        }
    ],
    contextMenuNode: 'div',
    contextMenu: [
        {
            label: 'Edit Div',
            icon: IconEdit,
            order: 0,
            command: (e) => openEditDivDialog(e)
        },
        {
            label: 'Remove Div',
            icon: IconTrash,
            order: 1,
            command: (e) => removeDiv(e)
        }
    ]
}

// ─── Types ───────────────────────────────────────────────────

type DivFormValues = {
    htmlClass: string
    id: string
    lang: string
    htmlStyle: string
    title: string
}

type ExistingDiv = {
    attrs: Record<string, any>
    pos: number
}

type StyleOption = {
    label: string
    value: string
}

// ─── Helpers ─────────────────────────────────────────────────

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

function toNullable(val: string): string | null {
    return val.trim() || null
}

function getFormValues(el: HTMLElement): DivFormValues {
    const get = (name: string) =>
        el.querySelector<HTMLInputElement>(`input[name="${name}"]`)?.value.trim() ?? ''
    return {
        htmlClass: get('htmlClass'),
        id: get('id'),
        lang: get('lang'),
        htmlStyle: get('htmlStyle'),
        title: get('title')
    }
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

// ─── Dialog ──────────────────────────────────────────────────

function buildDialogContent(styleOptions: StyleOption[], current: DivFormValues): string {
    const options = styleOptions
        .map((o) => `<option value="${o.value}">${o.label}</option>`)
        .join('')

    const presetRow =
        styleOptions.length > 0
            ? `<div class="div-form-row">
                <label>Class preset</label>
                <select class="div-class-preset">
                    <option value="">— Select —</option>
                    ${options}
                </select>
            </div>`
            : ''

    const field = (label: string, name: string, value: string) =>
        `<div class="div-form-row">
            <label>${label}</label>
            <input type="text" name="${name}" value="${value}" />
        </div>`

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
            ${field('Class', 'htmlClass', current.htmlClass)}
            ${field('ID', 'id', current.id)}
            ${field('Language', 'lang', current.lang)}
            ${field('Style', 'htmlStyle', current.htmlStyle)}
            ${field('Advisory Title', 'title', current.title)}
        </div>`
}

function openDivDialog(editor: TiptapEditor, existing: ExistingDiv | null): void {
    const styleOptions = getDivStyleOptions(editor)
    const current: DivFormValues = {
        htmlClass: existing?.attrs.htmlClass ?? '',
        id: existing?.attrs.id ?? '',
        lang: existing?.attrs.lang ?? '',
        htmlStyle: existing?.attrs.htmlStyle ?? '',
        title: existing?.attrs.title ?? ''
    }

    const dialog = new Dialog(existing ? 'Edit Div' : 'Insert Div', { draggable: true })
    dialog.setContent(buildDialogContent(styleOptions, current))

    dialog
        .addButton({
            label: existing ? 'Update' : 'Insert',
            variant: 'primary',
            onClick: (d) => {
                const values = getFormValues(d.element)
                const attrs = {
                    htmlClass: toNullable(values.htmlClass),
                    id: toNullable(values.id),
                    lang: toNullable(values.lang),
                    htmlStyle: toNullable(values.htmlStyle),
                    title: toNullable(values.title)
                }

                if (existing) {
                    editor.tiptap.view.dispatch(
                        editor.tiptap.state.tr.setNodeMarkup(existing.pos, undefined, {
                            ...existing.attrs,
                            ...attrs
                        })
                    )
                } else {
                    editor.tiptap
                        .chain()
                        .focus()
                        .wrapIn('div', attrs)
                        .setMeta('applyStyle', true)
                        .run()
                }

                d.close()
            }
        })
        .addButton({ label: 'Cancel', variant: 'secondary', onClick: (d) => d.close() })
        .open()

    const el = dialog.element
    const select = el.querySelector<HTMLSelectElement>('.div-class-preset')
    const classInput = el.querySelector<HTMLInputElement>('input[name="htmlClass"]')

    if (select && classInput) {
        select.addEventListener('change', () => {
            if (select.value) classInput.value = select.value
        })
    }
}

function openInsertDivDialog(editor: TiptapEditor): void {
    openDivDialog(editor, null)
}

function openEditDivDialog(editor: TiptapEditor): void {
    const existing = getCurrentDivNode(editor)
    if (existing) openDivDialog(editor, existing)
}
