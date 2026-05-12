import IconOmega from '@tabler/icons/outline/omega.svg?raw'
import { TiptapModule } from '../types.ts'
import { Dialog } from '../../dialog.ts'
import { TiptapEditor } from '../editor.ts'

export const specialCharModule: TiptapModule = {
    extensions: [],
    toolbarGroup: 'insert',
    toolbar: [
        {
            name: 'SpecialChar',
            icon: IconOmega,
            tooltip: 'Special Characters',
            order: 4,
            command: (e) => openSpecialCharDialog(e),
            isActive: () => false
        }
    ]
}

function buildDialogContent(chars: string[]): string {
    const buttons = chars
        .map(
            (char) =>
                `<button type="button" title="${char}" data-label="${char}" style="
                    width: 20px; height: 24px; font-size: 12px; cursor: pointer; line-height: 1;
                    border: 1px solid transparent; border-radius: 3px; background: none; padding: 0;
                    display: flex; align-items: center; justify-content: center;
                ">${char}</button>`
        )
        .join('')

    return `<style>
        .special-char-grid button:hover {
            border-color: #aaa !important;
            background: #f0f0f0 !important;
        }
    </style>
    <div style="display: flex; gap: 12px; align-items: flex-start;">
        <div class="special-char-grid" style="display: flex; flex-wrap: wrap; gap: 2px; width: 390px; max-height: 450px; overflow-y: auto;">
            ${buttons}
        </div>
        <div style="display: flex; flex-direction: column; gap: 4px; flex-shrink: 0;">
            <div class="special-char-preview-large" style="
                border: 1px solid #eee; font-size: 28px; height: 50px; width: 70px;
                display: flex; align-items: center; justify-content: center;
                background: #f9f9f9; box-sizing: border-box;
            ">&nbsp;</div>
            <div class="special-char-preview-label" style="
                border: 1px solid #eee; font-size: 11px; height: 22px; width: 70px;
                display: flex; align-items: center; justify-content: center;
                background: #f9f9f9; box-sizing: border-box;
                overflow: hidden; white-space: nowrap; text-overflow: ellipsis;
            ">&nbsp;</div>
        </div>
    </div>`
}

function openSpecialCharDialog(e: TiptapEditor) {
    const dialog = new Dialog('Special Characters', { draggable: true })
    dialog.setContent(buildDialogContent(e.profile.config.specialChars))
    dialog.addButton({ label: 'Close', variant: 'secondary', onClick: (d) => d.close() }).open()

    const el = dialog.element
    const grid = el.querySelector<HTMLElement>('.special-char-grid')
    const previewLarge = el.querySelector<HTMLElement>('.special-char-preview-large')
    const previewLabel = el.querySelector<HTMLElement>('.special-char-preview-label')

    if (!grid) return

    grid.addEventListener('mouseover', (ev) => {
        const btn = (ev.target as HTMLElement).closest('button[data-label]') as HTMLButtonElement | null
        if (!btn) return
        if (previewLarge) previewLarge.textContent = btn.dataset.label ?? ''
        if (previewLabel) previewLabel.textContent = btn.dataset.label ?? ''
    })

    grid.addEventListener('mouseout', (ev) => {
        const btn = (ev.target as HTMLElement).closest('button[data-label]') as HTMLButtonElement | null
        if (!btn) return
        if (previewLarge) previewLarge.innerHTML = '&nbsp;'
        if (previewLabel) previewLabel.innerHTML = '&nbsp;'
    })

    grid.addEventListener('click', (ev) => {
        const btn = (ev.target as HTMLElement).closest('button[data-label]') as HTMLButtonElement | null
        if (!btn) return
        const char = btn.dataset.label ?? ''
        if (!char) return
        e.tiptap
            .chain()
            .focus()
            .insertContent({ type: 'text', text: char, marks: [{ type: 'bold' }] })
            .run()
        dialog.close()
    })
}