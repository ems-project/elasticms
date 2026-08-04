import ApiClient from './ApiClient.ts'
import SelectionManager from './SelectionManager.ts'
import { Elements, State } from './MediaLibrary.ts'

export interface RendererOptions {
    api: ApiClient
    elements: Elements
    state: State
    selection: SelectionManager
    searchType: string
    onFolderDrag: (event: DragEvent) => void
    onOpenFolder: (folder: HTMLElement) => void
}

/**
 * Fetches media library data (files/folders/layout) from the API and renders
 * the resulting HTML into the DOM. Holds the state that is intrinsic to that
 * rendering (how many files were loaded, the last rendered folder header).
 */
export default class Renderer {
    readonly #api: ApiClient
    readonly #elements: Elements
    readonly #state: State
    readonly #selection: SelectionManager
    readonly #searchType: string
    readonly #onFolderDrag: (event: DragEvent) => void
    readonly #onOpenFolder: (folder: HTMLElement) => void

    #loadedFiles = 0
    #activeFolderHeader = ''

    constructor(options: RendererOptions) {
        this.#api = options.api
        this.#elements = options.elements
        this.#state = options.state
        this.#selection = options.selection
        this.#searchType = options.searchType
        this.#onFolderDrag = options.onFolderDrag
        this.#onOpenFolder = options.onOpenFolder
    }

    getLoadedFilesCount() {
        return this.#loadedFiles
    }

    getActiveFolderHeader() {
        return this.#activeFolderHeader
    }

    loadLayout(fileId: string | null = null) {
        let path = '/layout'
        const query = new URLSearchParams({
            loaded: this.#loadedFiles.toString()
        })

        const selectionCount = this.#selection.getFiles().length
        if (fileId) query.append('fileId', fileId)
        if (selectionCount > 0) query.append('selectionFiles', selectionCount.toString())
        if (this.#state.activeFolderId) query.append('folderId', this.#state.activeFolderId)
        if (this.#state.searchValue) query.append('search', this.#state.searchValue)

        if (Array.from(query).length > 0) path = path + '?' + query.toString()

        return this.#api.get(path).then((json) => {
            if (Object.hasOwn(json, 'header')) this.refreshHeader(json.header)
            if (Object.hasOwn(json, 'breadcrumb'))
                this.#elements.breadcrumb.innerHTML = json.breadcrumb
            if (Object.hasOwn(json, 'footer')) this.#elements.footer.innerHTML = json.footer
        })
    }

    loadFiles(from = 0) {
        if (from === 0) {
            this.#loadedFiles = 0
            this.#elements.loadMoreFiles.classList.remove('show-load-more')
            this.#elements.listFiles.innerHTML = ''
        }

        const selectionCount = this.#selection.getFiles().length
        const query = new URLSearchParams({ from: from.toString(), searchType: this.#searchType })
        if (selectionCount > 0) query.append('selectionFiles', selectionCount.toString())
        if (this.#state.searchValue) query.append('search', this.#state.searchValue)
        if (this.#state.sortId) query.append('sortId', this.#state.sortId)
        if (this.#state.sortOrder) query.append('sortOrder', this.#state.sortOrder)
        const path = this.#state.activeFolderId ? `/files/${this.#state.activeFolderId}` : '/files'

        return this.#api.get(`${path}?${query.toString()}`).then((files) => {
            this.#appendFiles(files)
        })
    }

    loadFolders(openPath: string | undefined = undefined) {
        this.#elements.listFolders.innerHTML = ''
        return this.#api.get('/folders').then((json) => {
            this.#appendFolderItems(json)
            if (openPath) {
                this.#openPath(openPath)
            }
        })
    }

    refreshHeader(html: string) {
        const searchBoxHasFocus = document.activeElement === this.#getSearchBox()

        this.#elements.header.innerHTML = html
        if (searchBoxHasFocus) {
            const searchBox = this.#getSearchBox()
            searchBox.focus()
            const val = searchBox.value
            searchBox.value = ''
            searchBox.value = val
        }
    }

    displaySort(sortId: string, sortOrder: string) {
        const sortElement = this.#elements.listFiles.querySelector(
            `[data-sort-id="${sortId}"]`
        ) as HTMLElement | null
        if (!sortElement) return
        sortElement.dataset.sortOrder = sortOrder
    }

    #getSearchBox() {
        return this.#elements.header.querySelector('.media-lib-search') as HTMLInputElement
    }

    #openPath(path: string) {
        let currentPath = ''
        path.split('/')
            .filter((f) => f !== '')
            .forEach((folderName) => {
                currentPath += `/${folderName}`
                const parentFolder = document.querySelector(
                    `.media-lib-folder[data-path="${currentPath}"]`
                )
                const parentLi = parentFolder ? parentFolder.closest('li') : null

                if (parentLi && parentLi.classList.contains('has-children')) {
                    parentLi.classList.add('open')
                }
            })

        if (currentPath !== '') {
            const folder = document.querySelector(
                `.media-lib-folder[data-path="${currentPath}"]`
            ) as HTMLElement | null
            if (folder) this.#onOpenFolder(folder)
        }
    }

    #appendFiles(json: any) {
        if (Object.hasOwn(json, 'header')) {
            this.refreshHeader(json.header)
            this.#activeFolderHeader = json.header
        }
        if (Object.hasOwn(json, 'breadcrumb')) this.#elements.breadcrumb.innerHTML = json.breadcrumb
        if (Object.hasOwn(json, 'footer')) this.#elements.footer.innerHTML = json.footer

        const { rowHeader, totalRows, rows, remaining = false } = json
        if (rowHeader !== undefined) {
            this.#elements.listFiles.innerHTML += rowHeader
            if (Object.hasOwn(json, 'sort')) this.displaySort(json.sort.id, json.sort.order)
        }
        if (totalRows !== undefined) this.#loadedFiles += totalRows
        if (rows !== undefined) this.#elements.listFiles.innerHTML += rows

        if (remaining) {
            this.#elements.loadMoreFiles.classList.add('show-load-more')
        } else {
            this.#elements.loadMoreFiles.classList.remove('show-load-more')
        }
    }

    #appendFolderItems(json: { folders: string }) {
        this.#elements.listFolders.innerHTML = json.folders

        this.#elements.listFolders
            .querySelectorAll<HTMLElement>('.media-lib-folder')
            .forEach((folder) => {
                ;(['dragenter', 'dragover', 'dragleave', 'drop'] as const).forEach((dragEvent) => {
                    folder.addEventListener(dragEvent, (event) =>
                        this.#onFolderDrag(event as DragEvent)
                    )
                })
            })
    }
}
