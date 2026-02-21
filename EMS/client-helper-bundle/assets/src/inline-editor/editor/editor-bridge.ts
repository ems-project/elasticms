export type EditorMessage =
    | { type: 'TOGGLE_INLINE_EDIT'; enabled: boolean };

export class EditorBridge {
    private iframe: HTMLIFrameElement;
    private readonly targetOrigin = window.location.origin;
    private editing = false;

    constructor(iframe: HTMLIFrameElement) {
        this.iframe = iframe;
    }

    public toggleInlineEdit(): void {
        this.editing = !this.editing;

        const message: EditorMessage = {
            type: 'TOGGLE_INLINE_EDIT',
            enabled: this.editing
        };

        this.iframe.contentWindow?.postMessage(
            message,
            this.targetOrigin
        );
    }
}