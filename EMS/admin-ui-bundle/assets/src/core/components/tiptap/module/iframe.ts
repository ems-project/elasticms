import IconMovie from '@tabler/icons/outline/movie.svg?raw'
import { Node } from '@tiptap/core'
import { TiptapModule } from '../types.ts'
import { TiptapEditor } from '../editor.ts'

export const iframeModule: TiptapModule = {
    extensions: [createIframeNode()],
    isEnabled: (wysiwygProfile) => wysiwygProfile.hasPlugin('iframe'),
    toolbar: {
        group: 'insert',
        items: [
            {
                name: 'Iframe',
                icon: IconMovie,
                tooltip: 'iframe_insert',
                order: 5,
                command: (editor: TiptapEditor) => openIframeDialog(editor),
                isActive: (editor: TiptapEditor) => editor.tiptap.isActive('iframe'),
            },
        ],
    },
    contextMenu: {
        node: 'iframe',
        items: [
            {
                label: 'iframe_edit',
                icon: IconMovie,
                order: 0,
                command: (editor: TiptapEditor) => openIframeDialog(editor),
            },
        ],
    },
}

function createIframeNode() {
    return Node.create({
        name: 'iframe',
        group: 'block',
        atom: true,

        addAttributes() {
            return {
                src: { default: null },
                width: { default: null },
                height: { default: null },
                allow: { default: null },
                allowfullscreen: { default: false },
                title: { default: null },
                frameborder: { default: null },
                referrerpolicy: { default: null },
            }
        },

        parseHTML() {
            return [{ tag: 'iframe[src]' }]
        },

        renderHTML({ HTMLAttributes }) {
            const attrs: Record<string, string> = {}
            for (const [k, v] of Object.entries(HTMLAttributes)) {
                if (v !== null && v !== false && v !== '') {
                    attrs[k] = v === true ? k : String(v)
                }
            }
            return ['iframe', attrs]
        },
    });
}

function parseEmbedCode(html: string): Record<string, string | boolean> | null {
    const doc = new DOMParser().parseFromString(html.trim(), 'text/html')
    const iframe = doc.querySelector('iframe')
    if (!iframe || !iframe.src) return null

    const result: Record<string, string | boolean> = { src: iframe.src }
    if (iframe.width) result.width = iframe.width
    if (iframe.height) result.height = iframe.height
    if (iframe.getAttribute('allow')) result.allow = iframe.getAttribute('allow')!
    if (iframe.getAttribute('title')) result.title = iframe.getAttribute('title')!
    if (iframe.getAttribute('frameborder')) result.frameborder = iframe.getAttribute('frameborder')!
    if (iframe.getAttribute('referrerpolicy')) result.referrerpolicy = iframe.getAttribute('referrerpolicy')!
    result.allowfullscreen = iframe.hasAttribute('allowfullscreen')

    return result
}

function openIframeDialog(editor: TiptapEditor): void {
    const existing = editor.tiptap.getAttributes('iframe')
    const isEdit = !!existing.src

    const dialog = editor.createDialog(
        isEdit ? 'iframe_edit' : 'iframe_insert',
        'tiptap-dialog-iframe'
    )

    const textarea = document.createElement('textarea')
    textarea.className = 'tiptap-iframe-input'
    textarea.placeholder = editor.trans('iframe_placeholder')
    textarea.rows = 5

    if (isEdit) {
        const attrs = existing as Record<string, string | boolean>
        const parts: string[] = []
        if (attrs.width) parts.push(`width="${attrs.width}"`)
        if (attrs.height) parts.push(`height="${attrs.height}"`)
        if (attrs.allow) parts.push(`allow="${attrs.allow}"`)
        if (attrs.title) parts.push(`title="${attrs.title}"`)
        if (attrs.frameborder) parts.push(`frameborder="${attrs.frameborder}"`)
        if (attrs.referrerpolicy) parts.push(`referrerpolicy="${attrs.referrerpolicy}"`)
        if (attrs.allowfullscreen) parts.push('allowfullscreen')
        textarea.value = `<iframe src="${attrs.src}" ${parts.join(' ')}></iframe>`
    }

    const error = document.createElement('p')
    error.className = 'tiptap-iframe-error'
    error.hidden = true
    error.textContent = editor.trans('iframe_invalid')

    dialog.body.appendChild(textarea)
    dialog.body.appendChild(error)

    dialog
        .addButton({
            label: editor.trans('button_cancel'),
            variant: 'secondary',
            onClick: (d) => d.close(),
        })
        .addButton({
            label: editor.trans('button_insert'),
            variant: 'primary',
            onClick: (d) => {
                const parsed = parseEmbedCode(textarea.value)
                if (!parsed) {
                    error.hidden = false
                    return false
                }
                error.hidden = true
                if (isEdit) {
                    editor.tiptap.chain().focus().updateAttributes('iframe', parsed).run()
                } else {
                    editor.tiptap.chain().focus().insertContent({ type: 'iframe', attrs: parsed }).run()
                }
                d.close()
            },
        })
        .open()
}