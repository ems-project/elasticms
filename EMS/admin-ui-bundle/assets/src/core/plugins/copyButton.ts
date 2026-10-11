export default class CopyButton {
    private readonly selector =
        'button[data-copy-text], button[data-copy-target]';

    private readonly pending = new WeakSet<HTMLButtonElement>();

    private readonly feedback = new Map<
        HTMLButtonElement,
        {
            icon: HTMLElement | null;
            originalClass: string;
            timeoutId: ReturnType<typeof setTimeout>;
        }
    >();

    public constructor(
        private readonly root: Document | HTMLElement = document,
    ) {
        this.root.addEventListener('click', this.onClick);
    }

    public destroy(): void {
        this.root.removeEventListener('click', this.onClick);

        for (const button of this.feedback.keys()) {
            this.resetFeedback(button);
        }
    }

    private readonly onClick = (event: Event): void => {
        if (!(event.target instanceof Element)) {
            return;
        }

        const button = event.target.closest<HTMLButtonElement>(
            this.selector,
        );

        if (
            !button
            || !this.root.contains(button)
            || button.disabled
            || this.pending.has(button)
        ) {
            return;
        }
        event.preventDefault();

        void this.copy(button);
    };

    private async copy(button: HTMLButtonElement): Promise<void> {
        this.pending.add(button);
        this.setStatus(button, '');

        try {
            const text = this.getText(button);

            if (!navigator.clipboard?.writeText) {
                throw new Error('Clipboard API is unavailable.');
            }

            await navigator.clipboard.writeText(text);

            this.showSuccess(button);

            button.dispatchEvent(new CustomEvent('cms:copy-success', {
                bubbles: true,
            }));
        } catch (error: unknown) {
            this.resetFeedback(button);

            this.setStatus(
                button,
                button.dataset.copyErrorMessage ?? 'Copy failed.',
            );

            button.dispatchEvent(new CustomEvent('cms:copy-error', {
                bubbles: true,
                detail: { error },
            }));
        } finally {
            this.pending.delete(button);
        }
    }

    private showSuccess(button: HTMLButtonElement): void {
        const previous = this.feedback.get(button);

        if (previous) {
            clearTimeout(previous.timeoutId);
        }

        const icon = previous
            ? previous.icon
            : button.querySelector<HTMLElement>('i[aria-hidden="true"]');

        const originalClass = previous
            ? previous.originalClass
            : icon?.className ?? '';

        if (icon) {
            const spacingClasses = originalClass
                .split(/\s+/)
                .filter((className) => /^m[se]-/.test(className));

            icon.className = ['fa', 'fa-check', ...spacingClasses].join(' ');
        }

        this.setStatus(
            button,
            button.dataset.copySuccessMessage ?? 'Copied to the clipboard.',
        );

        const timeoutId = setTimeout(() => {
            this.resetFeedback(button);
        }, 2000);

        this.feedback.set(button, {
            icon,
            originalClass,
            timeoutId,
        });
    }

    private resetFeedback(button: HTMLButtonElement): void {
        const feedback = this.feedback.get(button);

        if (!feedback) {
            return;
        }

        clearTimeout(feedback.timeoutId);

        if (feedback.icon) {
            feedback.icon.className = feedback.originalClass;
        }

        this.feedback.delete(button);
    }

    private setStatus(button: HTMLButtonElement, message: string): void {
        const status = button.querySelector<HTMLElement>('[role="status"]');

        if (status) {
            status.textContent = message;
        }
    }

    private getText(button: HTMLButtonElement): string {
        const text = button.getAttribute('data-copy-text');
        const targetId = button.getAttribute('data-copy-target');

        if (text !== null && targetId !== null) {
            throw new Error(
                '"text" and "target" cannot both be specified.',
            );
        }

        if (text !== null) {
            return text;
        }

        if (!targetId) {
            throw new Error(
                'Either "text" or "target" must be specified.',
            );
        }

        const target = button.ownerDocument.getElementById(targetId);

        if (!target) {
            throw new Error(`Copy target "${targetId}" was not found.`);
        }

        if (
            target instanceof HTMLInputElement
            || target instanceof HTMLTextAreaElement
        ) {
            return target.value;
        }

        return target.textContent ?? '';
    }
}