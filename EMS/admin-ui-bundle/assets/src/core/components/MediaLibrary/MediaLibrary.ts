import ApiClient from './ApiClient.ts'
import JobPoller from './JobPoller.ts'
import SelectionManager from './SelectionManager.ts'
import ProgressBar from '../../helpers/progressBar'
import { FileUploader } from '../FileUploader.ts'
import { resizeImage } from '../../helpers/resizeImage'
import { Dialog, DialogSize } from '../Dialog.ts'

export interface MediaLibraryOptions {
    urlMediaLib: string
    urlInitUpload: string
    hashAlgo: string
}

interface MediaLibraryElements {
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

export default class MediaLibrary {
    id: string
    element: HTMLElement

    readonly #api: ApiClient
    readonly #jobPoller: JobPoller
    readonly #selection: SelectionManager

    #options: MediaLibraryOptions
    #elements: MediaLibraryElements
    #activeFolderId: string | null = null
    #activeFolderHeader = ''
    #loadedFiles = 0

    #dragCounter = 0
    #dragFiles: HTMLElement[] | NodeListOf<Element> = []
    #debounceTimer: number | undefined
    #searchValue: string | null = null
    #sortId: string | null = null
    #sortOrder: string | null = null
    #searchType = 'term'


    constructor(element: HTMLElement, options: MediaLibraryOptions) {
        this.id = element.id
        this.element = element
        this.#api = new ApiClient(`${options.urlMediaLib}/${element.dataset.hash}`, () => this.loading(true))
        this.#jobPoller = new JobPoller()
        this.#options = options
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

        this._init()

        this.#selection = new SelectionManager(this.#elements.listFiles, (event) => this._onDragFile(event))
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
            if (classList.contains('media-lib-file')) this._onClickFile(target, event)
            if (classList.contains('media-lib-folder')) this._onClickFolder(target)

            if (classList.contains('btn-file-upload')) this.#elements.inputUpload.click()
            if (classList.contains('btn-file-view')) this._onClickButtonFileView(target, event)
            if (classList.contains('btn-file-rename')) this._onClickButtonFileRename(target)
            if (classList.contains('btn-file-delete')) this._onClickButtonFileDelete(target)
            if (classList.contains('btn-files-delete')) this._onClickButtonFilesDelete(target)
            if (classList.contains('btn-files-move')) this._onClickButtonFilesMove(target)

            if (classList.contains('btn-folder-add')) this._onClickButtonFolderAdd()
            if (classList.contains('btn-folder-delete')) this._onClickButtonFolderDelete(target)
            if (classList.contains('btn-folder-rename')) this._onClickButtonFolderRename(target)
            if (classList.contains('btn-folder-move')) this._onClickButtonFolderMove(target)

            if (classList.contains('btn-home')) this._onClickButtonHome()
            if (classList.contains('breadcrumb-item')) this._onClickBreadcrumbItem(target)
            if (Object.hasOwn(target.dataset, 'sortId')) this._onClickFileSort(target)

            const keepSelection = [
                'media-lib-file',
                'btn-file-rename',
                'btn-file-delete',
                'btn-files-delete',
                'btn-files-move',
                'btn-file-view'
            ]
            if (!keepSelection.some((className) => classList.contains(className))) {
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
                    this._onDragUpload(event as DragEvent)
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
        this.#sortId = target.dataset.sortId ?? null
        this.#sortOrder = 'asc'
        if (Object.hasOwn(target.dataset, 'sortOrder')) {
            this.#sortOrder = target.dataset.sortOrder === 'asc' ? 'desc' : 'asc'
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

        const path = this.#activeFolderId
            ? `/delete-files/${this.#activeFolderId}`
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
                    Array.from(selection).map((fileRow) => {
                        return this.#api.post(`/file/${fileRow.dataset.id}/delete`).then(() => {
                            fileRow.closest('li')?.remove()
                            progressBar
                                .progress(Math.round((++processed / selection.length) * 100))
                                .style('success')
                        })
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

        const path = this.#activeFolderId ? `/move-files/${this.#activeFolderId}` : '/move-files'
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
                            this.#api.post(`/file/${fileRow.dataset.id}/move`, { targetFolderId })
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
        this.#searchValue = null

        this.getFolders().forEach((f) => f.classList.remove('active'))
        folder.classList.add('active')

        const folderItem = folder.closest('li')
        if (folderItem && folderItem.classList.contains('has-children')) {
            folderItem.classList.toggle('open')
        }

        this.#activeFolderId = folder.dataset.id ?? null
        this._getFiles().then(() => this.loading(false))
    }

    _onClickButtonFolderAdd() {
        const path = this.#activeFolderId ? `/add-folder/${this.#activeFolderId}` : '/add-folder'
        new Dialog({
            url: this.#api.pathPrefix + path,
            ajaxModal: true,
            onAjaxModalResponse: (json) => {
                if (!json.success) return
                this.loading(true)
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

                if (json.async === true) {
                    Promise.allSettled([new Promise((resolve) => setTimeout(resolve, 3500))])
                        .then(() => dialog.close())
                        .then(() => location.reload())
                    return
                }

                const jobId = json.jobId as string
                const jobProgressBar = new ProgressBar('progress-' + jobId, {
                    label: 'Deleting folder',
                    value: 100,
                    showPercentage: false
                })

                dialog.body.append(jobProgressBar.element())
                this.loading(true)

                this.#jobPoller.run(jobId, jobProgressBar)
                    .then(() => this._onClickButtonHome())
                    .then(() => this._getFolders())
                    .then(() => new Promise((resolve) => setTimeout(resolve, 2000)))
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

                if (json.async === true) {
                    Promise.allSettled([new Promise((resolve) => setTimeout(resolve, 3500))])
                        .then(() => dialog.close())
                        .then(() => location.reload())
                    return
                }

                const jobId = json.jobId as string
                const path = json.path as string

                const jobProgressBar = new ProgressBar('progress-' + jobId, {
                    label: 'Renaming',
                    value: 100,
                    showPercentage: false
                })

                dialog.body.append(jobProgressBar.element())
                this.loading(true)

                this.#jobPoller.run(jobId, jobProgressBar)
                    .then(() => this._getFolders(path))
                    .then(() => new Promise((resolve) => setTimeout(resolve, 2000)))
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

                if (json.async === true) {
                    Promise.allSettled([new Promise((resolve) => setTimeout(resolve, 3500))])
                        .then(() => dialog.close())
                        .then(() => location.reload())
                    return
                }

                const jobId = json.jobId as string
                const path = json.path as string

                const jobProgressBar = new ProgressBar('progress-' + jobId, {
                    label: 'Moving',
                    value: 100,
                    showPercentage: false
                })

                dialog.body.append(jobProgressBar.element())
                this.loading(true)

                this.#jobPoller.run(jobId, jobProgressBar)
                    .then(() => this._getFolders(path))
                    .then(() => new Promise((resolve) => setTimeout(resolve, 2000)))
                    .then(() => this.loading(false))
                    .then(() => dialog.close())
            }
        }).open()
    }

    _onClickButtonHome() {
        this.loading(true)
        this.getFolders().forEach((f) => f.classList.remove('active'))
        this.#activeFolderId = null
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
            this.#searchValue = input.value
            this._getFiles(0).then(() => this.loading(false))
        }, delay)
    }

    _getLayout(fileId: string | null = null) {
        let path = '/layout'
        const query = new URLSearchParams({
            loaded: this.#loadedFiles.toString()
        })

        if (fileId) query.append('fileId', fileId)
        if (this.getSelectionFiles().length > 0)
            query.append('selectionFiles', this.getSelectionFiles().length.toString())
        if (this.#activeFolderId) query.append('folderId', this.#activeFolderId)
        if (this.#searchValue) query.append('search', this.#searchValue)

        if (Array.from(query).length > 0) path = path + '?' + query.toString()

        return this.#api.get(path).then((json) => {
            if (Object.hasOwn(json, 'header')) this._refreshHeader(json.header)
            if (Object.hasOwn(json, 'breadcrumb'))
                this.#elements.breadcrumb.innerHTML = json.breadcrumb
            if (Object.hasOwn(json, 'footer')) this.#elements.footer.innerHTML = json.footer
        })
    }

    _getFiles(from = 0) {
        if (from === 0) {
            this.#loadedFiles = 0
            this.#elements.loadMoreFiles.classList.remove('show-load-more')
            this.#elements.listFiles.innerHTML = ''
        }

        const query = new URLSearchParams({ from: from.toString(), searchType: this.#searchType })
        if (this.getSelectionFiles().length > 0)
            query.append('selectionFiles', this.getSelectionFiles().length.toString())
        if (this.#searchValue) query.append('search', this.#searchValue)
        if (this.#sortId) query.append('sortId', this.#sortId)
        if (this.#sortOrder) query.append('sortOrder', this.#sortOrder)
        const path = this.#activeFolderId ? `/files/${this.#activeFolderId}` : '/files'

        return this.#api.get(`${path}?${query.toString()}`).then((files) => {
            this._appendFiles(files)
        })
    }

    _getFolders(openPath: string | undefined = undefined) {
        this.#elements.listFolders.innerHTML = ''
        return this.#api.get('/folders').then((json) => {
            this._appendFolderItems(json)
            if (openPath) {
                this._openPath(openPath)
            }
        })
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
                    this._onDragFolder(event as DragEvent)
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

    _onDragUpload(event: DragEvent) {
        if (this.#dragFiles.length > 0) return

        if (event.type === 'dragend') this.#dragCounter = 0
        if (event.type === 'dragover') event.preventDefault()
        if (event.type === 'dragenter') {
            this.#dragCounter++
            this.#elements.files.classList.add('media-lib-drop-area')
            this._selectFilesReset()
        }
        if (event.type === 'dragleave') {
            this.#dragCounter--
            if (this.#dragCounter === 0)
                this.#elements.files.classList.remove('media-lib-drop-area')
        }
        if (event.type === 'drop') {
            event.preventDefault()
            this.#dragCounter = 0
            this.#elements.files.classList.remove('media-lib-drop-area')

            const files = event.dataTransfer?.files
            if (files) this._uploadFiles(Array.from(files))
        }
    }

    _onDragFolder(event: DragEvent) {
        if (this.#dragFiles.length === 0) return
        const target = event.target as HTMLElement
        if (target.dataset.id === this.#activeFolderId) return

        if (event.type === 'dragover') event.preventDefault()
        if (event.type === 'dragenter') {
            this.getFolders().forEach((f) => f.classList.remove('media-lib-drop-area'))
            target.classList.add('media-lib-drop-area')
        }
        if (event.type === 'dragleave') {
            target.classList.remove('media-lib-drop-area')
        }
        if (event.type === 'drop') {
            event.preventDefault()
            target.classList.remove('media-lib-drop-area')
            const folderId = target.dataset.id ?? null
            const moveButton = this.#elements.header.querySelector('.btn-files-move') as HTMLElement

            this._onClickButtonFilesMove(moveButton, folderId)
        }
    }

    _onDragFile(event: DragEvent) {
        if (event.type === 'dragstart') {
            this.#dragFiles = this.getSelectionFiles()
        }
        if (event.type === 'dragend') {
            this.#dragFiles = []
            this._selectFilesReset()
        }
    }

    _uploadFiles(files: File[]) {
        this.loading(true)

        Promise.allSettled(files.map((file) => this._uploadFile(file))).then(() =>
            this._getFiles().then(() => this.loading(false))
        )
    }

    _uploadFile(file: File) {
        return new Promise<void>((resolve, reject) => {
            const id = Date.now()
            let liUpload: HTMLLIElement | false = document.createElement('li')
            liUpload.id = `upload-${id}`

            const uploadDiv = document.createElement('div')
            uploadDiv.className = 'upload-file'

            const closeButton = document.createElement('button')
            closeButton.type = 'button'
            closeButton.className = 'close-button'
            closeButton.addEventListener('click', () => {
                if (liUpload) this.#elements.listUploads.removeChild(liUpload)
                liUpload = false
                reject(new Error())
            })

            const closeIcon = document.createElement('i')
            closeIcon.className = 'fa fa-times'
            closeIcon.setAttribute('aria-hidden', 'true')
            closeButton.appendChild(closeIcon)

            const progressBar = new ProgressBar(`progress-${id}`, {
                label: file.name,
                value: 5
            })

            uploadDiv.append(progressBar.style('success').element())
            uploadDiv.append(closeButton)

            liUpload.appendChild(uploadDiv)
            this.#elements.listUploads.appendChild(liUpload)

            this._getFileHash(file, progressBar)
                .then((fileHash) => {
                    progressBar.status('Resizing')
                    return this._resizeImage(file, fileHash)
                })
                .then(() => {
                    progressBar.status('Finished')
                    setTimeout(() => {
                        if (liUpload) this.#elements.listUploads.removeChild(liUpload)
                        resolve()
                    }, 1000)
                })
                .catch((error) => {
                    uploadDiv.classList.add('upload-error')
                    progressBar.status(error.message).style('danger').progress(100)
                    setTimeout(() => {
                        if (liUpload === false) return
                        this.#elements.listUploads.removeChild(liUpload)
                        reject(new Error())
                    }, 3000)
                })
        })
    }

    async _resizeImage(file: File, fileHash: string): Promise<void> {
        return await resizeImage(this.#options.hashAlgo, this.#options.urlInitUpload, file)
            .then((response: { hash: string } | null) => {
                if (response === null) {
                    return this._createFile(file, fileHash)
                } else {
                    return this._createFile(file, fileHash, response.hash)
                }
            })
            .catch(() => {
                return this._createFile(file, fileHash)
            })
    }

    async _createFile(file: File, fileHash: string, resizedHash: string | null = null) {
        const formData = new FormData()
        formData.append('name', file.name)
        formData.append('filesize', file.size.toString())
        formData.append('fileMimetype', file.type)
        formData.append('fileHash', fileHash)
        formData.append('fileResizedHash', resizedHash ?? '')

        const path = this.#activeFolderId ? `/add-file/${this.#activeFolderId}` : '/add-file'
        await this.#api.post(path, formData, true).catch((response) =>
            response.json().then((json: { error: string }) => {
                throw new Error(json.error)
            })
        )
    }

    async _getFileHash(file: File, progressBar: ProgressBar): Promise<string> {
        const hash = await new Promise((resolve, reject) => {
            let fileHash: string | null = null
            const fileUpload = () =>
                new FileUploader({
                    file,
                    algo: this.#options.hashAlgo,
                    initUrl: this.#options.urlInitUpload,
                    onHashAvailable: function (hash: string) {
                        progressBar.status('Hash available').progress(0)
                        fileHash = hash
                    },
                    onProgress: function (status: string, progress: number, remaining: string) {
                        if (status === 'Computing hash') {
                            progressBar.status('Calculating ...').progress(Number(remaining))
                        }
                        if (status === 'Uploading') {
                            progressBar
                                .status('Uploading: ' + remaining)
                                .progress(Math.round(progress * 100))
                        }
                    },
                    onUploaded: function () {
                        progressBar.status('Uploaded').progress(100)
                        resolve(fileHash)
                    },
                    onError: (message: string) => reject(message)
                })
            fileUpload()
        })

        if (typeof hash !== 'string') throw new Error('Invalid hash')

        return hash
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
}
