import IconTextColor from '@tabler/icons/outline/letter-case.svg?raw'
import IconHighlight from '@tabler/icons/outline/highlight.svg?raw'
import { TextStyle, Color } from '@tiptap/extension-text-style'
import { Extension } from '@tiptap/core'
import { TiptapModule } from '../types.ts'
import { TiptapEditor } from '../editor.ts'
import { createDropdown, Dropdown } from '../ui/dropdown.ts'
import { DialogColor } from '../ui/dialogColor.ts'

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
                create: (editor) => createColorDropdown(editor, 'font', editor.trans('color_font'), IconTextColor),
                destroy: (editor) => destroyColorDropdown(editor, 'font')
            },
            {
                name: 'BackgroundColor',
                order: 11,
                create: (editor) => createColorDropdown(editor, 'background', editor.trans('color_background'), IconHighlight),
                destroy: (editor) => destroyColorDropdown(editor, 'background')
            }
        ]
    }
}

type ColorType = 'font' | 'background'
const dropdowns = new WeakMap<TiptapEditor, Partial<Record<ColorType, Dropdown>>>()

function applyColor(editor: TiptapEditor, type: ColorType, color: string | null) {
    if (type === 'font') {
        color
            ? editor.tiptap.chain().focus().setColor(color).run()
            : editor.tiptap.chain().focus().unsetColor().run()
    } else {
        color
            ? (editor.tiptap.chain().focus() as any).setBackgroundColor(color).run()
            : (editor.tiptap.chain().focus() as any).unsetBackgroundColor().run()
    }
}

function getActiveColor(editor: TiptapEditor, type: ColorType): string | null {
    const attr = type === 'font' ? 'color' : 'backgroundColor'
    return editor.tiptap.getAttributes('textStyle')[attr] ?? null
}

function getDocumentColors(editor: TiptapEditor, type: ColorType): string[] {
    const attr = type === 'font' ? 'color' : 'backgroundColor'
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
    return `
        <div class="tiptap-color-dropdown">
            <ul class="tiptap-color-auto-option">
                <li data-name="auto">
                    <span class="tiptap-color-auto-icon"></span>${editor.trans('color_auto')}
                </li>
            </ul>
            <div class="tiptap-color-custom-section"></div>
            <div class="tiptap-color-doc-section"></div>
            <div class="tiptap-color-divider"></div>
            <button type="button" class="tiptap-color-more-btn" data-keep-open-on-blur>${editor.trans('color_more')}</button>
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
            ? `<div class="tiptap-color-divider"></div><ul class="tiptap-color-grid">${buildColorSwatches(docColors)}</ul>`
            : ''
}

function createColorDropdown(
    editor: TiptapEditor,
    type: ColorType,
    tooltip: string,
    icon: string
): HTMLElement {
    let customColor: string | null = null

    const dropdown = createDropdown(editor, {
        prefix: `colors-${type}`,
        action: type,
        buttonTooltip: tooltip,
        icon,
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
                    new DialogColor({
                        editor: editor,
                        initial: customColor,
                        onSelect: (color) => {
                            customColor = color
                            applyColor(editor, type, color)
                        },
                    }).open()
                }
            }
        }
    })

    const state = dropdowns.get(editor) ?? {}
    state[type] = dropdown
    dropdowns.set(editor, state)

    return dropdown.element
}

function destroyColorDropdown(editor: TiptapEditor, type: ColorType) {
    dropdowns.get(editor)?.[type]?.destroy()
}