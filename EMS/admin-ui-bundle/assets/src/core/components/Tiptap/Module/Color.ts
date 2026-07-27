import IconTextColor from '@tabler/icons/outline/letter-case.svg?raw'
import IconHighlight from '@tabler/icons/outline/highlight.svg?raw'
import { TextStyle, Color } from '@tiptap/extension-text-style'
import { Extension } from '@tiptap/core'
import { TiptapModule } from '../Types.ts'
import { TiptapEditor } from '../Editor.ts'
import { createDropdown, Dropdown } from '../UI/Dropdown.ts'
import { DialogColor } from '../UI/DialogColor.ts'
import { TranslationKey } from '../Translation/EN.ts'

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

export const ColorModule: TiptapModule = {
    extensions: [TextStyle, Color, BackgroundColor],
    toolbar: {
        group: 'colors',
        items: [
            {
                name: 'TextColor',
                order: 10,
                create: (editor) =>
                    createColorDropdown(
                        editor,
                        'font',
                        'TextColor',
                        editor.trans('color_font'),
                        IconTextColor
                    ),
                destroy: (editor) => destroyColorDropdown(editor, 'font')
            },
            {
                name: 'BackgroundColor',
                order: 11,
                create: (editor) =>
                    createColorDropdown(
                        editor,
                        'background',
                        'BackgroundColor',
                        editor.trans('color_background'),
                        IconHighlight
                    ),
                destroy: (editor) => destroyColorDropdown(editor, 'background')
            }
        ]
    }
}

type ColorType = 'font' | 'background'
const dropdowns = new WeakMap<TiptapEditor, Partial<Record<ColorType, Dropdown>>>()
const recentColors = new WeakMap<TiptapEditor, Partial<Record<ColorType, string[]>>>()

const PREDEFINED_COLORS = [
    '#1ABC9C',
    '#2ECC71',
    '#3498DB',
    '#9B59B6',
    '#4E5F70',
    '#F1C40F',
    '#16A085',
    '#27AE60',
    '#2980B9',
    '#8E44AD',
    '#2C3E50',
    '#F39C12',
    '#E67E22',
    '#E74C3C',
    '#ECF0F1',
    '#95A5A6',
    '#DDDDDD',
    '#FFFFFF',
    '#D35400',
    '#C0392B',
    '#BDC3C7',
    '#7F8C8D',
    '#999999',
    '#000000'
]
const PREDEFINED_COLOR_KEYS: Record<string, TranslationKey> = {
    '#1ABC9C': 'color_strong_cyan',
    '#2ECC71': 'color_emerald',
    '#3498DB': 'color_bright_blue',
    '#9B59B6': 'color_amethyst',
    '#4E5F70': 'color_grayish_blue',
    '#F1C40F': 'color_vivid_yellow',
    '#16A085': 'color_dark_cyan',
    '#27AE60': 'color_dark_emerald',
    '#2980B9': 'color_strong_blue',
    '#8E44AD': 'color_dark_violet',
    '#2C3E50': 'color_desaturated_blue',
    '#F39C12': 'color_orange',
    '#E67E22': 'color_carrot',
    '#E74C3C': 'color_pale_red',
    '#ECF0F1': 'color_bright_silver',
    '#95A5A6': 'color_light_grayish_cyan',
    '#DDDDDD': 'color_light_gray',
    '#FFFFFF': 'color_white',
    '#D35400': 'color_pumpkin',
    '#C0392B': 'color_strong_red',
    '#BDC3C7': 'color_silver',
    '#7F8C8D': 'color_grayish_cyan',
    '#999999': 'color_dark_gray',
    '#000000': 'color_black'
}

function addRecentColor(editor: TiptapEditor, type: ColorType, color: string) {
    const state = recentColors.get(editor) ?? {}
    const list = state[type] ?? []
    state[type] = [color, ...list.filter((c) => c !== color)].slice(0, 12)
    recentColors.set(editor, state)
}

function getRecentColors(editor: TiptapEditor, type: ColorType): string[] {
    return recentColors.get(editor)?.[type] ?? []
}

function applyColor(editor: TiptapEditor, type: ColorType, color: string | null) {
    if (color) addRecentColor(editor, type, color)
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

function buildColorSwatches(colors: string[], editor: TiptapEditor): string {
    return colors
        .map((c) => {
            const key = PREDEFINED_COLOR_KEYS[c.toUpperCase()]
            const label = key ? editor.trans(key) : c
            return `<li data-name="${c}" style="background:${c}" title="${label}"></li>`
        })
        .join('')
}

function buildBody(editor: TiptapEditor): string {
    return `
        <div class="tiptap-color-dropdown">
            <button type="button" class="tiptap-color-row-btn" data-name="auto">
                <span class="tiptap-color-auto-icon"></span>
                <span>${editor.trans('color_auto')}</span>
            </button>
            <div class="tiptap-color-predefined-section"></div>
            <div class="tiptap-color-divider"></div>
            <div class="tiptap-color-active-section"></div>
            <button type="button" class="tiptap-color-row-btn tiptap-color-more-btn" data-name="more">${editor.trans('color_more')}</button>
        </div>
    `
}

function getPredefinedColors(editor: TiptapEditor): string[] {
    const config = editor.profile.config.colorButton_colors
    if (!config) return PREDEFINED_COLORS
    if (Array.isArray(config)) return config
    return config.split(',').map((c) => {
        const trimmed = c.trim()
        return trimmed.startsWith('#') ? trimmed : `#${trimmed}`
    })
}

function refreshDynamicSections(root: HTMLElement, editor: TiptapEditor, type: ColorType) {
    const predefinedColors = getPredefinedColors(editor)
    root.querySelector('.tiptap-color-predefined-section')!.innerHTML =
        `<ul class="tiptap-color-grid">${buildColorSwatches(predefinedColors, editor)}</ul>`

    const docColors = getDocumentColors(editor, type)
    const recent = getRecentColors(editor, type)
    const activeColors = [...new Set([...recent, ...docColors])]
    const activeSection = root.querySelector('.tiptap-color-active-section')!
    activeSection.innerHTML =
        activeColors.length > 0
            ? `<ul class="tiptap-color-grid">${buildColorSwatches(activeColors, editor)}</ul>`
            : ''
}

function createColorDropdown(
    editor: TiptapEditor,
    type: ColorType,
    name: string,
    tooltip: string,
    icon: string
): HTMLElement {
    let activeColor: string | null = null

    const dropdown = createDropdown(editor, {
        prefix: `colors-${type}`,
        action: name,
        buttonTooltip: tooltip,
        icon,
        buildBody: () => buildBody(editor),
        onItemClick(name) {
            if (name === 'more') {
                new DialogColor({
                    editor: editor,
                    initial: activeColor,
                    onSelect: (color) => {
                        applyColor(editor, type, color)
                    }
                }).open()
                return
            }
            const color = name === 'auto' ? null : name
            applyColor(editor, type, color)
        },
        onOpen(root) {
            activeColor = getActiveColor(editor, type)
            refreshDynamicSections(root, editor, type)
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
