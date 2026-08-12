import IconFolderOpen from '@tabler/icons/outline/folder-open.svg?raw'
import { TiptapEditor } from '../Editor.ts'
import { escapeHtml } from '../Helper.ts'

interface DashboardConfig {
    label: string
    urlModal: string
}

export function buttonDashboardImage(
    editor: TiptapEditor,
    onSelect: (file: HTMLElement) => void
): HTMLButtonElement | null {
    const dashboard = editor.profile.config.emsBrowsers?.browser_image
    if (!dashboard) return null

    return createButton(editor, dashboard, (file) => onSelect(file))
}

export function buttonDashboardFile(
    editor: TiptapEditor,
    onSelect: (file: HTMLElement) => void
): HTMLButtonElement | null {
    const dashboard = editor.profile.config.emsBrowsers?.browser_file
    if (!dashboard) return null

    return createButton(editor, dashboard, (file) => onSelect(file))
}

function createButton(
    editor: TiptapEditor,
    dashboard: DashboardConfig,
    onSelect: (file: HTMLElement) => void
): HTMLButtonElement {
    const btn = document.createElement('button')
    btn.type = 'button'
    btn.className = 'ems-btn'
    btn.innerHTML = `${IconFolderOpen}<span>${escapeHtml(editor.trans('browse_dashboard'))}</span>`
    btn.addEventListener('click', () => openDashboardDialog(editor, dashboard, onSelect))
    return btn
}

function openDashboardDialog(
    editor: TiptapEditor,
    dashboard: DashboardConfig,
    onSelect: (file: HTMLElement) => void
) {
    const dialog = editor.createDialog('browse_dashboard', {
        resizable: true,
        size: 'lg',
        url: dashboard.urlModal,
        tiptapModal: false
    })

    let selectedFiles: HTMLElement[] = []

    dialog.body.addEventListener(
        'mediaLibraryInit',
        (ev) => {
            const { component } = (ev as CustomEvent).detail

            const submitBtn = dialog.addButtonRef({
                label: editor.trans('button_select'),
                variant: 'primary',
                onClick: () => {
                    const file = selectedFiles[0]
                    if (!file) return
                    onSelect(file)
                    dialog.close()
                }
            })
            submitBtn.disabled = true
            component.setOnSelectionChange((files: HTMLElement[]) => {
                selectedFiles = files
                submitBtn.disabled = files.length !== 1
            })

            dialog.addButton({
                label: editor.trans('button_cancel'),
                onClick: (d) => d.close()
            })
        },
        { once: true }
    )

    dialog.open()
}
