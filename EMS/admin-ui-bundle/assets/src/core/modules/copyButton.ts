import type { EmsModule } from '../types/emsModule'
import notifications from '../components/notifications'

const SELECTOR = 'button[data-copy-text], button[data-copy-target]'
const FEEDBACK_DURATION = 2000

interface Feedback {
    icon: HTMLElement | null
    originalClass: string
    timeoutId: ReturnType<typeof setTimeout>
}

export default class CopyButton implements EmsModule {
    private readonly pending = new WeakSet<HTMLButtonElement>()
    private readonly feedback = new Map<HTMLButtonElement, Feedback>()

    public load(target: Document | HTMLElement): void {
        if (target.querySelector(SELECTOR) === null) {
            return
        }

        document.addEventListener('click', this.onClick)
    }

    private readonly onClick = (event: Event): void => {
        if (!(event.target instanceof Element)) {
            return
        }

        const button = event.target.closest<HTMLButtonElement>(SELECTOR)

        if (!button || button.disabled || this.pending.has(button)) {
            return
        }

        event.preventDefault()
        void this.copy(button)
    }

    private async copy(button: HTMLButtonElement): Promise<void> {
        this.pending.add(button)
        this.setStatus(button, '')

        try {
            if (!navigator.clipboard?.writeText) {
                throw new Error('Clipboard API is unavailable.')
            }

            await navigator.clipboard.writeText(this.getText(button))
            this.showSuccess(button)
        } catch {
            const message = button.dataset.copyErrorMessage ?? 'Copy failed.'

            this.resetFeedback(button)
            this.setStatus(button, message)
            notifications.addErrorMessages([message])
        } finally {
            this.pending.delete(button)
        }
    }

    private showSuccess(button: HTMLButtonElement): void {
        const previous = this.feedback.get(button)

        if (previous) {
            clearTimeout(previous.timeoutId)
        }

        const icon = previous?.icon ?? button.querySelector<HTMLElement>('i[aria-hidden="true"]')
        const originalClass = previous?.originalClass ?? icon?.className ?? ''

        if (icon) {
            const spacing = originalClass.split(/\s+/).filter((name) => /^m[se]-/.test(name))
            icon.className = ['fa', 'fa-check', ...spacing].join(' ')
        }

        this.setStatus(button, button.dataset.copySuccessMessage ?? 'Copied to the clipboard.')

        this.feedback.set(button, {
            icon,
            originalClass,
            timeoutId: setTimeout(() => this.resetFeedback(button), FEEDBACK_DURATION),
        })
    }

    private resetFeedback(button: HTMLButtonElement): void {
        const feedback = this.feedback.get(button)

        if (!feedback) {
            return
        }

        clearTimeout(feedback.timeoutId)

        if (feedback.icon) {
            feedback.icon.className = feedback.originalClass
        }

        this.feedback.delete(button)
    }

    private setStatus(button: HTMLButtonElement, message: string): void {
        const status = button.querySelector<HTMLElement>('[role="status"]')

        if (status) {
            status.textContent = message
        }
    }

    private getText(button: HTMLButtonElement): string {
        const text = button.dataset.copyText
        const targetId = button.dataset.copyTarget

        if (text !== undefined && targetId !== undefined) {
            throw new Error('"text" and "target" cannot both be specified.')
        }

        if (text !== undefined) {
            return text
        }

        const target = targetId ? document.getElementById(targetId) : null

        if (!target) {
            throw new Error(`Copy target "${targetId}" was not found.`)
        }

        if (target instanceof HTMLInputElement || target instanceof HTMLTextAreaElement) {
            return target.value
        }

        return target.textContent ?? ''
    }
}