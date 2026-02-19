export type EditorMessage =
    | { type: 'TOGGLE_INLINE_EDIT'; enabled: boolean };

export class iframeBridge {
    constructor() {
        this.init();
    }

    private init(): void {
        window.addEventListener('message', (event: MessageEvent) => {
            if (event.origin !== window.location.origin) {
                return;
            }

            const message = event.data as EditorMessage;

            if (message.type === 'TOGGLE_INLINE_EDIT') {
                this.toggleInlineEdit(message.enabled);
            }
        });
    }

    private toggleInlineEdit(enabled: boolean): void {
        document
            .querySelectorAll<HTMLElement>('.inline-edit-element')
            .forEach(el => {
                el.contentEditable = enabled.toString();
                el.classList.toggle('is-editing', enabled);
            });
    }
}