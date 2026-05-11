import { Mark, mergeAttributes } from '@tiptap/core'
import IconLink from '@tabler/icons/outline/link.svg?raw'
import IconLinkOff from '@tabler/icons/outline/link-off.svg?raw'
import { TiptapModule } from '../types.ts'
import { Dialog } from '../../dialog.ts'
import { TiptapEditor } from '../editor.ts'

export const linkModule: TiptapModule = {
    extensions: getLinkExtension(),
    toolbarGroup: 'links',
    toolbar: [
        {
            name: 'Link',
            icon: IconLink,
            tooltip: 'Link',
            order: 1,
            command: (e) => openLinkDialog(e),
            isActive: (e) => e.tiptap.isActive('link')
        },
        {
            name: 'Unlink',
            icon: IconLinkOff,
            tooltip: 'Unlink',
            order: 2,
            command: (e) =>
                e.tiptap.chain().focus().extendMarkRange('link').unsetMark('link').run(),
            isActive: () => false,
            isDisabled: (e) => !e.tiptap.isActive('link')
        }
    ],
    contextMenuNode: 'link',
    contextMenu: [
        {
            label: 'Edit Link',
            icon: IconLink,
            order: 0,
            command: (e) => openLinkDialog(e)
        },
        {
            label: 'Unlink',
            icon: IconLinkOff,
            order: 1,
            command: (e) => e.tiptap.chain().focus().extendMarkRange('link').unsetMark('link').run()
        }
    ]
}

function getLinkExtension() {
    return [
        Mark.create({
            name: 'link',
            inclusive: false,
            priority: 1000,

            addAttributes() {
                return {
                    href: { default: null },
                    target: { default: null }
                }
            },

            parseHTML() {
                return [{ tag: 'a[href]' }]
            },

            renderHTML({ HTMLAttributes }) {
                return ['a', mergeAttributes(HTMLAttributes), 0]
            }
        })
    ]
}

interface LinkContext {
    type: string
    href: string
    target: string
    anchor: string
    email: string
    subject: string
    body: string
    phone: string
}

function getLinkContext(e: TiptapEditor): LinkContext {
    const attrs = e.tiptap.getAttributes('link')
    const href = attrs?.href ?? ''
    const target = attrs?.target ?? ''
    const empty = { anchor: '', email: '', subject: '', body: '', phone: '' }

    if (href.startsWith('mailto:')) {
        const clean = href.replace('mailto:', '')
        const [email, query] = clean.split('?')
        const params = new URLSearchParams(query || '')
        return {
            ...empty,
            type: 'email',
            href,
            target,
            email: email || '',
            subject: params.get('subject') || '',
            body: params.get('body') || ''
        }
    }

    if (href.startsWith('tel:')) {
        return { ...empty, type: 'phone', href, target, phone: href.replace('tel:', '') }
    }

    if (href.startsWith('#')) {
        return { ...empty, type: 'anchor', href, target, anchor: href.slice(1) }
    }

    return { ...empty, type: 'url', href, target }
}

function getAnchorsFromDoc(e: TiptapEditor): string[] {
    const anchors: string[] = []
    e.tiptap.state.doc.descendants((node) => {
        node.marks?.forEach((mark) => {
            if (mark.type.name === 'anchor' && mark.attrs.id) anchors.push(mark.attrs.id)
        })
    })
    return [...new Set(anchors)]
}

function esc(v: string) {
    return v.replace(/"/g, '&quot;')
}

function buildTypeSection(ctx: LinkContext) {
    return `<div>
        <label for="link-type">Link Type</label>
        <select id="link-type">
            <option value="url"${ctx.type === 'url' ? ' selected' : ''}>URL</option>
            <option value="anchor"${ctx.type === 'anchor' ? ' selected' : ''}>Link to anchor in the text</option>
            <option value="email"${ctx.type === 'email' ? ' selected' : ''}>E-mail</option>
            <option value="phone"${ctx.type === 'phone' ? ' selected' : ''}>Phone</option>
        </select>
    </div>`
}

function buildUrlFields(ctx: LinkContext) {
    return `<div id="link-fields-url" style="display: flex; flex-direction: column; gap: 10px;">
        <div>
            <label for="link-url">URL <span style="color: red">*</span></label>
            <input type="url" id="link-url" value="${esc(ctx.type === 'url' ? ctx.href : '')}" required>
        </div>
        <div>
            <label for="link-target">Target</label>
            <select id="link-target">
                <option value=""${!ctx.target ? ' selected' : ''}>Not set</option>
                <option value="_blank"${ctx.target === '_blank' ? ' selected' : ''}>New Window (_blank)</option>
                <option value="_self"${ctx.target === '_self' ? ' selected' : ''}>Same Window (_self)</option>
            </select>
        </div>
    </div>`
}

function buildAnchorFields(ctx: LinkContext, anchors: string[]) {
    if (!anchors.length) {
        return `<div id="link-fields-anchor" style="display: none;">
            <p style="color: #888; margin: 0;">No anchors available in the document</p>
        </div>`
    }

    const options = anchors
        .map(
            (a) =>
                `<option value="${esc(a)}"${a === ctx.anchor ? ' selected' : ''}>${esc(a)}</option>`
        )
        .join('')

    return `<div id="link-fields-anchor" style="display: none;">
        <label for="link-anchor">Select Anchor</label>
        <select id="link-anchor">
            <option value="">Select anchor</option>
            ${options}
        </select>
    </div>`
}

function buildEmailFields(ctx: LinkContext) {
    return `<div id="link-fields-email" style="display: none; flex-direction: column; gap: 10px;">
        <div>
            <label for="link-email">E-Mail Address <span style="color: red">*</span></label>
            <input type="email" id="link-email" value="${esc(ctx.email)}" required>
        </div>
        <div>
            <label for="link-subject">Message Subject</label>
            <input type="text" id="link-subject" value="${esc(ctx.subject)}">
        </div>
        <div>
            <label for="link-body">Message Body</label>
            <textarea id="link-body" rows="3">${esc(ctx.body)}</textarea>
        </div>
    </div>`
}

function buildPhoneFields(ctx: LinkContext) {
    return `<div id="link-fields-phone" style="display: none; flex-direction: column; gap: 10px;">
        <div>
            <label for="link-phone">Phone Number <span style="color: red">*</span></label>
            <input type="tel" id="link-phone" value="${esc(ctx.phone)}" required>
        </div>
    </div>`
}

function buildHref(type: string): { href: string; target: string | null } | null {
    if (type === 'url') {
        const input = document.getElementById('link-url') as HTMLInputElement
        if (!input.reportValidity()) return null
        const target = (document.getElementById('link-target') as HTMLSelectElement).value || null
        return { href: input.value.trim(), target }
    }

    if (type === 'anchor') {
        const select = document.getElementById('link-anchor') as HTMLSelectElement
        if (!select?.value) return null
        return { href: `#${select.value}`, target: null }
    }

    if (type === 'email') {
        const input = document.getElementById('link-email') as HTMLInputElement
        if (!input.reportValidity()) return null
        const subject = (document.getElementById('link-subject') as HTMLInputElement).value.trim()
        const body = (document.getElementById('link-body') as HTMLTextAreaElement).value.trim()
        const params = new URLSearchParams()
        if (subject) params.set('subject', subject)
        if (body) params.set('body', body)
        const qs = params.toString()
        return { href: `mailto:${input.value.trim()}${qs ? '?' + qs : ''}`, target: null }
    }

    if (type === 'phone') {
        const input = document.getElementById('link-phone') as HTMLInputElement
        if (!input.reportValidity()) return null
        return { href: `tel:${input.value.trim()}`, target: null }
    }

    return null
}

function showFields(type: string) {
    ;['url', 'anchor', 'email', 'phone'].forEach((t) => {
        const el = document.getElementById(`link-fields-${t}`)
        if (el) el.style.display = t === type ? 'flex' : 'none'
    })
}

function openLinkDialog(e: TiptapEditor) {
    const dialog = new Dialog('Link', { draggable: true })

    const { from, to } = e.tiptap.state.selection
    const isEdit = e.tiptap.isActive('link')
    const ctx = getLinkContext(e)
    const anchors = getAnchorsFromDoc(e)

    dialog.setContent(
        `<div style="display: flex; flex-direction: column; gap: 10px; width: 400px;">
            ${buildTypeSection(ctx)}
            ${buildUrlFields(ctx)}
            ${buildAnchorFields(ctx, anchors)}
            ${buildEmailFields(ctx)}
            ${buildPhoneFields(ctx)}
        </div>`
    )

    const apply = () => {
        const type = (document.getElementById('link-type') as HTMLSelectElement).value
        const result = buildHref(type)
        if (!result) return
        const chain = e.tiptap.chain().focus()
        if (isEdit) {
            chain.extendMarkRange('link').setMark('link', result).run()
        } else if (from === to) {
            chain
                .insertContent({
                    type: 'text',
                    text: result.href,
                    marks: [{ type: 'link', attrs: result }]
                })
                .run()
        } else {
            chain.setTextSelection({ from, to }).setMark('link', result).run()
        }
        dialog.close()
    }

    dialog
        .addButton({ label: 'Apply', variant: 'primary', onClick: () => apply() })
        .addButton({ label: 'Cancel', variant: 'secondary', onClick: (d) => d.close() })
        .open()

    showFields(ctx.type)

    const typeSelect = document.getElementById('link-type') as HTMLSelectElement
    if (typeSelect) typeSelect.addEventListener('change', () => showFields(typeSelect.value))
}
