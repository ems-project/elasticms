type DraggableElement = HTMLElement & {
    _dragEventHandlers?: { [event: string]: (event: DragEvent) => void }
}

const PRESERVING_CLASSES = [
    'media-lib-file',
    'btn-file-rename',
    'btn-file-delete',
    'btn-files-delete',
    'btn-files-move',
    'btn-file-view'
]

export default class Selection {
    readonly #container: HTMLElement
    readonly #onDragFile: (event: DragEvent) => void
    #lastFile: HTMLElement | null = null

    constructor(container: HTMLElement, onDragFile: (event: DragEvent) => void) {
        this.#container = container
        this.#onDragFile = onDragFile
    }

    getFile() {
        const selection = this.getFiles()
        return selection.length === 1 ? selection[0] : null
    }

    getFiles() {
        return this.#container.querySelectorAll<HTMLElement>('.active')
    }

    clearAnchor() {
        this.#lastFile = null
    }

    shouldPreserve(classList: DOMTokenList) {
        return PRESERVING_CLASSES.some((className) => classList.contains(className))
    }

    select(item: HTMLElement, event: MouseEvent) {
        if (event.shiftKey && this.#lastFile !== null) {
            const files = Array.from(
                this.#container.querySelectorAll<HTMLElement>('.media-lib-file')
            )
            let start = files.indexOf(item)
            let end = files.indexOf(this.#lastFile)
            if (start > end) [start, end] = [end, start]

            files.forEach((f, index) => {
                if (index >= start && index <= end) this.add(f)
            })
        } else if (event.ctrlKey || event.metaKey) {
            this.add(item, true)
        } else {
            this.reset()
            this.add(item)
        }

        this.#lastFile = item

        return this.getFiles()
    }

    selectAll() {
        const files = this.#container.querySelectorAll<HTMLElement>('.media-lib-file')
        files.forEach((f) => this.add(f))
    }

    add(item: HTMLElement, deselect = false) {
        const dragItem = item as DraggableElement
        if (!dragItem._dragEventHandlers) dragItem._dragEventHandlers = {}

        if (!item.classList.contains('active')) {
            item.classList.add('active')
            item.draggable = true
            ;(['dragstart', 'dragend'] as const).forEach((dragEvent) => {
                if (!dragItem._dragEventHandlers?.[dragEvent]) {
                    const handler = (event: DragEvent) => this.#onDragFile(event)
                    dragItem._dragEventHandlers![dragEvent] = handler
                    item.addEventListener(dragEvent, handler)
                }
            })
        } else if (deselect) {
            item.classList.remove('active')
            item.draggable = false
            ;(['dragstart', 'dragend'] as const).forEach((dragEvent) => {
                const handler = dragItem._dragEventHandlers?.[dragEvent]
                if (handler) {
                    item.removeEventListener(dragEvent, handler)
                    delete dragItem._dragEventHandlers![dragEvent]
                }
            })
        }
    }

    reset() {
        this.getFiles().forEach((file) => {
            const dragFile = file as DraggableElement
            file.classList.remove('active')
            file.draggable = false
            ;(['dragstart', 'dragend'] as const).forEach((dragEvent) => {
                const handler = dragFile._dragEventHandlers?.[dragEvent]
                if (handler) file.removeEventListener(dragEvent, handler)
            })
        })
    }
}
