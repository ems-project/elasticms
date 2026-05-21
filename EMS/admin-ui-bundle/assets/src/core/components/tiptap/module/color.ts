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
    customColor: string | null
}

type ColorEditorState = Partial<Record<ColorType, ColorDropdownState>>

const editorState = new WeakMap<TiptapEditor, ColorEditorState>()

const PANEL_CSS = `
.tiptap-dropdown-content {
    padding: 6px;
    font-family: 'Source Sans Pro', 'Helvetica Neue', Helvetica, Arial, sans-serif;
}
.tiptap-dropdown-content * { box-sizing: border-box }
.tiptap-dropdown-content ul { list-style: none; margin: 0; padding: 0; }
.cc-auto li, .cc-more li {
    padding: 4px 8px; cursor: pointer; display: flex; align-items: center; gap: 6px;
    border-radius: 3px; font-size: 12px;
}
.cc-auto li:hover, .cc-more li:hover { background: rgba(0,0,0,.06); }
.cc-auto-icon {
    width: 14px; height: 14px; flex-shrink: 0; border: 1px solid #bbb;
    background: linear-gradient(
        to bottom right, #fff 0%, #fff calc(50% - 1px),
        #d44 calc(50% - 1px), #d44 calc(50% + 1px),
        #fff calc(50% + 1px), #fff 100%
    );
}
.cc-grid { display: flex; flex-wrap: wrap; gap: 3px; padding: 4px 0; }
.cc-grid li {
    width: 24px; height: 24px; border-radius: 2px; cursor: pointer;
    border: 1px solid rgba(0,0,0,.12); transition: transform .1s;
}
.cc-grid li:hover { transform: scale(1.2); border-color: #555; z-index: 1; position: relative; }
.cc-divider { height: 1px; background: #eee; margin: 4px 0; }
.cc-label { font-size: 10px; color: #888; padding: 2px 4px; text-transform: uppercase; letter-spacing: .03em; }
`

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
        <ul class="cc-auto">
            <li data-name="auto">
                <span class="cc-auto-icon"></span>${editor.trans('color_auto')}
            </li>
        </ul>
        ${predefined.length > 0 ? `<ul class="cc-grid">${buildColorSwatches(predefined)}</ul>` : ''}
        <div id="cc-custom-section"></div>
        <div id="cc-doc-section"></div>
        <div class="cc-divider"></div>
        <ul class="cc-more">
            <li data-name="more">${editor.trans('color_more')}</li>
        </ul>
    `
}

function refreshDynamicSections(
    root: HTMLElement,
    editor: TiptapEditor,
    type: ColorType,
    customColor: string | null
) {
    const docColors = getDocumentColors(editor, type)

    const customSection = root.querySelector<HTMLElement>('#cc-custom-section')!
    customSection.innerHTML = customColor
        ? `<div class="cc-divider"></div><ul class="cc-grid">${buildColorSwatches([customColor])}</ul>`
        : ''

    const docSection = root.querySelector<HTMLElement>('#cc-doc-section')!
    docSection.innerHTML =
        docColors.length > 0
            ? `<div class="cc-divider"></div><div class="cc-label">${editor.trans('color_in_doc')}</div><ul class="cc-grid">${buildColorSwatches(docColors)}</ul>`
            : ''
}

function openMoreColorsDialog(
    editor: TiptapEditor,
    type: ColorType,
    onPick: (color: string) => void
) {
    const dialog = editor.createDialog(titleKeyMap[type])

    const input = editor.docParent.createElement('input') as HTMLInputElement
    input.type = 'color'
    input.style.cssText = 'width:100%;height:48px;border:none;cursor:pointer;display:block;'

    const wrap = editor.docParent.createElement('div')
    wrap.style.cssText = 'padding:8px;min-width:200px;'
    wrap.appendChild(input)

    dialog.setContent(wrap)
    dialog
        .addButton({
            label: editor.trans('button_ok'),
            variant: 'primary',
            onClick: (d) => {
                d.close()
                onPick(input.value)
            }
        })
        .addButton({
            label: editor.trans('button_cancel'),
            variant: 'secondary',
            onClick: (d) => d.close()
        })
        .open()
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
        css: PANEL_CSS,
        buttonLabel: editor.trans(titleKeyMap[type]),
        buttonTooltip: titleKeyMap[type],
        icon: iconMap[type],
        buildBody: () => buildBody(editor),
        onItemClick(name) {
            if (name === 'auto') {
                applyColor(editor, type, null)
            } else if (name === 'more') {
                openMoreColorsDialog(editor, type, (color) => {
                    customColor = color
                    dropdown.show()
                })
            } else {
                applyColor(editor, type, name)
            }
        },
        onOpen(root) {
            refreshDynamicSections(root, editor, type, customColor)
        }
    })

    state[type] = { dropdown, customColor: null }

    return dropdown.element
}

function destroyColorDropdown(editor: TiptapEditor, type: ColorType) {
    const state = editorState.get(editor)
    if (!state) return
    state[type]?.dropdown.destroy()
    delete state[type]
}
