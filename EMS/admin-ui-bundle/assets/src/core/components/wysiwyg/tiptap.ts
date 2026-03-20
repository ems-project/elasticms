import {EditorRevisionOptions} from './../../helpers/editorRevisionOptions.ts'
import {EditorProfile} from './../../helpers/editorProfile.ts'

import { Editor } from '@tiptap/core'
import StarterKit from '@tiptap/starter-kit'
import Paragraph from '@tiptap/extension-paragraph'
import Underline from '@tiptap/extension-underline'
import TextAlign from '@tiptap/extension-text-align'

import './../../../../css/core/components/wysiwyg.scss'

interface ConfigGroup {
    name: string;
    groups: string[];
}

export interface ToolbarAction {
    label: string;
    tooltip?: string;
    command?: (editor: Editor) => void;
    isActive: (editor: Editor) => boolean;
}

export class Tiptap {
    isSourceView: boolean = false;
    isMaximized: boolean = false;

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

        const container = this.element.parentElement;
        if (container) {
            container.insertBefore(toolbar, container.firstChild);
        }
    }

    private createButton(key: string, action: ToolbarAction): HTMLButtonElement {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.innerHTML = action.label;
        btn.dataset.action = key;

        if (action.tooltip) {
            btn.title = action.tooltip;
        }

        btn.onclick = (event) => {
            event.preventDefault();
            event.stopPropagation();

            if (btn.dataset.action === 'source') {
                this.toggleSourceView();
            }
            else if (btn.dataset.action === 'maximize') {
                this.toggleMaximize();
            }
            else if (this.innerEditor && !this.isSourceView && action.command) {
                action.command(this.innerEditor);
                this.updateToolbarUI(this.innerEditor);
            }
        };
        return btn;
    }

    public updateToolbarUI(editor: Editor) {
        const container = this.element.parentElement;
        const toolbar = container?.querySelector('.wysiwyg-toolbar') as HTMLElement;

        if (!toolbar) return;

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
        const container = document.createElement('div');
        container.className = 'wysiwyg-container';
        this.element.parentNode?.insertBefore(container, this.element);

        container.appendChild(this.element);
        this.element.className = 'wysiwyg-source-view';

        this.iframe = document.createElement('iframe');
        this.iframe.className = 'wysiwyg-iframe';
        container.appendChild(this.iframe);

        const doc = this.iframe.contentDocument;
        if (doc) {
            const style = doc.createElement('style');
            style.textContent = `
                body { padding: 15px; font-family: sans-serif; }
                .ProseMirror { outline: none; min-height: 100%; }
                .ProseMirror p { margin-bottom: 1em; line-height: 1.5; }
            `;
            doc.head.appendChild(style);
            this.setupEditorInsideIframe(doc.body);
        }
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
                Underline,
                TextAlign.configure({
                    types: ['heading', 'paragraph'],
                    alignments: ['left', 'center', 'right', 'justify'],
                    defaultAlignment: 'left',
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
            },
            onTransaction: ({ editor }) => {
                this.updateToolbarUI(editor);
            }
        })
    }

    private toggleSourceView() {
        this.isSourceView = !this.isSourceView;
        const container = this.element.parentElement;
        const toolbar = container?.querySelector('.wysiwyg-toolbar') as HTMLElement;
        const sourceBtn = toolbar?.querySelector('[data-action="source"]');

        if (container) {
            container.classList.toggle('is-source-mode', this.isSourceView);
            if (this.isSourceView) {
                this.element.value = this.innerEditor?.getHTML() || '';
                sourceBtn?.classList.add('is-active');
                this.setToolbarDisabled(toolbar, true);
            } else {
                this.innerEditor?.commands.setContent(this.element.value);
                sourceBtn?.classList.remove('is-active');
                this.setToolbarDisabled(toolbar, false);
            }
        }
    }

    private toggleMaximize() {
        this.isMaximized = !this.isMaximized;
        const container = this.element.parentElement;
        const btn = container?.querySelector('[data-action="maximize"]') as HTMLElement;

        if (container) {
            container.classList.toggle('is-maximized', this.isMaximized);
            btn.innerHTML = this.isMaximized
                ? '<i class="fa-solid fa-compress"></i>'
                : '<i class="fa-solid fa-expand"></i>';
            document.body.style.overflow = this.isMaximized ? 'hidden' : '';
        }
    }

    private setToolbarDisabled(toolbar: HTMLElement, disabled: boolean) {
        const buttons = toolbar.querySelectorAll('button:not([data-action="source"])');
        buttons.forEach(btn => {
            const b = btn as HTMLButtonElement;
            b.disabled = disabled;
            b.style.opacity = disabled ? '0.4' : '1';
            b.style.cursor = disabled ? 'not-allowed' : 'pointer';
        });
    }
}

const GroupRegistry: Record<string, string[]> = {
    'mode': ['source', 'maximize'],
    'undo': ['undo', 'redo'],
    'basicstyles': ['bold', 'italic', 'underline', 'strike'],
    'cleanup': ['clear'],
    'list': ['bulletList', 'orderedList'],
    'indent': ['outdent', 'indent'],
    'align': ['alignLeft', 'alignCenter', 'alignRight', 'alignJustify'],
    'insert': ['horizontalRule']
};

const ActionRegistry: Record<string, ToolbarAction> = {
    source: {
        label: '<i class="fa-solid fa-code"></i>',
        tooltip: 'Source Code',
        isActive: () => false
    },
    maximize: {
        label: '<i class="fa-solid fa-expand"></i>',
        tooltip: 'Maximize / Fullscreen',
        isActive: () => false
    },
    bold: {
        label: '<i class="fa-solid fa-bold"></i>',
        tooltip: 'Bold (Ctrl+B)',
        command: (e) => e.chain().focus().toggleBold().run(),
        isActive: (e) => e.isActive('bold')
    },
    italic: {
        label: '<i class="fa-solid fa-italic"></i>',
        tooltip: 'Italic (Ctrl+I)',
        command: (e) => e.chain().focus().toggleItalic().run(),
        isActive: (e) => e.isActive('italic')
    },
    underline: {
        label: '<i class="fa-solid fa-underline"></i>',
        tooltip: 'Underline (Ctrl+U)',
        command: (e) => e.chain().focus().toggleUnderline().run(),
        isActive: (e) => e.isActive('underline')
    },
    strike: {
        label: '<i class="fa-solid fa-strikethrough"></i>',
        tooltip: 'Strikethrough',
        command: (e) => e.chain().focus().toggleStrike().run(),
        isActive: (e) => e.isActive('strike')
    },
    undo: {
        label: '<i class="fa-solid fa-rotate-left"></i>',
        tooltip: 'Undo (Ctrl+Z)',
        command: (e) => e.chain().focus().undo().run(),
        isActive: () => false
    },
    redo: {
        label: '<i class="fa-solid fa-rotate-right"></i>',
        tooltip: 'Redo (Ctrl+Y)',
        command: (e) => e.chain().focus().redo().run(),
        isActive: () => false
    },
    clear: {
        label: '<i class="fa-solid fa-remove-format"></i>',
        tooltip: 'Remove Formatting',
        command: (e) => e.chain().focus().unsetAllMarks().clearNodes().run(),
        isActive: () => false
    },
    horizontalRule: {
        label: '<i class="fa-solid fa-grip-lines"></i>',
        tooltip: 'Insert Horizontal Line',
        command: (e) => e.chain().focus().setHorizontalRule().run(),
        isActive: () => false
    },
    bulletList: {
        label: '<i class="fa-solid fa-list-ul"></i>',
        tooltip: 'Bullet List',
        command: (e) => e.chain().focus().toggleBulletList().run(),
        isActive: (e) => e.isActive('bulletList')
    },
    orderedList: {
        label: '<i class="fa-solid fa-list-ol"></i>',
        tooltip: 'Numbered List',
        command: (e) => e.chain().focus().toggleOrderedList().run(),
        isActive: (e) => e.isActive('orderedList')
    },
    indent: {
        label: '<i class="fa-solid fa-indent"></i>',
        tooltip: 'Increase Indent',
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
        tooltip: 'Decrease Indent',
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
    },
    alignLeft: {
        label: '<i class="fa-solid fa-align-left"></i>',
        tooltip: 'Align Left',
        command: (e) => e.chain().focus().setTextAlign('left').run(),
        isActive: (e) => e.isActive({ textAlign: 'left' })
    },
    alignCenter: {
        label: '<i class="fa-solid fa-align-center"></i>',
        tooltip: 'Align Center',
        command: (e) => e.chain().focus().setTextAlign('center').run(),
        isActive: (e) => e.isActive({ textAlign: 'center' })
    },
    alignRight: {
        label: '<i class="fa-solid fa-align-right"></i>',
        tooltip: 'Align Right',
        command: (e) => e.chain().focus().setTextAlign('right').run(),
        isActive: (e) => e.isActive({ textAlign: 'right' })
    },
    alignJustify: {
        label: '<i class="fa-solid fa-align-justify"></i>',
        tooltip: 'Justify',
        command: (e) => e.chain().focus().setTextAlign('justify').run(),
        isActive: (e) => e.isActive({ textAlign: 'justify' })
    }
};