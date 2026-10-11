export default class CopyButton {
    private readonly selector =
        'button[data-copy-text], button[data-copy-target]';

    private readonly pending = new WeakSet<HTMLButtonElement>();

    public constructor(
        private readonly root: Document | HTMLElement = document,
    ) {
        this.root.addEventListener('click', this.onClick);
    }

    public destroy(): void {
        this.root.removeEventListener('click', this.onClick);
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

        try {
            const text = this.getText(button);

            if (!navigator.clipboard?.writeText) {
                throw new Error('Clipboard API is unavailable.');
            }

            await navigator.clipboard.writeText(text);

            button.dispatchEvent(new CustomEvent('cms:copy-success', {
                bubbles: true,
            }));
        } catch (error: unknown) {
            button.dispatchEvent(new CustomEvent('cms:copy-error', {
                bubbles: true,
                detail: { error },
            }));
        } finally {
            this.pending.delete(button);
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