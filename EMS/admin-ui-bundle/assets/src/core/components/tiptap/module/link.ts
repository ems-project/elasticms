import { Mark, mergeAttributes } from '@tiptap/core'
import IconLink from '@tabler/icons/outline/link.svg?raw'
import IconLinkOff from '@tabler/icons/outline/link-off.svg?raw'
import { TiptapModule } from '../types.ts'
import { Dialog } from '../../dialog.ts'
import { TiptapEditor } from '../editor.ts'
import { escapeHtml } from '../helper.ts'

const URL_TYPES = ['url', 'anchor', 'email', 'phone'] as const
type UrlType = (typeof URL_TYPES)[number]

const URL_TYPE_OPTIONS: { value: UrlType; label: string }[] = [
    { value: 'url', label: 'URL' },
    { value: 'anchor', label: 'Link to anchor in the text' },
    { value: 'email', label: 'E-mail' },
    { value: 'phone', label: 'Phone' }
]

export const linkModule: TiptapModule = {
    extensions: getLinkExtension(),
    toolbarGroup: 'links',
    toolbar: [
        {
            name: 'Link',
            icon: IconLink,
            tooltip: 'link',
            order: 1,
            command: (e) => openLinkDialog(e),
            isActive: (e) => e.tiptap.isActive('link')
        },
        {
            name: 'Unlink',
            icon: IconLinkOff,
            tooltip: 'link_unlink',
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
            label: 'link_edit',
            icon: IconLink,
            order: 0,
            command: (e) => openLinkDialog(e)
        },
        {
            label: 'link_unlink',
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
                    target: { default: null },
                    class: {
                        default: null,
                        parseHTML: (el) => el.getAttribute('class') || null,
                        renderHTML: (attrs) => (attrs.class ? { class: attrs.class } : {})
                    },
                    style: {
                        default: null,
                        parseHTML: (el) => el.getAttribute('style') || null,
                        renderHTML: (attrs) => (attrs.style ? { style: attrs.style } : {})
                    }
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
    type: UrlType
    href: string
    target: string
    anchor: string
    email: string
    subject: string
    body: string
    phone: string
}

type LinkResult = { href: string; target: string | null }

function getLinkContext(e: TiptapEditor): LinkContext {
    const attrs = e.tiptap.getAttributes('link')
    const href = attrs?.href ?? ''
    const target = attrs?.target ?? ''
    const empty = { anchor: '', email: '', subject: '', body: '', phone: '' }

    if (href.startsWith('mailto:')) {
        const [email, query] = href.replace('mailto:', '').split('?')
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

function buildTypeSection(ctx: LinkContext, urlTypes?: string[]) {
    const options = URL_TYPE_OPTIONS.filter((o) => !urlTypes || urlTypes.includes(o.value))
        .map(
            (o) =>
                `<option value="${o.value}"${ctx.type === o.value ? ' selected' : ''}>${o.label}</option>`
        )
        .join('')

    return `<div>
        <label for="link-type">Link Type</label>
        <select id="link-type">${options}</select>
    </div>`
}

function buildUrlFields(ctx: LinkContext) {
    const url = ctx.type === 'url' ? ctx.href : ''
    return `<div id="link-fields-url" style="display: flex; flex-direction: column; gap: 10px;">
        <div>
            <label for="link-url">URL <span style="color: red">*</span></label>
            <input type="text" id="link-url" value="${escapeHtml(url)}" required>
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
                `<option value="${escapeHtml(a)}"${a === ctx.anchor ? ' selected' : ''}>${escapeHtml(a)}</option>`
        )
        .join('')
    return `<div id="link-fields-anchor" style="display: none; flex-direction: column; gap: 10px;">
        <div>
            <label for="link-anchor">Select Anchor</label>
            <select id="link-anchor">
                <option value="">Select anchor</option>
                ${options}
            </select>
        </div>
    </div>`
}

function buildEmailFields(ctx: LinkContext) {
    return `<div id="link-fields-email" style="display: none; flex-direction: column; gap: 10px;">
        <div>
            <label for="link-email">E-Mail Address <span style="color: red">*</span></label>
            <input type="email" id="link-email" value="${escapeHtml(ctx.email)}" required>
        </div>
        <div>
            <label for="link-subject">Message Subject</label>
            <input type="text" id="link-subject" value="${escapeHtml(ctx.subject)}">
        </div>
        <div>
            <label for="link-body">Message Body</label>
            <textarea id="link-body" rows="3">${escapeHtml(ctx.body)}</textarea>
        </div>
    </div>`
}

function buildPhoneFields(ctx: LinkContext) {
    return `<div id="link-fields-phone" style="display: none; flex-direction: column; gap: 10px;">
        <div>
            <label for="link-phone">Phone Number <span style="color: red">*</span></label>
            <input type="tel" id="link-phone" value="${escapeHtml(ctx.phone)}" required>
        </div>
    </div>`
}

const HREF_BUILDERS: Record<UrlType, (root: HTMLElement) => LinkResult | null> = {
    url: (root) => {
        const input = root.querySelector<HTMLInputElement>('#link-url')!
        if (!input.reportValidity()) return null
        const target = root.querySelector<HTMLSelectElement>('#link-target')!.value || null
        return { href: input.value.trim(), target }
    },
    anchor: (root) => {
        const select = root.querySelector<HTMLSelectElement>('#link-anchor')
        if (!select?.value) return null
        return { href: `#${select.value}`, target: null }
    },
    email: (root) => {
        const input = root.querySelector<HTMLInputElement>('#link-email')!
        if (!input.reportValidity()) return null
        const subject = root.querySelector<HTMLInputElement>('#link-subject')!.value.trim()
        const body = root.querySelector<HTMLTextAreaElement>('#link-body')!.value.trim()
        const params = new URLSearchParams()
        if (subject) params.set('subject', subject)
        if (body) params.set('body', body)
        const qs = params.toString()
        return { href: `mailto:${input.value.trim()}${qs ? '?' + qs : ''}`, target: null }
    },
    phone: (root) => {
        const input = root.querySelector<HTMLInputElement>('#link-phone')!
        if (!input.reportValidity()) return null
        return { href: `tel:${input.value.trim()}`, target: null }
    }
}

function showFields(root: HTMLElement, type: UrlType) {
    URL_TYPES.forEach((t) => {
        const el = root.querySelector<HTMLElement>(`#link-fields-${t}`)
        if (el) el.style.display = t === type ? 'flex' : 'none'
    })
}

function applyLink(e: TiptapEditor, result: LinkResult, isEdit: boolean, from: number, to: number) {
    const chain = e.tiptap.chain().focus()
    if (isEdit) {
        chain.extendMarkRange('link').setMark('link', result).run()
        return
    }
    if (from === to) {
        chain
            .insertContent({
                type: 'text',
                text: result.href,
                marks: [{ type: 'link', attrs: result }]
            })
            .run()
        return
    }
    chain.setTextSelection({ from, to }).setMark('link', result).run()
}

function openLinkDialog(e: TiptapEditor) {
    const urlTypes = e.profile.config.ems?.urlTypes as UrlType[] | undefined
    const urlTargetDefaultBlank = e.profile.config.ems?.urlTargetDefaultBlank
    const { from, to } = e.tiptap.state.selection
    const isEdit = e.tiptap.isActive('link')
    const ctx = getLinkContext(e)
    const anchors = getAnchorsFromDoc(e)

    const availableTypes = urlTypes ?? [...URL_TYPES]
    if (!availableTypes.includes(ctx.type)) ctx.type = availableTypes[0]
    if (!isEdit && !ctx.target && urlTargetDefaultBlank?.includes(ctx.type)) {
        ctx.target = '_blank'
    }

    const dialog = new Dialog('Link', { draggable: true })
    dialog.setContent(
        `<div style="display: flex; flex-direction: column; gap: 10px; width: 400px;">
            ${buildTypeSection(ctx, urlTypes)}
            ${availableTypes.includes('url') ? buildUrlFields(ctx) : ''}
            ${availableTypes.includes('anchor') ? buildAnchorFields(ctx, anchors) : ''}
            ${availableTypes.includes('email') ? buildEmailFields(ctx) : ''}
            ${availableTypes.includes('phone') ? buildPhoneFields(ctx) : ''}
        </div>`
    )

    const root = dialog.element

    const apply = () => {
        const type = root.querySelector<HTMLSelectElement>('#link-type')!.value as UrlType
        const result = HREF_BUILDERS[type]?.(root)
        if (!result) return
        applyLink(e, result, isEdit, from, to)
        dialog.close()
    }

    dialog
        .addButton({ label: 'Apply', variant: 'primary', onClick: apply })
        .addButton({ label: 'Cancel', variant: 'secondary', onClick: (d) => d.close() })
        .open()

    showFields(root, ctx.type)

    const typeSelect = root.querySelector<HTMLSelectElement>('#link-type')!
    typeSelect.addEventListener('change', () => showFields(root, typeSelect.value as UrlType))
}
