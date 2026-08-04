export interface DragDropControllerOptions {
    filesContainer: HTMLElement
    getActiveFolderId: () => string | null
    getSelectionFiles: () => NodeListOf<HTMLElement>
    onUpload: (files: File[]) => void
    onSelectionReset: () => void
    onFilesMove: (targetFolderId: string | null) => void
}

export default class DragDropController {
    readonly #options: DragDropControllerOptions
    #counter = 0
    #files: HTMLElement[] | NodeListOf<Element> = []
    #highlighted: HTMLElement | null = null

    constructor(options: DragDropControllerOptions) {
        this.#options = options
    }

    onDragUpload(event: DragEvent) {
        if (this.#files.length > 0) return

        if (event.type === 'dragend') this.#counter = 0
        if (event.type === 'dragover') event.preventDefault()
        if (event.type === 'dragenter') {
            this.#counter++
            this.#options.filesContainer.classList.add('media-lib-drop-area')
            this.#options.onSelectionReset()
        }
        if (event.type === 'dragleave') {
            this.#counter--
            if (this.#counter === 0)
                this.#options.filesContainer.classList.remove('media-lib-drop-area')
        }
        if (event.type === 'drop') {
            event.preventDefault()
            this.#counter = 0
            this.#options.filesContainer.classList.remove('media-lib-drop-area')

            const files = event.dataTransfer?.files
            if (files) this.#options.onUpload(Array.from(files))
        }
    }

    onDragFolder(event: DragEvent) {
        if (this.#files.length === 0) return
        const target = event.target as HTMLElement
        if (target.dataset.id === this.#options.getActiveFolderId()) return

        if (event.type === 'dragover') event.preventDefault()
        if (event.type === 'dragenter') {
            this.#highlighted?.classList.remove('media-lib-drop-area')
            target.classList.add('media-lib-drop-area')
            this.#highlighted = target
        }
        if (event.type === 'dragleave') {
            target.classList.remove('media-lib-drop-area')
        }
        if (event.type === 'drop') {
            event.preventDefault()
            target.classList.remove('media-lib-drop-area')
            this.#options.onFilesMove(target.dataset.id ?? null)
        }
    }

    onDragFile(event: DragEvent) {
        if (event.type === 'dragstart') {
            this.#files = this.#options.getSelectionFiles()
        }
        if (event.type === 'dragend') {
            this.#files = []
            this.#options.onSelectionReset()
        }
    }
}