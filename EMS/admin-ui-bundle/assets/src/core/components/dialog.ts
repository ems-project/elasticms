interface DialogButton {
    label: string;
    className: string;
    onClick: (dialog: Dialog) => void;
}

export class Dialog {
    private dialog: HTMLDialogElement;
    private body: HTMLElement;
    private footer: HTMLElement;

    constructor(title: string) {
        this.dialog = document.createElement('dialog');
        this.dialog.className = 'tiptap-dialog-native';

        this.dialog.innerHTML = `
            <div class="dialog-content">
                <div class="dialog-header">
                    <h4 class="dialog-title">${title}</h4>
                </div>
                <div class="dialog-body"></div>
                <div class="dialog-footer"></div>
            </div>
        `;

        this.body = this.dialog.querySelector('.dialog-body')!;
        this.footer = this.dialog.querySelector('.dialog-footer')!;

        this.dialog.addEventListener('close', () => this.dialog.remove());

        document.body.appendChild(this.dialog);
    }

    setContent(html: string | HTMLElement): this {
        if (typeof html === 'string') {
            this.body.innerHTML = html;
        } else {
            this.body.appendChild(html);
        }
        return this;
    }

    addButton({ label, className, onClick }: DialogButton): this {
        const btn = document.createElement('button');
        btn.innerText = label;
        btn.className = `btn ${className}`;
        btn.type = 'button';
        btn.style.marginLeft = '8px';
        btn.onclick = (e) => {
            e.preventDefault();
            onClick(this);
        };
        this.footer.appendChild(btn);
        return this;
    }

    open(): void {
        this.dialog.showModal();
    }

    close(): void {
        this.dialog.close();
    }

    getFieldValue(id: string): string {
        const el = this.dialog.querySelector(`#${id}`) as HTMLInputElement;
        return el ? el.value : '';
    }
}