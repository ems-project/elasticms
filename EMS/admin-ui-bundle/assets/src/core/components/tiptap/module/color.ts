import IconTextColor from '@tabler/icons/outline/letter-case.svg?raw'
import IconHighlight from '@tabler/icons/outline/highlight.svg?raw'
import { TextStyle, Color } from '@tiptap/extension-text-style'
import { Extension } from '@tiptap/core'
import { TiptapModule } from '../types.ts'
import { TiptapEditor } from '../editor.ts'
import { TranslationKey } from '../translation/en.ts'

export const colorModule: TiptapModule = {
    extensions: [TextStyle, Color],
    toolbar: {
        group: 'colors',
        items: [
            {
                name: 'TextColor',
                icon: IconTextColor,
                tooltip: 'font_color',
                order: 10,
                command: (e) => openColorPanel(e, 'font'),
                isActive: () => false
            },
            {
                name: 'BackgroundColor',
                extensions: [getBackgroundExtension()],
                icon: IconHighlight,
                tooltip: 'background_color',
                order: 11,
                command: (e) => openColorPanel(e, 'background'),
                isActive: () => false
            }
        ]
    }
}

function getBackgroundExtension() {
    return Extension.create({
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
                            chain().setMark('textStyle', { backgroundColor: null }).removeEmptyTextStyle().run()
            }
        }
    });
}

type ColorType = 'font' | 'background'

const PANEL_STYLES = `
<style>
    .cc-panel {
        position: absolute; z-index: 9999;
        background: #fff; border: 1px solid #ccc; border-radius: 4px;
        box-shadow: 0 4px 12px rgba(0,0,0,.15);
        padding: 8px; min-width: 170px;
        display: flex; flex-direction: column; gap: 4px;
    }
    .cc-auto-btn {
        display: flex; align-items: center; gap: 6px;
        padding: 4px 6px; border: 1px solid transparent; border-radius: 3px;
        background: none; cursor: pointer; font-size: 12px; width: 100%;
        text-align: left;
    }
    .cc-auto-btn:hover { background: #f0f0f0; border-color: #ddd; }
    .cc-auto-swatch {
        width: 16px; height: 16px; border-radius: 2px; flex-shrink: 0;
        border: 1px solid #bbb; background: linear-gradient(
            to bottom right, #fff 0%, #fff calc(50% - 1px),
            #d44 calc(50% - 1px), #d44 calc(50% + 1px),
            #fff calc(50% + 1px), #fff 100%
        );
    }
    .cc-label { font-size: 10px; color: #888; padding: 2px 2px 0; text-transform: uppercase; letter-spacing: .03em; }
    .cc-grid { display: flex; flex-wrap: wrap; gap: 2px; padding: 2px 0; }
    .cc-swatch {
        width: 24px; height: 24px; border-radius: 2px; cursor: pointer;
        border: 1px solid rgba(0,0,0,.12); flex-shrink: 0; transition: transform .1s;
    }
    .cc-swatch:hover { transform: scale(1.2); border-color: #666; z-index: 1; position: relative; }
    .cc-divider { height: 1px; background: #eee; margin: 2px 0; }
    .cc-more-btn {
        font-size: 12px; background: none; border: 1px solid #ddd;
        border-radius: 3px; padding: 4px 8px; cursor: pointer; width: 100%;
        text-align: center; margin-top: 2px;
    }
    .cc-more-btn:hover { background: #f0f0f0; }
</style>
`

const panelState: Record<ColorType, { customColor: string | null }> = {
    font: { customColor: null },
    background: { customColor: null }
}

let activePanel: HTMLElement | null = null
let outsideClickHandler: ((ev: MouseEvent) => void) | null = null

function closeActivePanel() {
    activePanel?.remove()
    activePanel = null
    if (outsideClickHandler) {
        document.removeEventListener('click', outsideClickHandler, true)
        outsideClickHandler = null
    }
}

function getDocumentColors(e: TiptapEditor, type: ColorType): string[] {
    const attr = type === 'font' ? 'color' : 'backgroundColor'
    const seen = new Set<string>()
    e.tiptap.state.doc.descendants((node) => {
        node.marks.forEach((mark) => {
            if (mark.type.name === 'textStyle') {
                const val = mark.attrs[attr]
                if (val) seen.add(val)
            }
        })
    })
    return [...seen]
}

function findToolbarAnchor(e: TiptapEditor, name: string): HTMLElement | null {
    let el: Element | null = e.tiptap.view.dom
    for (let i = 0; i < 6; i++) {
        el = el?.parentElement ?? null
        if (!el) break
        const found = el.querySelector<HTMLElement>(`[data-item="${name}"]`)
        if (found) return found
    }
    return null
}

function buildAutoButton(label: string): HTMLButtonElement {
    const btn = document.createElement('button')
    btn.type = 'button'
    btn.className = 'cc-auto-btn'
    btn.dataset.auto = '1'
    btn.innerHTML = `<span class="cc-auto-swatch"></span>${label}`
    return btn
}

function buildSwatch(color: string): HTMLButtonElement {
    const btn = document.createElement('button')
    btn.type = 'button'
    btn.className = 'cc-swatch'
    btn.dataset.color = color
    btn.title = color
    btn.style.backgroundColor = color
    return btn
}

function buildSwatchGrid(colors: string[]): HTMLElement {
    const grid = document.createElement('div')
    grid.className = 'cc-grid'
    colors.forEach((c) => grid.appendChild(buildSwatch(c)))
    return grid
}

function buildLabel(text: string): HTMLElement {
    const el = document.createElement('div')
    el.className = 'cc-label'
    el.textContent = text
    return el
}

function buildDivider(): HTMLElement {
    const el = document.createElement('div')
    el.className = 'cc-divider'
    return el
}

function buildMoreButton(label: string): HTMLButtonElement {
    const btn = document.createElement('button')
    btn.type = 'button'
    btn.className = 'cc-more-btn'
    btn.dataset.more = '1'
    btn.textContent = label
    return btn
}

function buildPanel(e: TiptapEditor, type: ColorType): HTMLElement {
    const panel = document.createElement('div')
    panel.className = 'cc-panel'
    panel.innerHTML = PANEL_STYLES

    const predefined: string[] = ['FF0000']
    const docColors = getDocumentColors(e, type)
    const { customColor } = panelState[type]

    panel.appendChild(buildAutoButton(e.trans('color_auto')))

    if (predefined.length > 0) {
        panel.appendChild(buildSwatchGrid(predefined))
    }

    if (customColor) {
        panel.appendChild(buildDivider())
        panel.appendChild(buildSwatchGrid([customColor]))
    }

    if (docColors.length > 0) {
        panel.appendChild(buildDivider())
        panel.appendChild(buildLabel(e.trans('color_in_doc')))
        panel.appendChild(buildSwatchGrid(docColors))
    }

    panel.appendChild(buildDivider())
    panel.appendChild(buildMoreButton(e.trans('color_more')))

    return panel
}

const applyMap: Record<ColorType, { set: (e: TiptapEditor, c: string) => void; unset: (e: TiptapEditor) => void }> = {
    font: {
        set: (e, c) => e.tiptap.chain().focus().setColor(c).run(),
        unset: (e) => e.tiptap.chain().focus().unsetColor().run()
    },
    background: {
        set: (e, c) => e.tiptap.chain().focus().setBackgroundColor(c).run(),
        unset: (e) => e.tiptap.chain().focus().unsetBackgroundColor().run()
    }
}

function applyColor(e: TiptapEditor, type: ColorType, color: string | null) {
    if (color) {
        applyMap[type].set(e, color)
    } else {
        applyMap[type].unset(e)
    }
}

function openMoreColorsDialog(e: TiptapEditor, type: ColorType) {
    const titleKey: Record<ColorType, TranslationKey> = { font: 'font_color', background: 'background_color' }
    const dialog = e.createDialog(titleKey[type])

    const input = document.createElement('input')
    input.type = 'color'
    input.value = panelState[type].customColor ?? '#000000'
    input.style.cssText = 'width:100%;height:48px;border:none;cursor:pointer;display:block;'

    const wrap = document.createElement('div')
    wrap.style.cssText = 'padding:8px;min-width:200px;'
    wrap.appendChild(input)

    dialog.setContent(wrap)
    dialog
        .addButton({
            label: e.trans('button_ok'),
            variant: 'primary',
            onClick: (d) => {
                d.close()
                panelState[type].customColor = input.value
                openColorPanel(e, type)
            }
        })
        .addButton({
            label: e.trans('button_cancel'),
            variant: 'secondary',
            onClick: (d) => d.close()
        })
        .open()
}

const anchorNameMap: Record<ColorType, string> = {
    font: 'FontColor',
    background: 'BackgroundColor'
}

function positionPanel(panel: HTMLElement, anchor: HTMLElement) {
    const rect = anchor.getBoundingClientRect()
    panel.style.top = `${rect.bottom + window.scrollY + 2}px`
    panel.style.left = `${rect.left + window.scrollX}px`
}

function openColorPanel(e: TiptapEditor, type: ColorType) {
    if (activePanel) {
        closeActivePanel()
        return
    }

    const anchor = findToolbarAnchor(e, anchorNameMap[type])
    const panel = buildPanel(e, type)
    document.body.appendChild(panel)
    activePanel = panel

    if (anchor) positionPanel(panel, anchor)

    panel.addEventListener('click', (ev) => {
        const target = ev.target as HTMLElement

        if (target.closest('[data-auto]')) {
            applyColor(e, type, null)
            closeActivePanel()
            return
        }

        const swatch = target.closest<HTMLElement>('[data-color]')
        if (swatch?.dataset.color) {
            applyColor(e, type, swatch.dataset.color)
            closeActivePanel()
            return
        }

        if (target.closest('[data-more]')) {
            closeActivePanel()
            openMoreColorsDialog(e, type)
        }
    })

    outsideClickHandler = (ev: MouseEvent) => {
        if (activePanel && !activePanel.contains(ev.target as Node)) {
            closeActivePanel()
        }
    }
    setTimeout(() => document.addEventListener('click', outsideClickHandler!, true), 0)
}