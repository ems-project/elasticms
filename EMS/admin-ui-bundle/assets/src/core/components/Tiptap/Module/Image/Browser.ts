import { TiptapEditor } from '../../Editor.ts'

interface BrowsableImage {
    image: string
    thumb: string
    folder: string
}

export function getImageBrowserListUrl(editor: TiptapEditor): string | null {
    return (
        editor.profile.config.emsBrowsers?.browser_image?.url ??
        editor.profile.config.imageBrowser_listUrl ??
        null
    )
}

function groupImagesByFolder(items: BrowsableImage[]): Map<string, BrowsableImage[]> {
    const folders = new Map<string, BrowsableImage[]>()
    for (const item of items) {
        const folder = item.folder || 'Images'
        if (!folders.has(folder)) folders.set(folder, [])
        folders.get(folder)!.push(item)
    }
    return folders
}

export function openImageBrowser(editor: TiptapEditor, onSelect: (url: string) => void): void {
    const listUrl = getImageBrowserListUrl(editor)
    if (!listUrl) return

    const dialog = editor.createDialog('image_browse', {
        bodyClasses: ['tiptap-dialog-image-browser'],
        resizable: true,
        size: 'md'
    })

    const folderTabs = document.createElement('div')
    folderTabs.className = 'tiptap-image-browser-folders'

    const grid = document.createElement('div')
    grid.className = 'tiptap-image-browser-grid'

    const status = document.createElement('p')
    status.className = 'tiptap-image-browser-status'
    status.textContent = editor.trans('image_browse_loading')

    dialog.body.append(folderTabs, status, grid)
    dialog.addButton({
        label: editor.trans('button_cancel'),
        variant: 'secondary',
        onClick: (d) => d.close()
    })
    dialog.open()

    fetch(listUrl, { credentials: 'same-origin' })
        .then((response) => {
            if (!response.ok) throw new Error(`HTTP ${response.status}`)
            return response.json() as Promise<unknown>
        })
        .then((list) => {
            const items = Array.isArray(list) ? (list as BrowsableImage[]) : []
            renderImageBrowser(editor, folderTabs, grid, status, items, (url) => {
                onSelect(url)
                dialog.close()
            })
        })
        .catch(() => {
            status.textContent = editor.trans('image_browse_error')
            status.classList.add('is-error')
        })
}

function renderImageBrowser(
    editor: TiptapEditor,
    folderTabs: HTMLElement,
    grid: HTMLElement,
    status: HTMLElement,
    items: BrowsableImage[],
    onSelect: (url: string) => void
): void {
    if (!items.length) {
        status.textContent = editor.trans('image_browse_empty')
        return
    }
    status.remove()

    const folders = groupImagesByFolder(items)
    const folderNames = Array.from(folders.keys())

    const renderGrid = (folder: string) => {
        grid.innerHTML = ''
        for (const item of folders.get(folder) ?? []) {
            const thumb = document.createElement('button')
            thumb.type = 'button'
            thumb.className = 'tiptap-image-browser-item'
            thumb.title = item.image
            const img = document.createElement('img')
            img.src = item.thumb || item.image
            img.alt = ''
            thumb.appendChild(img)
            thumb.addEventListener('click', () => onSelect(item.image))
            grid.appendChild(thumb)
        }
    }

    if (folderNames.length > 1) {
        folderNames.forEach((folder, idx) => {
            const tab = document.createElement('button')
            tab.type = 'button'
            tab.className = 'tiptap-image-browser-folder' + (idx === 0 ? ' is-active' : '')
            tab.textContent = folder
            tab.addEventListener('click', () => {
                folderTabs
                    .querySelectorAll('.tiptap-image-browser-folder')
                    .forEach((el) => el.classList.remove('is-active'))
                tab.classList.add('is-active')
                renderGrid(folder)
            })
            folderTabs.appendChild(tab)
        })
    }

    renderGrid(folderNames[0])
}
