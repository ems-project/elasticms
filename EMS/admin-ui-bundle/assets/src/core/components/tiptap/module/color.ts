import IconTextColor from '@tabler/icons/outline/letter-case.svg?raw'
import IconHighlight from '@tabler/icons/outline/highlight.svg?raw'
import { TextStyle, Color } from '@tiptap/extension-text-style'
import { Extension } from '@tiptap/core'
import { TiptapModule } from '../types.ts'
import { TiptapEditor } from '../editor.ts'
import { TranslationKey } from '../translation/en.ts'
import { createDropdown, Dropdown } from '../ui/dropdown.ts'

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
            <label class="tiptap-color-more-option" data-keep-open-on-blur>
                <input type="color" class="tiptap-color-input" >
                <span>${editor.trans('color_more')}</span>
            </label>
        </div>
    `
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

    const dropdown = createDropdown(editor, {
        prefix: `colors-${type}`,
        buttonLabel: editor.trans(titleKeyMap[type]),
        buttonTooltip: titleKeyMap[type],
        icon: iconMap[type],
        buildBody: () => buildBody(editor),
        onItemClick(name) {
            if (name === 'auto') {
                applyColor(editor, type, null)
            } else {
                applyColor(editor, type, name)
            }
        },
        onOpen(root) {
            refreshDynamicSections(root, editor, type, customColor)

            const input = root.querySelector<HTMLInputElement>('.tiptap-color-input')
            if (!input) return
            if (customColor) input.value = customColor
            input.oninput = () => {
                customColor = input.value
                applyColor(editor, type, customColor)
                refreshDynamicSections(root, editor, type, customColor)
            }
            input.onchange = () => {
                dropdown.focus()
            }
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
