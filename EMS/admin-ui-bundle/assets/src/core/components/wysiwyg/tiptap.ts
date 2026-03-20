import {EditorRevisionOptions} from './../../helpers/editorRevisionOptions.ts'
import {EditorProfile} from './../../helpers/editorProfile.ts'

import { Editor } from '@tiptap/core'
import StarterKit from '@tiptap/starter-kit'
import './../../../../css/core/components/wysiwyg.scss'

interface ConfigGroup {
    name: string;
    groups: string[];
}

export interface ToolbarAction {
    label: string;
    command: (editor: Editor) => void;
    isActive: (editor: Editor) => boolean;
}

export class Tiptap {
    element: HTMLTextAreaElement
    options: EditorRevisionOptions | null
    profile: EditorProfile
    iframe: HTMLIFrameElement | null = null
    innerEditor: Editor | null = null

    config: ConfigGroup[];

    constructor(
        element: HTMLTextAreaElement,
        options: EditorRevisionOptions | null,
        profile: EditorProfile
    ) {
        this.element = element

        this.options = options
        this.profile = profile

        this.config = [
            { name: 'clipboard', groups: ['undo', 'redo'] },
            { name: 'basicstyles', groups: ['bold', 'italic'] }
        ];

        this.initIframe()
        this.initToolbar()
    }

    private initToolbar() {
        const toolbar = document.createElement('div');
        toolbar.className = 'wysiwyg-toolbar';

        this.config.forEach(configGroup => {
            const groupDiv = document.createElement('div');
            groupDiv.className = `wysiwyg-toolbar-group-${configGroup.name}`;

            configGroup.groups.forEach(actionKey => {
                const action = ActionRegistry[actionKey];

                if (action) {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.innerHTML = action.label;
                    btn.dataset.action = actionKey;

                    btn.onclick = (event) => {
                        event.preventDefault();
                        event.stopPropagation();

                        if (this.innerEditor) {
                            action.command(this.innerEditor);
                            this.updateToolbarUI(this.innerEditor);
                        }
                    };

                    groupDiv.appendChild(btn);
                } else {
                    console.warn(`Actie "${actionKey}" niet gevonden in ActionRegistry.`);
                }
            });

            toolbar.appendChild(groupDiv);
        });

        this.element.parentNode?.insertBefore(toolbar, this.element.nextSibling);
    }

    public updateToolbarUI(editor: Editor) {
        const toolbar = this.element.nextSibling as HTMLElement;

        this.config.forEach(configGroup => {
            configGroup.groups.forEach(actionKey => {
                const action = ActionRegistry[actionKey];
                const btn = toolbar.querySelector(`button[data-action="${actionKey}"]`) as HTMLButtonElement;

                if (btn && action) {
                    const active = action.isActive(editor);
                    btn.classList.toggle('is-active', active);
                }
            });
        });
    }

    private initIframe() {
        this.element.style.display = 'none';

        this.iframe = document.createElement('iframe');
        this.iframe.className = 'wysiwyg-iframe'

        this.element.parentNode?.insertBefore(this.iframe, this.element.nextSibling);

        const doc = this.iframe.contentDocument;
        if (!doc) return;

        const style = doc.createElement('style');
        style.textContent = `
        html, body{
            height: auto; 
            margin: 0; 
            padding: 0; 
        }
        body{
            padding: 15px; 
            font-family: sans-serif; 
            cursor: text; 
        }
        .ProseMirror{
            min-height: 100%; 
            outline: none; 
        }
    `;
        doc.head.appendChild(style);

        this.setupEditorInsideIframe(doc.body);
    }

    private setupEditorInsideIframe(mountElement: HTMLElement) {
        if (!this.iframe?.contentWindow) return

        const doc = this.iframe.contentDocument
        if (!doc) return;

        this.innerEditor = new Editor({
           element: mountElement,
            extensions: [
                StarterKit,
            ],
           content: this.element.value,
            onUpdate: ({ editor }) => {
                this.element.value = editor.getHTML();
                this.updateToolbarUI(editor);
            },
            onSelectionUpdate: ({ editor }) => {
                this.updateToolbarUI(editor);
            }
        })
    }
}

const ActionRegistry: Record<string, ToolbarAction> = {
    bold: {
        label: '<i class="fa-solid fa-bold"></i>',
        command: (e) => e.chain().focus().toggleBold().run(),
        isActive: (e) => e.isActive('bold')
    },
    italic: {
        label: '<i class="fa-solid fa-italic"></i>',
        command: (e) => e.chain().focus().toggleItalic().run(),
        isActive: (e) => e.isActive('italic')
    },
    undo: {
        label: '<i class="fa-solid fa-rotate-left"></i>',
        command: (e) => e.chain().focus().undo().run(),
        isActive: () => false
    },
    redo: {
        label: '<i class="fa-solid fa-rotate-right"></i>',
        command: (e) => e.chain().focus().redo().run(),
        isActive: () => false
    }
};