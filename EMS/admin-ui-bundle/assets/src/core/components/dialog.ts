import '../../../css/core/components/_dialog.scss'

interface DialogButton {
    label: string
    variant?: 'primary' | 'secondary' | 'danger' | 'default'
    onClick: (dialog: Dialog) => void
}

interface DialogOptions {
    draggable?: boolean
    closeLabel: string
    bodyClass?: string
}

export class Dialog {
    readonly element: HTMLDialogElement
    private body: HTMLElement
    private footer: HTMLElement
    private options: DialogOptions

    private onCloseCallback?: () => void

    constructor(title: string, options: DialogOptions) {
        this.options = options
        this.element = document.createElement('dialog')
        this.element.className = 'ems-dialog'

        this.element.innerHTML = `
            <div class="dialog-content">
                <div class="dialog-header">
                    <h4 class="dialog-title">${title}</h4>
                    <button type="button" class="dialog-close" aria-label="${options.closeLabel}" title="${options.closeLabel}">&times;</button>
                </div>
                <div class="dialog-body"></div>
                <div class="dialog-footer"></div>
            </div>
        `

        this.body = this.element.querySelector('.dialog-body')!

        if (options.bodyClass) {
            this.body.classList.add(...options.bodyClass.split(' ').filter(Boolean))
        }

        this.footer = this.element.querySelector('.dialog-footer')!

        this.element.querySelector('.dialog-close')!.addEventListener('click', () => this.close())

        this.element.addEventListener('close', () => {
            document.body.classList.remove('dialog-open')
            this.element.remove()
            this.onCloseCallback?.()
        })

        this.element.addEventListener('cancel', (e) => {
            e.preventDefault()
            this.close()
        })

        if (this.options.draggable) {
            this.makeDraggable()
        }

        const doc = window.top?.document || document
        doc.body.appendChild(this.element)
    }

    private makeDraggable(): void {
        const header = this.element.querySelector('.dialog-header') as HTMLElement
        const doc = window.top?.document || document
        header.classList.add('draggable')
        let offsetX = 0
        let offsetY = 0

        const onMouseMove = (e: MouseEvent) => {
            const content = this.element.querySelector('.dialog-content') as HTMLElement
            content.style.margin = '0'
            content.style.position = 'fixed'
            content.style.left = `${e.clientX - offsetX}px`
            content.style.top = `${e.clientY - offsetY}px`
        }

        const onMouseUp = () => {
            doc.removeEventListener('mousemove', onMouseMove)
            doc.removeEventListener('mouseup', onMouseUp)
        }

        header.addEventListener('mousedown', (e) => {
            if ((e.target as HTMLElement).closest('.dialog-close')) return
            const content = this.element.querySelector('.dialog-content') as HTMLElement
            const rect = content.getBoundingClientRect()
            content.style.width = `${rect.width}px`
            offsetX = e.clientX - rect.left
            offsetY = e.clientY - rect.top
            doc.addEventListener('mousemove', onMouseMove)
            doc.addEventListener('mouseup', onMouseUp)
        })
    }

    setContent(html: string | HTMLElement): this {
        if (typeof html === 'string') {
            this.body.innerHTML = html
        } else {
            this.body.appendChild(html)
        }
        return this
    }

    addButton({ label, variant = 'default', onClick }: DialogButton): this {
        const btn = document.createElement('button')
        btn.innerText = label
        btn.type = 'button'
        btn.dataset.variant = variant
        btn.onclick = (e) => {
            e.preventDefault()
            onClick(this)
        }
        this.footer.appendChild(btn)
        return this
    }

    open(): void {
        document.body.classList.add('dialog-open')
        this.element.showModal()
        const firstInput = this.element.querySelector<HTMLElement>('input, select, textarea')
        if (firstInput) {
            firstInput.focus()
        }
    }

    close(): void {
        this.element.close()
    }

    onClose(callback: () => void): this {
        this.onCloseCallback = callback
        return this
    }

    getFieldValue(id: string): string {
        const el = this.element.querySelector(`#${id}`) as HTMLInputElement
        return el ? el.value : ''
    }
}
