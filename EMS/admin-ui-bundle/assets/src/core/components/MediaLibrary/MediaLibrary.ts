import '@css/core/components/_media_library.scss'

import ApiClient from './Helper/Api.ts'
import Job from './Helper/Job.ts'
import Selection from './Dom/Selection.ts'
import DragDrop from './Dom/DragDrop.ts'
import Upload from './Helper/Upload.ts'

import ProgressBar from '../../helpers/progressBar'
import { Dialog, DialogSize } from '../Dialog.ts'

export interface MediaLibraryOptions {
    urlMediaLib: string
    urlInitUpload: string
    hashAlgo: string
}

interface Elements {
    header: HTMLElement
    breadcrumb: HTMLElement
    footer: HTMLElement
    inputUpload: HTMLInputElement
    files: HTMLElement
    loadMoreFiles: HTMLElement
    listFiles: HTMLElement
    listFolders: HTMLElement
    listUploads: HTMLElement
}

export interface State {
    activeFolderId: string | null
    searchValue: string | null
    sortId: string | null
    sortOrder: string | null
}

export default class MediaLibrary {
    id: string
    element: HTMLElement

    readonly #api: ApiClient
    readonly #jobPoller: Job
    readonly #selection: Selection
    readonly #dragDrop: DragDrop
    readonly #uploads: Upload

    #elements: Elements
    readonly #state: State = {
        activeFolderId: null,
        searchValue: null,
        sortId: null,
        sortOrder: null
    }

    #activeFolderHeader = ''
    #loadedFiles = 0
    #debounceTimer: number | undefined
    readonly #searchType: string

    readonly #clickHandlers = new Map<string, (target: HTMLElement, event: MouseEvent) => void>([
        ['media-lib-file', (target, event) => this._onClickFile(target, event)],
        ['media-lib-folder', (target) => this._onClickFolder(target)],
        ['btn-file-upload', () => this.#elements.inputUpload.click()],
        ['btn-file-view', (target, event) => this._onClickButtonFileView(target, event)],
        ['btn-file-rename', (target) => this._onClickButtonFileRename(target)],
        ['btn-file-delete', (target) => this._onClickButtonFileDelete(target)],
        ['btn-files-delete', (target) => this._onClickButtonFilesDelete(target)],
        ['btn-files-move', (target) => this._onClickButtonFilesMove(target)],
        ['btn-folder-add', () => this._onClickButtonFolderAdd()],
        ['btn-folder-delete', (target) => this._onClickButtonFolderDelete(target)],
        ['btn-folder-rename', (target) => this._onClickButtonFolderRename(target)],
        ['btn-folder-move', (target) => this._onClickButtonFolderMove(target)],
        ['btn-home', () => this._onClickButtonHome()],
        ['breadcrumb-item', (target) => this._onClickBreadcrumbItem(target)]
    ])

    constructor(element: HTMLElement, options: MediaLibraryOptions) {
        this.id = element.id
        this.element = element
        this.#api = new ApiClient(`${options.urlMediaLib}/${element.dataset.hash}`, () =>
            this.loading(true)
        )
        this.#jobPoller = new Job()
        this.#searchType = element.dataset.searchType ?? 'term'

        this.#elements = {
            header: element.querySelector('div.media-nav-bar') as HTMLElement,
            breadcrumb: element.querySelector('div.media-lib-breadcrumb') as HTMLElement,
            footer: element.querySelector('div.media-lib-footer') as HTMLElement,
            inputUpload: element.querySelector('input.file-uploader-input') as HTMLInputElement,
            files: element.querySelector('div.media-lib-files') as HTMLElement,
            loadMoreFiles: element.querySelector(
                'div.media-lib-files > div.media-lib-load-more'
            ) as HTMLElement,
            listFiles: element.querySelector('ul.media-lib-list-files') as HTMLElement,
            listFolders: element.querySelector('ul.media-lib-list-folders') as HTMLElement,
            listUploads: element.querySelector('ul.media-lib-list-uploads') as HTMLElement
        }

        this.#uploads = new Upload({
            container: this.#elements.listUploads,
            api: this.#api,
            hashAlgo: options.hashAlgo,
            urlInitUpload: options.urlInitUpload,
            getActiveFolderId: () => this.#state.activeFolderId
        })

        this.#dragDrop = new DragDrop({
            filesContainer: this.#elements.files,
            getActiveFolderId: () => this.#state.activeFolderId,
            getSelectionFiles: () => this.getSelectionFiles(),
            onUpload: (files) => this._uploadFiles(files),
            onSelectionReset: () => this._selectFilesReset(),
            onFilesMove: (targetFolderId) => {
                const moveButton = this.#elements.header.querySelector(
                    '.btn-files-move'
                ) as HTMLElement
                this._onClickButtonFilesMove(moveButton, targetFolderId)
            }
        })

        this.#selection = new Selection(this.#elements.listFiles, (event) =>
            this.#dragDrop.onDragFile(event)
        )

        this._init()
    }

    _init() {
        this.loading(true)
        this._addEventListeners()
        this._initInfiniteScrollFiles(this.#elements.files, this.#elements.loadMoreFiles)

        Promise.allSettled([this._getFolders(), this._getFiles()]).then(() => this.loading(false))
    }

    isLoading() {
        return this.element.classList.contains('loading')
    }

    loading(flag: boolean) {
        const buttons = this.element.querySelectorAll<HTMLButtonElement>(
            'button:not(.close-button)'
        )
        const uploadButton = this.#elements.inputUpload
            ? this.#elements.header.querySelector(`label[for="${this.#elements.inputUpload.id}"]`)
            : false

        if (flag) {
            this.element.classList.add('loading')
            buttons.forEach((button) => (button.disabled = true))
            if (uploadButton) uploadButton.setAttribute('disabled', 'disabled')
        } else {
            this.element.classList.remove('loading')
            buttons.forEach((button) => (button.disabled = false))
            if (uploadButton) uploadButton.removeAttribute('disabled')
        }
    }

    getSearchBox() {
        return this.#elements.header.querySelector('.media-lib-search') as HTMLInputElement
    }

    getFolders() {
        return this.#elements.listFolders.querySelectorAll<HTMLElement>('.media-lib-folder')
    }

    getSelectionFile() {
        return this.#selection.getFile()
    }

    getSelectionFiles() {
        return this.#selection.getFiles()
    }

    _addEventListeners() {
        document.addEventListener('keydown', (event) => {
            if ((event.ctrlKey || event.metaKey) && event.key === 'a') this._selectAllFiles(event)
        })

        this.element.onkeyup = (event) => {
            if (event.shiftKey) this.#selection.clearAnchor()
            const target = event.target as HTMLElement
            if (target.classList.contains('media-lib-search'))
                this._onSearchInput(target as HTMLInputElement, 1000)
        }

        this.element.onclick = (event) => {
            if (this.isLoading()) return

            const target = event.target as HTMLElement
            const classList = target.classList

            if (classList.contains('media-lib-search')) return

            this.#clickHandlers.forEach((handler, className) => {
                if (classList.contains(className)) handler(target, event)
            })

            if (Object.hasOwn(target.dataset, 'sortId')) this._onClickFileSort(target)

            if (!this.#selection.shouldPreserve(classList)) {
                this._selectFilesReset()
            }
        }

        this.#elements.inputUpload.onchange = (event) => {
            if (this.isLoading()) return
            const target = event.target as HTMLInputElement
            if (target.classList.contains('file-uploader-input')) {
                this._uploadFiles(Array.from(target.files ?? []))
                target.value = ''
            }
        }
        ;(['dragenter', 'dragover', 'dragleave', 'drop', 'dragend'] as const).forEach(
            (dragEvent) => {
                this.#elements.files.addEventListener(dragEvent, (event) =>
                    this.#dragDrop.onDragUpload(event as DragEvent)
                )
            }
        )
    }

    _onClickFile(item: HTMLElement, event: MouseEvent) {
        this.loading(true)
        const selection = this.#selection.select(item, event)
        const fileId = selection.length === 1 ? (item.dataset.id ?? null) : null
        this._getLayout(fileId).then(() => {
            this.loading(false)
        })
    }

    _onClickFileSort(target: HTMLElement) {
        this.#state.sortId = target.dataset.sortId ?? null
        this.#state.sortOrder = 'asc'
        if (Object.hasOwn(target.dataset, 'sortOrder')) {
            this.#state.sortOrder = target.dataset.sortOrder === 'asc' ? 'desc' : 'asc'
        }

        this._getFiles().then(() => this.loading(false))
    }

    _onClickButtonFileView(button: HTMLElement, event: MouseEvent) {
        event.preventDefault()

        const getSiblingFile = (
            fileId: string,
            sibling: 'previousSibling' | 'nextSibling'
        ): HTMLElement | null => {
            const row = this.#elements.listFiles.querySelector(
                `.media-lib-file[data-id='${fileId}']`
            ) as HTMLElement
            const rowSibling = row.closest('li')?.[sibling] as HTMLElement | null
            return rowSibling ? rowSibling.querySelector('.media-lib-file') : null
        }

        let currentFileId = button.dataset.id as string

        const navigation = (
            dialog: Dialog,
            action: 'next' | 'prev',
            sibling: 'previousSibling' | 'nextSibling'
        ) => {
            const navButton = dialog.element.querySelector(`.btn-preview-${action}`)
            if (!navButton || getSiblingFile(currentFileId, sibling) === null) return

            const buttonElement = navButton as HTMLElement
            buttonElement.style.display = 'inline-block'
            buttonElement.classList.remove('disabled')
            buttonElement.addEventListener('click', () => {
                const file = getSiblingFile(currentFileId, sibling)
                if (!file) return

                const header = this.#elements.files.querySelector('.media-lib-file-header')
                const headerHeight = header ? header.getBoundingClientRect().height : 0

                this._selectFilesReset()
                this.#selection.add(file)
                this.#elements.files.scrollTop =
                    file.offsetTop - this.#elements.files.offsetTop - headerHeight

                currentFileId = file.dataset.id as string
                void dialog.load(`${this.#api.pathPrefix}/file/${currentFileId}/view`)
            })
        }

        const dialog = new Dialog({
            url: `${this.#api.pathPrefix}/file/${currentFileId}/view`,
            ajaxModal: true,
            onAjaxModalResponse: (_, dialog) => {
                navigation(dialog, 'prev', 'previousSibling')
                navigation(dialog, 'next', 'nextSibling')
            }
        })

        const onKeydown = (e: KeyboardEvent) => {
            const actions: { [key: string]: 'next' | 'prev' } = {
                ArrowRight: 'next',
                ArrowLeft: 'prev'
            }
            const action = actions[e.key] || false
            if (!action) return

            const navButton = dialog.element.querySelector(
                `.btn-preview-${action}`
            ) as HTMLElement | null
            if (navButton) navButton.click()
        }

        document.addEventListener('keydown', onKeydown)

        dialog.onClose(() => {
            document.removeEventListener('keydown', onKeydown)
            const selectionFile = this.getSelectionFile()
            if (selectionFile) selectionFile.click()
        })

        dialog.open()
    }

    _onClickButtonFileRename(button: HTMLElement) {
        const fileId = button.dataset.id
        const rowElement = this.#elements.listFiles.querySelector(
            `.media-lib-file[data-id='${fileId}']`
        ) as HTMLElement

        new Dialog({
            url: `${this.#api.pathPrefix}/file/${fileId}/rename`,
            size: 'sm',
            ajaxModal: true,
            onAjaxModalResponse: (json, dialog) => {
                if (!json.success) return

                const fileRow = (json.fileRow as string) ?? ''
                if (fileRow.length > 0) {
                    const li = rowElement.closest('li')
                    if (li) li.innerHTML = fileRow
                }

                this._getLayout().then(() => {
                    dialog.close()
                    this.loading(false)
                })
            }
        }).open()
    }

    _onClickButtonFileDelete(button: HTMLElement) {
        const fileId = button.dataset.id
        const fileRow = this.#elements.listFiles.querySelector(
            `.media-lib-file[data-id='${fileId}']`
        ) as HTMLElement

        this.#api.post(`/file/${fileId}/delete`).then((json) => {
            if (!Object.hasOwn(json, 'success') || json.success === false) return

            fileRow.closest('li')?.remove()
            this._selectFilesReset()
            this.loading(false)
        })
    }

    _onClickButtonFilesDelete(button: HTMLElement) {
        const selection = this.getSelectionFiles()
        if (selection.length < 1) return

        const path = this.#state.activeFolderId
            ? `/delete-files/${this.#state.activeFolderId}`
            : '/delete-files'
        const query = new URLSearchParams({
            selectionFiles: selection.length.toString()
        })
        const modalSize = (button.dataset.modalSize ?? 'sm') as DialogSize

        new Dialog({
            url: this.#api.pathPrefix + path + '?' + query.toString(),
            size: modalSize,
            ajaxModal: true,
            onAjaxModalResponse: (json, dialog) => {
                if (!json.success) return

                let processed = 0
                const progressBar = new ProgressBar('progress-delete-files', {
                    label: 'Deleting files',
                    value: 100,
                    showPercentage: true
                })

                dialog.body.append(progressBar.element())
                this.loading(true)

                Promise.allSettled(
                    Array.from(selection).map(async (fileRow) => {
                        await this.#api.post(`/file/${fileRow.dataset.id}/delete`)
                        fileRow.closest('li')?.remove()
                        progressBar
                            .progress(Math.round((++processed / selection.length) * 100))
                            .style('success')
                    })
                )
                    .then(() => this._selectFilesReset())
                    .then(() => this.loading(false))
                    .then(() => new Promise((resolve) => setTimeout(resolve, 2000)))
                    .then(() => dialog.close())
            }
        }).open()
    }

    _onClickButtonFilesMove(button: HTMLElement, targetId: string | null = null) {
        const selection = this.getSelectionFiles()
        if (selection.length === 0) return

        const path = this.#state.activeFolderId
            ? `/move-files/${this.#state.activeFolderId}`
            : '/move-files'
        const query = new URLSearchParams({
            selectionFiles: selection.length.toString()
        })
        if (targetId) query.append('targetId', targetId)
        const modalSize = (button.dataset.modalSize ?? 'sm') as DialogSize

        new Dialog({
            url: this.#api.pathPrefix + path + '?' + query.toString(),
            size: modalSize,
            ajaxModal: true,
            onAjaxModalResponse: (json, dialog) => {
                if (!json.success || !json.targetFolderId) return

                const targetFolderId = json.targetFolderId

                let processed = 0
                const errorList: { [error: string]: number } = {}
                const progressBar = new ProgressBar('progress-move-files', {
                    label: selection.length === 1 ? 'Moving file' : 'Moving files',
                    value: 100,
                    showPercentage: true
                })

                const divAlert = document.createElement('div')
                divAlert.id = 'move-errors'
                divAlert.className = 'alert alert-danger'
                divAlert.style.display = 'none'
                divAlert.setAttribute('role', 'alert')

                dialog.body.append(divAlert)
                dialog.body.append(progressBar.element())
                this.loading(true)

                Promise.allSettled(
                    Array.from(selection).map((fileRow) => {
                        return new Promise<void>((resolve, reject) => {
                            this.#api
                                .post(`/file/${fileRow.dataset.id}/move`, { targetFolderId })
                                .then((moveOk) => {
                                    if (
                                        !Object.hasOwn(moveOk, 'success') ||
                                        moveOk.success === false
                                    )
                                        return
                                    fileRow.closest('li')?.remove()
                                    resolve()
                                })
                                .catch((moveError) =>
                                    moveError.json().then((moveError: { error: string }) => {
                                        errorList[moveError.error] =
                                            (errorList[moveError.error] || 0) + 1

                                        let content = ''
                                        for (const e in errorList) {
                                            content += `<p>${e} : for ${errorList[e]} files</p>`
                                        }

                                        divAlert.style.display = 'block'
                                        divAlert.innerHTML = content

                                        reject(new Error(moveError.error))
                                    })
                                )
                                .finally(() => {
                                    progressBar
                                        .style('success')
                                        .progress(
                                            Math.round((++processed / selection.length) * 100)
                                        )
                                        .status(`${processed} / ${selection.length}`)
                                })
                        })
                    })
                )
                    .then(() => this._getFiles())
                    .then(() => this.loading(false))
                    .then(() => {
                        if (Object.keys(errorList).length === 0)
                            setTimeout(() => {
                                dialog.close()
                            }, 2000)
                    })
            }
        }).open()
    }

    _onClickFolder(folder: HTMLElement) {
        this.loading(true)
        this.#state.searchValue = null

        this.getFolders().forEach((f) => f.classList.remove('active'))
        folder.classList.add('active')

        const folderItem = folder.closest('li')
        if (folderItem && folderItem.classList.contains('has-children')) {
            folderItem.classList.toggle('open')
        }

        this.#state.activeFolderId = folder.dataset.id ?? null
        this._getFiles().then(() => this.loading(false))
    }

    _onClickButtonFolderAdd() {
        const path = this.#state.activeFolderId
            ? `/add-folder/${this.#state.activeFolderId}`
            : '/add-folder'
        new Dialog({
            url: this.#api.pathPrefix + path,
            ajaxModal: true,
            onAjaxModalResponse: (json, dialog) => {
                if (!json.success) return
                this.loading(true)
                dialog.close()
                this._getFolders(json.path as string).then(() => this.loading(false))
            }
        }).open()
    }

    _onClickButtonFolderDelete(button: HTMLElement) {
        const folderId = button.dataset.id
        const modalSize = button.dataset.modalSize ?? 'sm'

        new Dialog({
            url: `${this.#api.pathPrefix}/folder/${folderId}/delete`,
            size: modalSize as DialogSize,
            ajaxModal: true,
            onAjaxModalResponse: (json, dialog) => {
                if (!json.jobId || !json.success) return

                this._runFolderJob(json, dialog, 'Deleting folder', () => {
                    this._onClickButtonHome()
                    return this._getFolders()
                })
                    .then(() => this.loading(false))
                    .then(() => dialog.close())
            }
        }).open()
    }

    _onClickButtonFolderRename(button: HTMLElement) {
        const folderId = button.dataset.id

        new Dialog({
            url: `${this.#api.pathPrefix}/folder/${folderId}/rename`,
            size: 'sm',
            ajaxModal: true,
            onAjaxModalResponse: (json, dialog) => {
                if (!json.success || !json.jobId || !json.path) return

                const path = json.path as string

                this._runFolderJob(json, dialog, 'Renaming', () => this._getFolders(path))
                    .then(() => this.loading(false))
                    .then(() => dialog.close())
            }
        }).open()
    }

    _onClickButtonFolderMove(button: HTMLElement, targetId: string | null = null) {
        const folderId = button.dataset.id
        const modalSize = (button.dataset.modalSize ?? 'sm') as DialogSize

        const path = `${this.#api.pathPrefix}/folder/${folderId}/move`
        const query = new URLSearchParams({})
        if (targetId) query.append('targetId', targetId)

        new Dialog({
            url: path + '?' + query.toString(),
            size: modalSize,
            ajaxModal: true,
            onAjaxModalResponse: (json, dialog) => {
                if (!json.success || !json.jobId || !json.path) return

                const path = json.path as string

                this._runFolderJob(json, dialog, 'Moving', () => this._getFolders(path))
                    .then(() => this.loading(false))
                    .then(() => dialog.close())
            }
        }).open()
    }

    _onClickButtonHome() {
        this.loading(true)
        this.getFolders().forEach((f) => f.classList.remove('active'))
        this.#state.activeFolderId = null
        this._getFiles().then(() => this.loading(false))
    }

    _onClickBreadcrumbItem(item: HTMLElement) {
        const id = item.dataset.id
        if (id) {
            const folder = this.#elements.listFolders.querySelector(
                `.media-lib-folder[data-id="${id}"]`
            ) as HTMLElement
            this._onClickFolder(folder)
        } else {
            this._onClickButtonHome()
        }
    }

    _onSearchInput(input: HTMLInputElement, delay: number) {
        clearTimeout(this.#debounceTimer)
        this.#debounceTimer = window.setTimeout(() => {
            this.#state.searchValue = input.value
            this._getFiles(0).then(() => this.loading(false))
        }, delay)
    }

    async _getLayout(fileId: string | null = null) {
        let path = '/layout'
        const query = new URLSearchParams({
            loaded: this.#loadedFiles.toString()
        })

        if (fileId) query.append('fileId', fileId)
        if (this.getSelectionFiles().length > 0)
            query.append('selectionFiles', this.getSelectionFiles().length.toString())
        if (this.#state.activeFolderId) query.append('folderId', this.#state.activeFolderId)
        if (this.#state.searchValue) query.append('search', this.#state.searchValue)

        if (Array.from(query).length > 0) path = path + '?' + query.toString()

        const json = await this.#api.get(path)
        if (Object.hasOwn(json, 'header')) this._refreshHeader(json.header)
        if (Object.hasOwn(json, 'breadcrumb')) this.#elements.breadcrumb.innerHTML = json.breadcrumb
        if (Object.hasOwn(json, 'footer')) this.#elements.footer.innerHTML = json.footer
    }

    async _getFiles(from = 0) {
        if (from === 0) {
            this.#loadedFiles = 0
            this.#elements.loadMoreFiles.classList.remove('show-load-more')
            this.#elements.listFiles.innerHTML = ''
        }

        const query = new URLSearchParams({ from: from.toString(), searchType: this.#searchType })
        if (this.getSelectionFiles().length > 0)
            query.append('selectionFiles', this.getSelectionFiles().length.toString())
        if (this.#state.searchValue) query.append('search', this.#state.searchValue)
        if (this.#state.sortId) query.append('sortId', this.#state.sortId)
        if (this.#state.sortOrder) query.append('sortOrder', this.#state.sortOrder)
        const path = this.#state.activeFolderId ? `/files/${this.#state.activeFolderId}` : '/files'

        const files = await this.#api.get(`${path}?${query.toString()}`)
        this._appendFiles(files)
    }

    async _getFolders(openPath: string | undefined = undefined) {
        this.#elements.listFolders.innerHTML = ''
        const json = await this.#api.get('/folders')
        this._appendFolderItems(json)
        if (openPath) {
            this._openPath(openPath)
        }
    }

    _openPath(path: string) {
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
            if (folder) this._onClickFolder(folder)
        }
    }

    _appendFiles(json: any) {
        if (Object.hasOwn(json, 'header')) {
            this._refreshHeader(json.header)
            this.#activeFolderHeader = json.header
        }
        if (Object.hasOwn(json, 'breadcrumb')) this.#elements.breadcrumb.innerHTML = json.breadcrumb
        if (Object.hasOwn(json, 'footer')) this.#elements.footer.innerHTML = json.footer

        const { rowHeader, totalRows, rows, remaining = false } = json
        if (rowHeader !== undefined) {
            this.#elements.listFiles.innerHTML += rowHeader
            if (Object.hasOwn(json, 'sort')) this._displaySort(json.sort.id, json.sort.order)
        }
        if (totalRows !== undefined) this.#loadedFiles += totalRows
        if (rows !== undefined) this.#elements.listFiles.innerHTML += rows

        if (remaining) {
            this.#elements.loadMoreFiles.classList.add('show-load-more')
        } else {
            this.#elements.loadMoreFiles.classList.remove('show-load-more')
        }
    }

    _appendFolderItems(json: { folders: string }) {
        this.#elements.listFolders.innerHTML = json.folders

        this.getFolders().forEach((folder) => {
            ;(['dragenter', 'dragover', 'dragleave', 'drop'] as const).forEach((dragEvent) => {
                folder.addEventListener(dragEvent, (event) =>
                    this.#dragDrop.onDragFolder(event as DragEvent)
                )
            })
        })
    }

    _refreshHeader(html: string) {
        const searchBoxHasFocus = document.activeElement === this.getSearchBox()

        this.#elements.header.innerHTML = html
        if (searchBoxHasFocus) {
            const searchBox = this.getSearchBox()
            searchBox.focus()
            const val = searchBox.value
            searchBox.value = ''
            searchBox.value = val
        }
    }

    _displaySort(sortId: string, sortOrder: string) {
        const sortElement = this.#elements.listFiles.querySelector(
            `[data-sort-id="${sortId}"]`
        ) as HTMLElement | null
        if (!sortElement) return
        sortElement.dataset.sortOrder = sortOrder
    }

    _uploadFiles(files: File[]) {
        this.loading(true)

        this.#uploads.uploadAll(files).then(() => this._getFiles().then(() => this.loading(false)))
    }

    _initInfiniteScrollFiles(scrollArea: HTMLElement, divLoadMore: HTMLElement) {
        const options = {
            root: scrollArea,
            rootMargin: '0px',
            threshold: 0.5
        }

        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    this.loading(true)
                    this._getFiles(this.#loadedFiles).then(() => this.loading(false))
                }
            })
        }, options)

        observer.observe(divLoadMore)
    }

    _selectAllFiles(event: KeyboardEvent) {
        if (event.target !== document.body) return
        event.preventDefault()

        this.loading(true)
        this.#selection.selectAll()
        this._getLayout().then(() => {
            this.loading(false)
        })
    }

    _selectFilesReset(refreshHeader = true) {
        if (refreshHeader) this._refreshHeader(this.#activeFolderHeader)
        this.#selection.reset()
    }

    async _runFolderJob(json: any, dialog: Dialog, label: string, refresh: () => Promise<unknown>) {
        if (json.async === true) {
            await Promise.allSettled([new Promise((resolve) => setTimeout(resolve, 3500))])
            dialog.close()
            return location.reload()
        }

        const jobId = json.jobId as string
        const jobProgressBar = new ProgressBar('progress-' + jobId, {
            label,
            value: 100,
            showPercentage: false
        })

        dialog.body.append(jobProgressBar.element())
        this.loading(true)

        await this.#jobPoller.run(jobId, jobProgressBar)
        await refresh()
        return await new Promise((resolve) => setTimeout(resolve, 2000))
    }
}
