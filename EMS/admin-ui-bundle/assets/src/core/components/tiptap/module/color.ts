import IconTextColor from '@tabler/icons/outline/letter-case.svg?raw'
import IconHighlight from '@tabler/icons/outline/highlight.svg?raw'
import { TextStyle, Color } from '@tiptap/extension-text-style'
import { Extension } from '@tiptap/core'
import { TiptapModule } from '../types.ts'
import { TiptapEditor } from '../editor.ts'
import { TranslationKey } from '../translation/en.ts'
import { createDropdown, Dropdown } from '../ui/dropdown.ts'
import { Dialog } from '../../dialog.ts'
import { getWebSafePalette } from '../ui/webSafePalette.ts'

const BackgroundColor = Extension.create({
    name: 'backgroundColor',
    addGlobalAttributes() {
        return [
            {
                types: ['textStyle'],
                attributes: {
                    backgroundColor: {
                        default: null,
                        parseHTML: (el) => (el as HTMLElement).style.backgroundColor || null,
                        renderHTML: (attrs) => {
                            if (!attrs.backgroundColor) return {}
                            return { style: `background-color: ${attrs.backgroundColor}` }
                        }
                    }
                }
            }
        ]
    },
    addCommands() {
        return {
            setBackgroundColor:
                (color: string) =>
                ({ chain }) =>
                    chain().setMark('textStyle', { backgroundColor: color }).run(),
            unsetBackgroundColor:
                () =>
                ({ chain }) =>
                    chain()
                        .setMark('textStyle', { backgroundColor: null })
                        .removeEmptyTextStyle()
                        .run()
        }
    }
})

export const colorModule: TiptapModule = {
    extensions: [TextStyle, Color, BackgroundColor],
    toolbar: {
        group: 'colors',
        items: [
            {
                name: 'TextColor',
                order: 10,
                create: (editor) => createColorDropdown(editor, 'font'),
                destroy: (editor) => destroyColorDropdown(editor, 'font')
            },
            {
                name: 'BackgroundColor',
                order: 11,
                create: (editor) => createColorDropdown(editor, 'background'),
                destroy: (editor) => destroyColorDropdown(editor, 'background')
            }
        ]
    }
}

type ColorType = 'font' | 'background'

type ColorDropdownState = {
    dropdown: Dropdown
}

type ColorEditorState = Partial<Record<ColorType, ColorDropdownState>>

const editorState = new WeakMap<TiptapEditor, ColorEditorState>()

const titleKeyMap: Record<ColorType, TranslationKey> = {
    font: 'font_color',
    background: 'background_color'
}

const iconMap: Record<ColorType, string> = {
    font: IconTextColor,
    background: IconHighlight
}

const attrMap: Record<ColorType, string> = {
    font: 'color',
    background: 'backgroundColor'
}

const applyMap: Record<
    ColorType,
    { set: (e: TiptapEditor, c: string) => void; unset: (e: TiptapEditor) => void }
> = {
    font: {
        set: (e, c) => e.tiptap.chain().focus().setColor(c).run(),
        unset: (e) => e.tiptap.chain().focus().unsetColor().run()
    },
    background: {
        set: (e, c) => (e.tiptap.chain().focus() as any).setBackgroundColor(c).run(),
        unset: (e) => (e.tiptap.chain().focus() as any).unsetBackgroundColor().run()
    }
}

function applyColor(editor: TiptapEditor, type: ColorType, color: string | null) {
    if (color) {
        applyMap[type].set(editor, color)
    } else {
        applyMap[type].unset(editor)
    }
}

function getActiveColor(editor: TiptapEditor, type: ColorType): string | null {
    const attr = attrMap[type]
    return editor.tiptap.getAttributes('textStyle')[attr] ?? null
}

function getDocumentColors(editor: TiptapEditor, type: ColorType): string[] {
    const attr = attrMap[type]
    const seen = new Set<string>()
    editor.tiptap.state.doc.descendants((node) => {
        node.marks.forEach((mark) => {
            if (mark.type.name === 'textStyle') {
                const val = mark.attrs[attr]
                if (val) seen.add(val)
            }
        })
    })
    return [...seen]
}

function buildColorSwatches(colors: string[]): string {
    return colors
        .map((c) => `<li data-name="${c}" style="background:${c}" title="${c}"></li>`)
        .join('')
}



function buildBody(editor: TiptapEditor): string {
    const predefined: string[] = []
    return `
        <div class="tiptap-color-dropdown">
            <ul class="tiptap-color-auto-option">
                <li data-name="auto">
                    <span class="tiptap-color-auto-icon"></span>${editor.trans('color_auto')}
                </li>
            </ul>
            ${predefined.length > 0 ? `<ul class="tiptap-color-grid">${buildColorSwatches(predefined)}</ul>` : ''}
            <div class="tiptap-color-custom-section"></div>
            <div class="tiptap-color-doc-section"></div>
            <div class="tiptap-color-divider"></div>
            <button type="button" class="tiptap-color-more-btn" data-keep-open-on-blur>${editor.trans('color_more')}</button>
        </div>
    `
}

const PALETTE = getWebSafePalette()

function buildColorPickerContent(
    initial: string | null,
): { root: HTMLElement; getValue: () => string } {
    const current = initial ?? '#000000'
    let selected = current

    const root = document.createElement('div')
    root.className = 'tiptap-color-picker'

    const preview = document.createElement('div')
    preview.className = 'tiptap-color-preview'

    const previewBox = document.createElement('div')
    previewBox.className = 'tiptap-color-preview-box'
    previewBox.style.background = current

    const previewHex = document.createElement('span')
    previewHex.className = 'tiptap-color-preview-hex'
    previewHex.textContent = current.toUpperCase()

    preview.append(previewBox, previewHex)

    const grid = document.createElement('div')
    grid.className = 'tiptap-color-grid-picker'

    const hexInput = document.createElement('input')

    const setSelected = (color: string) => {
        selected = color
        previewBox.style.background = color
        previewHex.textContent = color.toUpperCase()
        hexInput.value = color.replace('#', '').toUpperCase()
        grid.querySelectorAll<HTMLElement>('.tiptap-color-swatch').forEach((s) =>
            s.classList.toggle('is-selected', s.dataset.color === color)
        )
    }

    PALETTE.forEach(({ hex }) => {
        const swatch = document.createElement('span')
        swatch.className = 'tiptap-color-swatch'
        if (hex === current) swatch.classList.add('is-selected')
        swatch.dataset.color = hex
        swatch.style.background = hex
        swatch.title = hex
        swatch.addEventListener('click', () => setSelected(hex))
        grid.appendChild(swatch)
    })

    const hexRow = document.createElement('div')
    hexRow.className = 'tiptap-color-hex-row'

    const hexLabel = document.createElement('label')
    hexLabel.textContent = '#'

    hexInput.type = 'text'
    hexInput.className = 'tiptap-color-hex-input'
    hexInput.value = current.replace('#', '').toUpperCase()
    hexInput.maxLength = 6
    hexInput.addEventListener('input', () => {
        const val = `#${hexInput.value}`
        if (/^#[0-9a-fA-F]{6}$/.test(val)) setSelected(val)
    })

    hexRow.append(hexLabel, hexInput)
    root.append(preview, grid, hexRow)

    return { root, getValue: () => selected }
}

function refreshDynamicSections(
    root: HTMLElement,
    editor: TiptapEditor,
    type: ColorType,
    customColor: string | null
) {
    const docColors = getDocumentColors(editor, type)

    const customSection = root.querySelector<HTMLElement>('.tiptap-color-custom-section')!
    customSection.innerHTML = customColor
        ? `<div class="tiptap-color-divider"></div><ul class="tiptap-color-grid">${buildColorSwatches([customColor])}</ul>`
        : ''

    const docSection = root.querySelector<HTMLElement>('.tiptap-color-doc-section')!
    docSection.innerHTML =
        docColors.length > 0
            ? `<div class="tiptap-color-divider"></div><div class="tiptap-color-label">${editor.trans('color_in_doc')}</div><ul class="tiptap-color-grid">${buildColorSwatches(docColors)}</ul>`
            : ''
}

function createColorDropdown(editor: TiptapEditor, type: ColorType): HTMLElement {
    let state = editorState.get(editor)
    if (!state) {
        state = {}
        editorState.set(editor, state)
    }

    let customColor: string | null = null

    function openColorPickerModal(
        editor: TiptapEditor,
        type: ColorType,
        initial: string | null,
        onConfirm: (color: string) => void
    ) {
        const { root, getValue } = buildColorPickerContent(initial)

        const dialog = new Dialog(editor.trans(titleKeyMap[type]), {
            closeLabel: editor.trans('modal_close'),
        })

        dialog
            .setContent(root)
            .addButton({
                label: editor.trans('button_close'),
                variant: 'secondary',
                onClick: (d) => d.close(),
            })
            .addButton({
                label: editor.trans('button_ok'),
                variant: 'primary',
                onClick: (d) => {
                    onConfirm(getValue())
                    d.close()
                },
            })
            .open()
    }

    const dropdown = createDropdown(editor, {
        prefix: `colors-${type}`,
        buttonLabel: editor.trans(titleKeyMap[type]),
        buttonTooltip: titleKeyMap[type],
        icon: iconMap[type],
        buildBody: () => buildBody(editor),
        onItemClick(name) {
            const color = name === 'auto' ? null : name
            if (color === getActiveColor(editor, type)) return
            applyColor(editor, type, color)
        },
        onOpen(root) {
            customColor = getActiveColor(editor, type)
            refreshDynamicSections(root, editor, type, customColor)

            const moreBtn = root.querySelector<HTMLButtonElement>('.tiptap-color-more-btn')
            if (moreBtn) {
                moreBtn.onclick = () => {
                    dropdown.hide()

                    openColorPickerModal(editor, type, customColor, (color) => {
                        customColor = color
                        applyColor(editor, type, color)

                    })
                }
            }

            // const input = root.querySelector<HTMLInputElement>('.tiptap-color-input')
            // if (!input) return
            // if (customColor) input.value = customColor
            // input.oninput = () => {
            //     dropdown.setKeepOpenOnBlur(true)
            //     const newColor = input.value
            //     if (newColor === customColor) return
            //     customColor = newColor
            //     applyColor(editor, type, customColor)
            // }
            // input.onchange = () => {
            //     dropdown.focus()
            // }
            // input.onclick = () => {
            //     dropdown.setKeepOpenOnBlur(false);
            // }
            //
            // const submit = root.querySelector<HTMLButtonElement>('.tiptap-color-submit')
            // if (submit) {
            //     submit.onclick = () => dropdown.hide()
            // }
        }
    })

    state[type] = { dropdown }

    return dropdown.element
}

function destroyColorDropdown(editor: TiptapEditor, type: ColorType) {
    const state = editorState.get(editor)
    if (!state) return
    state[type]?.dropdown.destroy()
    delete state[type]
}
