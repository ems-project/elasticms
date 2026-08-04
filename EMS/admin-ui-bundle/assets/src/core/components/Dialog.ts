import '../../../css/core/components/_dialog.scss'
import AddedDomEvent from '../events/addedDomEvent'

export type DialogSize = 'xs' | 'sm' | 'md' | 'lg'

const DIALOG_SIZE_WIDTHS: Record<DialogSize, number> = {
    xs: 300,
    sm: 480,
    md: 720,
    lg: 960
}

type DialogMessageType = 'success' | 'info' | 'warning' | 'error'

const DIALOG_MESSAGE_CLASSES: Record<DialogMessageType, string> = {
    success: 'alert-success',
    info: 'alert-info',
    warning: 'alert-warning',
    error: 'alert-danger'
}

interface DialogButton {
    label: string
    variant?: 'primary' | 'secondary' | 'danger' | 'default'
    align?: 'left' | 'right'
    onClick: (dialog: Dialog) => void
}

export interface DialogOptions {
    title?: string
    size?: DialogSize
    draggable?: boolean
    resizable?: boolean
    minWidth?: number
    closeLabel?: string
    bodyClasses?: string[]
    doc?: Document
    url?: string
    ajaxModal?: boolean
    onAjaxModalResponse?: (data: DialogAjaxModalResponse, dialog: Dialog) => void
}

interface DialogAjaxResponse {
    title?: string
    content?: string
    footer?: string
}

interface DialogAjaxModalResponse {
    modalMessages?: Partial<Record<DialogMessageType, string>>[]
    modalTitle?: string
    modalBody?: string
    modalFooter?: string
    modalClose?: boolean
    success?: boolean
    [key: string]: unknown
}

export class Dialog {
    readonly element: HTMLDialogElement
    title: HTMLElement
    body: HTMLElement
    footer: HTMLElement
    private options: DialogOptions
    private readonly doc: Document
    private currentUrl?: string
    private isBusy = false
    private onCloseCallback?: () => void

    constructor(options: DialogOptions) {
        this.options = options
        this.doc = options.doc ?? document
        this.element = this.buildElement(options)
        this.body = this.element.querySelector('.dialog-body')!
        this.title = this.element.querySelector('.dialog-title')!
        this.footer = this.element.querySelector('.dialog-footer')!

        if (options.bodyClasses) {
            this.body.classList.add(...options.bodyClasses)
        }

        this.bindLifecycleEvents()

        if (options.draggable) {
            this.makeDraggable()
        }

        if (options.resizable) {
            this.makeResizable(this.element.querySelector('.dialog-content')!)
        }

        this.doc.body.appendChild(this.element)

        if (options.ajaxModal) {
            this.element.addEventListener('keydown', this.onKeyDown)
        }

        if (options.url) {
            void this.loadUrl(options.url)
        }
    }

    open(): void {
        this.doc.body.classList.add('dialog-open')
        this.element.showModal()
        if (this.options.resizable) {
            const content = this.element.querySelector<HTMLElement>('.dialog-content')!
            if (this.options.minWidth) {
                content.style.minWidth = `${this.options.minWidth}px`
            }
            content.style.width = `${content.getBoundingClientRect().width}px`
        }
        this.element.querySelector<HTMLElement>('input, select, textarea')?.focus()
    }

    close(): void {
        this.element.close()
    }

    onClose(callback: () => void): this {
        this.onCloseCallback = callback
        return this
    }

    setContent(html: string | HTMLElement): this {
        if (typeof html === 'string') {
            this.body.innerHTML = html
        } else {
            this.body.appendChild(html)
        }
        return this
    }

    addButton({ label, variant = 'default', align = 'right', onClick }: DialogButton): this {
        const btn = this.doc.createElement('button')
        btn.innerText = label
        btn.type = 'button'
        btn.className = 'ems-btn'
        btn.dataset.variant = variant
        btn.dataset.align = align
        btn.onclick = (e) => {
            e.preventDefault()
            onClick(this)
        }
        this.footer.appendChild(btn)
        return this
    }

    getFieldValue(id: string): string {
        const el = this.element.querySelector(`#${id}`) as HTMLInputElement
        return el ? el.value : ''
    }

    private buildElement(options: DialogOptions): HTMLDialogElement {
        const element = this.doc.createElement('dialog')
        element.className = 'ems-dialog'
        element.innerHTML = `
            <div class="dialog-content">
                <div class="dialog-header">
                    <h4 class="dialog-title">${options.title ?? ''}</h4>
                    <button type="button" class="dialog-close" aria-label="${options.closeLabel ?? 'Close'}" title="${options.closeLabel ?? 'Close'}">&times;</button>
                </div>
                <div class="dialog-body"></div>
                <div class="dialog-footer"></div>
            </div>
        `

        if (options.size) {
            const content = element.querySelector<HTMLElement>('.dialog-content')!
            content.style.width = `${DIALOG_SIZE_WIDTHS[options.size]}px`
        }

        return element
    }

    private bindLifecycleEvents(): void {
        this.element.querySelector('.dialog-close')!.addEventListener('click', () => this.close())

        this.element.addEventListener('close', () => {
            this.doc.body.classList.remove('dialog-open')
            this.element.remove()
            this.onCloseCallback?.()
        })

        this.element.addEventListener('cancel', (e) => {
            e.preventDefault()
            this.close()
        })
    }

    private async loadUrl(url: string): Promise<void> {
        if (this.isBusy) return
        this.isBusy = true
        this.currentUrl = url
        this.body.innerHTML = '<div class="dialog-loading"></div>'
        try {
            const res = await fetch(url)
            this.applyResponse(await res.json())
        } catch {
            this.body.innerHTML = '<div class="dialog-error"></div>'
        } finally {
            this.isBusy = false
        }
    }

    private async submitForm(form: HTMLFormElement): Promise<void> {
        if (this.isBusy || !this.currentUrl) return
        this.isBusy = true
        const formData = new FormData(form)
        this.setFormDisabled(form, true)
        try {
            const res = await fetch(this.currentUrl, { method: 'POST', body: formData })
            this.applyResponse(await res.json())
        } catch {
            this.setBody(`<div class="dialog-error">Something went wrong.</div>`)
            this.setFormDisabled(form, false)
        } finally {
            this.isBusy = false
        }
    }

    private setFormDisabled(form: HTMLFormElement, disabled: boolean): void {
        form.querySelectorAll<
            HTMLInputElement | HTMLButtonElement | HTMLSelectElement | HTMLTextAreaElement
        >('input, button, select, textarea').forEach((el) => {
            el.disabled = disabled
        })
        this.element
            .querySelector<HTMLButtonElement>('#ajax-modal-submit')
            ?.toggleAttribute('disabled', disabled)
    }

    private applyResponse(data: DialogAjaxResponse | DialogAjaxModalResponse): void {
        this.body.querySelector('.dialog-loading')?.remove()

        const { title, body, footer, messages, close } = this.normalizeResponse(data)

        if (title) this.setTitle(title)

        if (body && body.length > 0) {
            this.setBody(body)
        } else if (this.options.ajaxModal) {
            this.setBody('')
        }

        if (messages) this.setMessages(messages)

        if (footer) {
            this.setFooter(footer)
        } else if (this.options.ajaxModal) {
            this.setFooter('')
            this.addButton({
                label: this.options.closeLabel ?? 'Close',
                onClick: (dialog) => dialog.close()
            })
        }

        if (this.options.ajaxModal) {
            this.options.onAjaxModalResponse?.(data as DialogAjaxModalResponse, this)
        }

        if (close) {
            this.close()
        } else {
            this.bindForm()
            new AddedDomEvent(this.element).dispatch()
        }
    }

    private normalizeResponse(data: DialogAjaxResponse | DialogAjaxModalResponse) {
        if (this.options.ajaxModal) {
            const d = data as DialogAjaxModalResponse
            return {
                title: d.modalTitle,
                body: d.modalBody,
                footer: d.modalFooter,
                messages: d.modalMessages,
                close: d.modalClose ?? false
            }
        }
        const d = data as DialogAjaxResponse
        return {
            title: d.title,
            body: d.content,
            footer: d.footer,
            messages: undefined,
            close: false
        }
    }

    private bindForm(): void {
        const form = this.body.querySelector('form')
        if (!form) return

        form.addEventListener('submit', (e) => {
            e.preventDefault()
            void this.submitForm(form)
        })

        this.element
            .querySelector<HTMLButtonElement>('#ajax-modal-submit')
            ?.addEventListener('click', () => {
                void this.submitForm(form)
            })
    }

    private readonly onKeyDown = (e: KeyboardEvent): void => {
        if (e.key !== 'Enter' || e.shiftKey) return
        if ((e.target as HTMLElement).nodeName.toLowerCase() === 'textarea') return

        const submitBtn = this.element.querySelector<HTMLButtonElement>('#ajax-modal-submit')
        if (submitBtn) {
            e.preventDefault()
            submitBtn.click()
        }
    }

    private setTitle(title: string): void {
        this.title.textContent = title
    }

    private setBody(body: string): void {
        this.body.innerHTML = body
    }

    private setFooter(footer: string): void {
        this.footer.innerHTML = footer
    }

    private setMessages(messages: Partial<Record<DialogMessageType, string>>[]): void {
        messages.forEach((m) => {
            const type = Object.keys(m)[0] as DialogMessageType
            this.printMessage(type, m[type]!)
        })
    }

    private printMessage(type: DialogMessageType, message: string): void {
        const cls = DIALOG_MESSAGE_CLASSES[type] ?? DIALOG_MESSAGE_CLASSES.success
        this.body.insertAdjacentHTML(
            'afterbegin',
            `<div class="alert ${cls}" role="alert">${message.replace(/\n/g, '<br>')}</div>`
        )
    }

    private makeResizable(content: HTMLElement): void {
        const handle = this.doc.createElement('div')
        handle.className = 'dialog-resize-handle'
        content.style.overflow = 'hidden'
        content.appendChild(handle)

        const doc = this.doc
        let startX = 0
        let startWidth = 0
        const minWidth = this.options.minWidth ?? 300

        const onMouseMove = (e: MouseEvent) => {
            const newWidth = Math.max(minWidth, startWidth + (e.clientX - startX))
            content.style.width = `${newWidth}px`
        }

        const onMouseUp = () => {
            doc.removeEventListener('mousemove', onMouseMove)
            doc.removeEventListener('mouseup', onMouseUp)
            doc.body.style.userSelect = ''
        }

        handle.addEventListener('mousedown', (e) => {
            e.preventDefault()
            startX = e.clientX
            startWidth = content.getBoundingClientRect().width
            doc.body.style.userSelect = 'none'
            doc.addEventListener('mousemove', onMouseMove)
            doc.addEventListener('mouseup', onMouseUp)
        })
    }

    private makeDraggable(): void {
        const header = this.element.querySelector('.dialog-header') as HTMLElement
        const doc = this.doc
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
}
