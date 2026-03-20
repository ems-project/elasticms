import {EditorRevisionOptions} from './../../helpers/editorRevisionOptions.ts'
import {EditorProfile} from './../../helpers/editorProfile.ts'

import { Editor } from '@tiptap/core'
import StarterKit from '@tiptap/starter-kit'
import Paragraph from '@tiptap/extension-paragraph'

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
            {
                name: 'default',
                groups: Object.keys(GroupRegistry)
            }
        ];

        this.initIframe()
        this.initToolbar()
    }

    private initToolbar() {
        const toolbar = document.createElement('div');
        toolbar.className = 'wysiwyg-toolbar';

        this.config.forEach(section => {
            const sectionDiv = document.createElement('div');
            sectionDiv.className = `wysiwyg-toolbar-section`;

            section.groups.forEach(groupName => {
                const groupDiv = document.createElement('div');
                groupDiv.className = 'wysiwyg-toolbar-group';

                const items = GroupRegistry[groupName] || [];
                items.forEach(actionKey => {
                    const action = ActionRegistry[actionKey];
                    if (action) {
                        groupDiv.appendChild(this.createButton(actionKey, action));
                    }
                });

                if (groupDiv.children.length > 0) {
                    sectionDiv.appendChild(groupDiv);
                }
            });

            toolbar.appendChild(sectionDiv);
        });

        this.element.parentNode?.insertBefore(toolbar, this.element.nextSibling);
    }

    private createButton(key: string, action: ToolbarAction): HTMLButtonElement {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.innerHTML = action.label;
        btn.dataset.action = key;
        btn.onclick = (event) => {
            event.preventDefault();
            event.stopPropagation();
            if (this.innerEditor) {
                action.command(this.innerEditor);
                this.updateToolbarUI(this.innerEditor);
            }
        };
        return btn;
    }

    public updateToolbarUI(editor: Editor) {
        const toolbar = this.element.nextSibling as HTMLElement;
        if (!toolbar || !toolbar.classList.contains('wysiwyg-toolbar')) return;

        const buttons = toolbar.querySelectorAll('button[data-action]');
        buttons.forEach(btn => {
            const actionKey = (btn as HTMLElement).dataset.action;
            const action = actionKey ? ActionRegistry[actionKey] : null;

            if (action) {
                const active = action.isActive(editor);
                btn.classList.toggle('is-active', active);
            }
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

        const CustomParagraph = Paragraph.extend({
            addAttributes() {
                return {
                    indent: {
                        default: 0,
                        renderHTML: attributes => {
                            if (attributes.indent === 0) return {}
                            return { style: `margin-left: ${attributes.indent * 20}px` }
                        },
                        parseHTML: element => parseInt(element.style.marginLeft) / 20 || 0,
                    },
                }
            },
        })

        this.innerEditor = new Editor({
           element: mountElement,
            extensions: [
                StarterKit.configure({
                    paragraph: false
                }),
                CustomParagraph,
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

const GroupRegistry: Record<string, string[]> = {
    'undo': ['undo', 'redo'],
    'basicstyles': ['bold', 'italic', 'strike'],
    'cleanup': ['clear'],
    'list': ['bulletList', 'orderedList'],
    'indent': ['outdent', 'indent'],
};

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
    strike: {
        label: '<i class="fa-solid fa-strikethrough"></i>',
        command: (e) => e.chain().focus().toggleStrike().run(),
        isActive: (e) => e.isActive('strike')
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
    },
    clear: {
        label: '<i class="fa-solid fa-remove-format"></i>',
        command: (e) => e.chain().focus().unsetAllMarks().clearNodes().run(),
        isActive: () => false
    },
    bulletList: {
        label: '<i class="fa-solid fa-list-ul"></i>',
        command: (e) => e.chain().focus().toggleBulletList().run(),
        isActive: (e) => e.isActive('bulletList')
    },
    orderedList: {
        label: '<i class="fa-solid fa-list-ol"></i>',
        command: (e) => e.chain().focus().toggleOrderedList().run(),
        isActive: (e) => e.isActive('orderedList')
    },
    indent: {
        label: '<i class="fa-solid fa-indent"></i>',
        command: (e) => {
            return e.chain().focus().command(({ tr, state }) => {
                const { selection } = state;
                tr.doc.nodesBetween(selection.from, selection.to, (node, pos) => {
                    if (node.type.name === 'paragraph' || node.type.name === 'heading') {
                        const currentIndent = node.attrs.indent || 0;
                        tr.setNodeMarkup(pos, undefined, { ...node.attrs, indent: currentIndent + 1 });
                    }
                });
                return true;
            }).run();
        },
        isActive: () => false
    },
    outdent: {
        label: '<i class="fa-solid fa-outdent"></i>',
        command: (e) => {
            return e.chain().focus().command(({ tr, state }) => {
                const { selection } = state;
                tr.doc.nodesBetween(selection.from, selection.to, (node, pos) => {
                    if (node.type.name === 'paragraph' || node.type.name === 'heading') {
                        const currentIndent = node.attrs.indent || 0;
                        if (currentIndent > 0) {
                            tr.setNodeMarkup(pos, undefined, { ...node.attrs, indent: currentIndent - 1 });
                        }
                    }
                });
                return true;
            }).run();
        },
        isActive: () => false
    }
};