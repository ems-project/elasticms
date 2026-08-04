import ProgressBar from '../../helpers/progressBar'
import { FileUploader } from '../FileUploader.ts'
import { resizeImage } from '../../helpers/resizeImage'
import ApiClient from './ApiClient.ts'

export interface UploadManagerOptions {
    container: HTMLElement
    api: ApiClient
    hashAlgo: string
    urlInitUpload: string
    getActiveFolderId: () => string | null
}

export default class UploadManager {
    readonly #options: UploadManagerOptions

    constructor(options: UploadManagerOptions) {
        this.#options = options
    }

    uploadAll(files: File[]) {
        return Promise.allSettled(files.map((file) => this.#uploadFile(file)))
    }

    #uploadFile(file: File) {
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
                if (liUpload) this.#options.container.removeChild(liUpload)
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
            this.#options.container.appendChild(liUpload)

            this.#getFileHash(file, progressBar)
                .then((fileHash) => {
                    progressBar.status('Resizing')
                    return this.#resizeImage(file, fileHash)
                })
                .then(() => {
                    progressBar.status('Finished')
                    setTimeout(() => {
                        if (liUpload) this.#options.container.removeChild(liUpload)
                        resolve()
                    }, 1000)
                })
                .catch((error) => {
                    uploadDiv.classList.add('upload-error')
                    progressBar.status(error.message).style('danger').progress(100)
                    setTimeout(() => {
                        if (liUpload === false) return
                        this.#options.container.removeChild(liUpload)
                        reject(new Error())
                    }, 3000)
                })
        })
    }

    async #resizeImage(file: File, fileHash: string): Promise<void> {
        return resizeImage(this.#options.hashAlgo, this.#options.urlInitUpload, file)
            .then((response: { hash: string } | null) => {
                if (response === null) {
                    return this.#createFile(file, fileHash)
                } else {
                    return this.#createFile(file, fileHash, response.hash)
                }
            })
            .catch(() => {
                return this.#createFile(file, fileHash)
            });
    }

    async #createFile(file: File, fileHash: string, resizedHash: string | null = null) {
        const formData = new FormData()
        formData.append('name', file.name)
        formData.append('filesize', file.size.toString())
        formData.append('fileMimetype', file.type)
        formData.append('fileHash', fileHash)
        formData.append('fileResizedHash', resizedHash ?? '')

        const activeFolderId = this.#options.getActiveFolderId()
        const path = activeFolderId ? `/add-file/${activeFolderId}` : '/add-file'
        await this.#options.api.post(path, formData, true).catch((response) =>
            response.json().then((json: { error: string }) => {
                throw new Error(json.error)
            })
        )
    }

    async #getFileHash(file: File, progressBar: ProgressBar): Promise<string> {
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
}