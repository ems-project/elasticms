import IconOmega from '@tabler/icons/outline/omega.svg?raw'
import { TiptapModule } from '../types.ts'
import { TiptapEditor } from '../editor.ts'
import { escapeHtml } from '../helper.ts'

export const specialCharModule: TiptapModule = {
    isEnabled: (wysiwygProfile) => wysiwygProfile.hasPlugin('specialchar'),
    extensions: [],
    toolbar: {
        group: 'insert',
        items: [
            {
                name: 'SpecialChar',
                icon: IconOmega,
                tooltip: 'special_characters',
                order: 4,
                command: (e) => openSpecialCharDialog(e),
                isActive: () => false
            }
        ]
    }
}

const PLACEHOLDER = '\u00a0'

const STYLES = `
    <style>
        .sc-wrap { display: flex; gap: 12px; align-items: flex-start; }
        .sc-grid {
            display: flex; flex-wrap: wrap; gap: 2px;
            width: 390px; max-height: 450px; overflow-y: auto;
        }
        .sc-btn {
            width: 20px; height: 24px; font-size: 12px; line-height: 1;
            cursor: pointer; border: 1px solid transparent; border-radius: 3px;
            background: none; padding: 0;
            display: flex; align-items: center; justify-content: center;
        }
        .sc-btn:hover { border-color: #aaa; background: #f0f0f0; }
        .sc-preview-pane { display: flex; flex-direction: column; gap: 4px; flex-shrink: 0; }
        .sc-preview-box {
            border: 1px solid #eee; background: #f9f9f9; box-sizing: border-box;
            display: flex; align-items: center; justify-content: center;
        }
        .sc-preview-box--large { font-size: 28px; height: 50px; width: 70px; }
        .sc-preview-box--small { font-size: 11px; height: 22px; width: 70px; overflow: hidden; }
    </style>
`

function decodeEntity(s: string): string {
    const el = document.createElement('textarea')
    el.innerHTML = s
    return el.value
}

function buildContent(chars: string[]): string {
    const buttons = chars
        .map((raw) => {
            const char = decodeEntity(raw)
            const esc = escapeHtml(char)
            return `<button type="button" class="sc-btn" data-char="${esc}">${esc}</button>`
        })
        .join('')

    return `
        ${STYLES}
        <div class="sc-wrap">
            <div class="sc-grid">${buttons}</div>
            <div class="sc-preview-pane">
                <div class="sc-preview-box sc-preview-box--large">&nbsp;</div>
                <div class="sc-preview-box sc-preview-box--small">&nbsp;</div>
            </div>
        </div>
    `
}

function getCharFrom(ev: Event): string | null {
    return (ev.target as HTMLElement).closest<HTMLButtonElement>('.sc-btn')?.dataset.char ?? null
}

function bindPreview(grid: HTMLElement, large: HTMLElement, small: HTMLElement) {
    const set = (char: string | null) => {
        large.textContent = char ?? PLACEHOLDER
        small.textContent = char ?? PLACEHOLDER
    }
    const onEnter = (ev: Event) => {
        const char = getCharFrom(ev)
        if (char) set(char)
    }
    const onLeave = () => set(null)

    grid.addEventListener('mouseover', onEnter)
    grid.addEventListener('focusin', onEnter)
    grid.addEventListener('mouseleave', onLeave)
    grid.addEventListener('focusout', onLeave)
}

function openSpecialCharDialog(e: TiptapEditor) {
    const dialog = e.createDialog('special_characters')
    dialog.setContent(buildContent(e.profile.config.specialChars))
    dialog
        .addButton({
            label: e.trans('button_cancel'),
            variant: 'secondary',
            onClick: (d) => d.close()
        })
        .open()

    const el = dialog.element
    const grid = el.querySelector<HTMLElement>('.sc-grid')!
    const large = el.querySelector<HTMLElement>('.sc-preview-box--large')!
    const small = el.querySelector<HTMLElement>('.sc-preview-box--small')!

    bindPreview(grid, large, small)

    grid.addEventListener('click', (ev) => {
        const char = getCharFrom(ev)
        if (!char) return
        e.tiptap.chain().focus().insertContent({ type: 'text', text: char }).run()
        dialog.close()
    })
}
