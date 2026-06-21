import { Dialog } from './../../dialog'
import '../../../../../css/core/components/tiptap/_dialog.scss'
import { TiptapEditor } from '../editor.ts'

interface PaletteColor {
    hex: string
    isLight: boolean
}

function getWebSafePalette(): PaletteColor[] {
    const steps = ['00', '33', '66', '99', 'cc', 'ff']
    const list: PaletteColor[] = []
    const redBlocks = [
        ['00', '33', '66'],
        ['99', 'cc', 'ff']
    ]

    redBlocks.forEach((redSet) => {
        steps.forEach((blue) => {
            redSet.forEach((red) => {
                steps.forEach((green) => {
                    const r = parseInt(red, 16)
                    const g = parseInt(green, 16)
                    const b = parseInt(blue, 16)
                    list.push({
                        hex: `#${red}${green}${blue}`,
                        isLight: r * 0.299 + g * 0.587 + b * 0.114 > 128
                    })
                })
            })
        })
    })

    const graySteps = [
        '00',
        '00',
        '11',
        '22',
        '33',
        '44',
        '55',
        '66',
        '77',
        '88',
        '99',
        'aa',
        'bb',
        'cc',
        'dd',
        'ee',
        'ff',
        'ff'
    ]
    graySteps.forEach((step) => {
        const value = parseInt(step, 16)
        list.push({
            hex: `#${step}${step}${step}`,
            isLight: value > 128
        })
    })

    return list
}

interface DialogColorOptions {
    editor: TiptapEditor
    initial?: string | null
    onSelect: (color: string) => void
}

export class DialogColor {
    private dialog: Dialog

    constructor({ editor, initial, onSelect }: DialogColorOptions) {
        this.dialog = editor.createDialog('color_select', 'tiptap-dialog-color')

        const grid = document.createElement('div')
        grid.className = 'tiptap-dialog-color-grid'

        const sidebar = document.createElement('div')
        sidebar.className = 'tiptap-dialog-color-sidebar'

        const activeLabel = document.createElement('span')
        activeLabel.className = 'tiptap-dialog-color-label'
        activeLabel.textContent = editor.trans('color_active')

        const activeBox = document.createElement('div')
        activeBox.className = 'tiptap-dialog-color-active-box'

        const selectedLabel = document.createElement('span')
        selectedLabel.className = 'tiptap-dialog-color-label'
        selectedLabel.textContent = editor.trans('color_selected')

        const selectedBox = document.createElement('div')
        selectedBox.className = 'tiptap-dialog-color-selected-box'

        const hexInput = document.createElement('input')
        hexInput.type = 'text'
        hexInput.className = 'tiptap-dialog-color-hex-input'
        hexInput.maxLength = 7
        hexInput.value = initial ? initial.toUpperCase() : ''

        const clearBtn = document.createElement('button')
        clearBtn.type = 'button'
        clearBtn.className = 'tiptap-dialog-color-clear-btn'
        clearBtn.textContent = editor.trans('button_erase')

        const activeHex = document.createElement('span')
        activeHex.className = 'tiptap-dialog-color-label'
        if (initial) activeHex.textContent = initial.toUpperCase()

        sidebar.append(
            activeLabel,
            activeBox,
            activeHex,
            selectedLabel,
            selectedBox,
            hexInput,
            clearBtn
        )

        let selected: string | null = initial ?? null

        const setSelected = (color: string | null) => {
            selected = color
            selectedBox.style.background = color ?? ''
            hexInput.value = color ? color.toUpperCase() : ''
            if (color) setActive(color)
        }

        const setActive = (color: string | null) => {
            activeBox.style.background = color ?? ''
            activeHex.textContent = color ? color.toUpperCase() : ''
        }

        if (initial) {
            setSelected(initial)
            setActive(initial)
        } else {
            setActive('#000000')
        }

        getWebSafePalette().forEach(({ hex }) => {
            const swatch = document.createElement('span')
            swatch.className = 'tiptap-dialog-color-swatch'
            swatch.tabIndex = 0
            swatch.dataset.color = hex
            swatch.style.background = hex
            swatch.title = hex
            swatch.addEventListener('mouseenter', () => setActive(hex))
            swatch.addEventListener('click', () => setSelected(hex))
            grid.appendChild(swatch)
        })

        hexInput.addEventListener('input', () => {
            if (!hexInput.value.startsWith('#'))
                hexInput.value = '#' + hexInput.value.replace('#', '')
            if (/^#[0-9a-fA-F]{6}$/.test(hexInput.value)) setSelected(hexInput.value)
        })

        clearBtn.addEventListener('click', () => setSelected(null))

        this.dialog.body.append(grid, sidebar)
        this.dialog
            .addButton({
                label: editor.trans('button_apply'),
                variant: 'primary',
                onClick: (d) => {
                    if (selected) onSelect(selected)
                    d.close()
                }
            })
            .addButton({
                label: editor.trans('button_close'),
                variant: 'secondary',
                onClick: (d) => d.close()
            })
    }

    open(): void {
        this.dialog.open()
        const firstSwatch = this.dialog.element.querySelector<HTMLElement>(
            '.tiptap-dialog-color-swatch'
        )
        firstSwatch?.focus()
    }

    onClose(callback: () => void): this {
        this.dialog.onClose(callback)
        return this
    }
}
