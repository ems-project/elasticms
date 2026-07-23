import { Mark, mergeAttributes, markPasteRule, InputRule } from '@tiptap/core'
import IconLink from '@tabler/icons/outline/link.svg?raw'
import IconLinkOff from '@tabler/icons/outline/link-off.svg?raw'
import { TiptapModule } from '../types.ts'
import { TiptapEditor } from '../editor.ts'
import { escapeHtml } from '../helper.ts'
import { TranslationKey } from '../translations.ts'
import { Plugin, PluginKey } from '@tiptap/pm/state'
import { createSearchLink } from '../ui/searchLink.ts'
import { UrlType } from '../../wysiwyg/wysiwyg.ts'

const URL_TYPE_OPTIONS: { value: UrlType; label: TranslationKey }[] = [
    { value: 'url', label: 'link_type_url' },
    { value: 'anchor', label: 'link_type_anchor' },
    { value: 'email', label: 'link_type_email' },
    { value: 'phone', label: 'link_type_phone' },
    { value: 'localPage', label: 'link_type_internal' }
]

export const linkModule: TiptapModule = {
    extensions: (e) => getLinkExtension(e),
    toolbar: {
        group: 'links',
        items: [
            {
                name: 'Link',
                icon: IconLink,
                tooltip: 'link_tooltip',
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
        ]
    },
    contextMenu: {
        node: 'link',
        order: 2,
        items: [
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
                command: (e) =>
                    e.tiptap.chain().focus().extendMarkRange('link').unsetMark('link').run()
            }
        ]
    }
}

function getLinkExtension(e: TiptapEditor) {
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
            },
            addPasteRules() {
                return [
                    markPasteRule({
                        find: /https?:\/\/[^\s]+/g,
                        type: this.type,
                        getAttributes: (match) => ({ href: match[0] })
                    }),
                    markPasteRule({
                        find: /(?<![:/])\bwww\.[^\s]+/g,
                        type: this.type,
                        getAttributes: (match) => ({ href: `https://${match[0]}` })
                    })
                ]
            },
            addInputRules() {
                return [
                    new InputRule({
                        find: /(https?:\/\/[^\s]+|www\.[^\s]+)\s$/,
                        handler: ({ state, range, match }) => {
                            const captured = match[1]
                            const start = range.from
                            const end = start + captured.length
                            const href = captured.startsWith('www.')
                                ? `https://${captured}`
                                : captured
                            const tr = state.tr.addMark(start, end, this.type.create({ href }))
                            tr.removeStoredMark(this.type)
                        }
                    })
                ]
            },
            addKeyboardShortcuts() {
                return {
                    'Mod-l': () => {
                        openLinkDialog(e)
                        return true
                    },
                    Enter: () => {
                        autoLinkBeforeCursor(e)
                        return false
                    }
                }
            },
            addProseMirrorPlugins() {
                return [
                    new Plugin({
                        key: new PluginKey('linkDoubleClick'),
                        props: {
                            ['handleDoubleClick']: (view, pos) => {
                                const marks = view.state.doc.resolve(pos).marks()
                                if (!marks.some((m) => m.type.name === 'link')) return false
                                openLinkDialog(e)
                                return true
                            }
                        }
                    }),
                    new Plugin({
                        key: new PluginKey('linkPasteOverSelection'),
                        props: {
                            handleDOMEvents: {
                                paste: (view, event) => {
                                    const { selection } = view.state
                                    if (selection.empty) return false
                                    const text = event.clipboardData?.getData('text/plain')?.trim()
                                    if (!text) return false
                                    const match = /^(https?:\/\/[^\s]+|www\.[^\s]+)$/.exec(text)
                                    if (!match) return false
                                    event.preventDefault()
                                    const href = match[1].startsWith('www.')
                                        ? `https://${match[1]}`
                                        : match[1]
                                    view.dispatch(
                                        view.state.tr.addMark(
                                            selection.from,
                                            selection.to,
                                            this.type.create({ href })
                                        )
                                    )
                                    return true
                                }
                            }
                        }
                    })
                ]
            }
        })
    ]
}

function autoLinkBeforeCursor(e: TiptapEditor) {
    const { state, view } = e.tiptap
    const { $from } = state.selection
    const textBefore = $from.parent.textBetween(
        Math.max(0, $from.parentOffset - 500),
        $from.parentOffset,
        undefined,
        '\ufffc'
    )
    const match = /(?:^|\s)((?:https?:\/\/|www\.)[^\s]+)$/.exec(textBefore)
    if (!match) return

    const linkType = state.schema.marks.link
    if (!linkType) return

    const captured = match[1]
    const end = $from.pos
    const start = end - captured.length
    const href = captured.startsWith('www.') ? `https://${captured}` : captured

    const tr = state.tr.addMark(start, end, linkType.create({ href }))
    tr.removeStoredMark(linkType)
    view.dispatch(tr)
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
    localPageId: string
}

type LinkResult = { href: string; target: string | null; text?: string }

function getLinkContext(e: TiptapEditor): LinkContext {
    const attrs = e.tiptap.getAttributes('link')
    const href = attrs?.href ?? ''
    const target = attrs?.target ?? ''
    const empty = { anchor: '', email: '', subject: '', body: '', phone: '', localPageId: '' }

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
    if (href.startsWith('ems://object:')) {
        return {
            ...empty,
            type: 'localPage',
            href,
            target,
            localPageId: href.replace('ems://object:', '')
        }
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

function buildTypeSection(e: TiptapEditor, ctx: LinkContext, urlTypes: UrlType[]) {
    const options = urlTypes
        .map((t) => URL_TYPE_OPTIONS.find((o) => o.value === t)!)
        .map(
            (o) =>
                `<option value="${o.value}"${ctx.type === o.value ? ' selected' : ''}>${e.trans(o.label)}</option>`
        )
        .join('')

    return `<div>
        <label for="link-type">${e.trans('link_type')}</label>
        <select id="link-type">${options}</select>
    </div>`
}

function buildUrlFields(e: TiptapEditor, ctx: LinkContext) {
    const url = ctx.type === 'url' ? ctx.href : ''
    return `<div id="link-fields-url" style="display: flex; flex-direction: column; gap: 10px;">
        <div>
            <label for="link-url">${e.trans('link_url')} <span style="color: red">*</span></label>
            <input type="text" id="link-url" value="${escapeHtml(url)}" required>
        </div>
        <div>
            <label for="link-target">${e.trans('link_target')}</label>
            <select id="link-target">
                <option value=""${!ctx.target ? ' selected' : ''}>${e.trans('select')}</option>
                <option value="_blank"${ctx.target === '_blank' ? ' selected' : ''}>${e.trans('link_target_new_window')}</option>
                <option value="_self"${ctx.target === '_self' ? ' selected' : ''}>${e.trans('link_target_same_window')}</option>
            </select>
        </div>
    </div>`
}

function buildAnchorFields(e: TiptapEditor, ctx: LinkContext, anchors: string[]) {
    if (!anchors.length) {
        return `<div id="link-fields-anchor" style="display: none;">
            <p style="color: #888; margin: 0;">${e.trans('link_no_anchors')}</p>
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
            <label for="link-anchor">${e.trans('link_anchor_select')}</label>
            <select id="link-anchor">
                <option value="">${e.trans('select')}</option>
                ${options}
            </select>
        </div>
    </div>`
}

function buildEmailFields(e: TiptapEditor, ctx: LinkContext) {
    return `<div id="link-fields-email" style="display: none; flex-direction: column; gap: 10px;">
        <div>
            <label for="link-email">${e.trans('link_email_address')} <span style="color: red">*</span></label>
            <input type="email" id="link-email" value="${escapeHtml(ctx.email)}" required>
        </div>
        <div>
            <label for="link-subject">${e.trans('link_email_subject')}</label>
            <input type="text" id="link-subject" value="${escapeHtml(ctx.subject)}">
        </div>
        <div>
            <label for="link-body">${e.trans('link_email_body')}</label>
            <textarea id="link-body" rows="3">${escapeHtml(ctx.body)}</textarea>
        </div>
    </div>`
}

function buildPhoneFields(e: TiptapEditor, ctx: LinkContext) {
    return `<div id="link-fields-phone" style="display: none; flex-direction: column; gap: 10px;">
        <div>
            <label for="link-phone">${e.trans('link_phone_number')} <span style="color: red">*</span></label>
            <input type="tel" id="link-phone" value="${escapeHtml(ctx.phone)}" required>
        </div>
    </div>`
}

function buildLocalPageWrapper(
    e: TiptapEditor,
    ctx: LinkContext
): {
    html: string
    mount: (root: HTMLElement) => {
        getId: () => string | null
        getLabel: () => string | null
    }
} {
    const currentType = ctx.localPageId.split(':')[0]
    const typeOptions = e.profile.linkTypes
        .map(
            ([label, value]) =>
                `<option value="${value}"${value === currentType ? ' selected' : ''}>${label}</option>`
        )
        .join('')

    return {
        html: `<div id="link-fields-localPage" style="display: none; flex-direction: column; gap: 10px;">
            ${
                e.profile.linkTypes.length > 1
                    ? `<div>
                        <label for="link-localPage-type">${e.trans('link_internal_content_type')}</label>
                        <select id="link-localPage-type">${typeOptions}</select>
                    </div>`
                    : ''
            }
            <div id="link-localPage-search"></div>
            <div>
                <label for="link-target-localPage">${e.trans('link_target')}</label>
                <select id="link-target-localPage">
                    <option value=""${!ctx.target ? ' selected' : ''}>${e.trans('select')}</option>
                    <option value="_blank"${ctx.target === '_blank' ? ' selected' : ''}>${e.trans('link_target_new_window')}</option>
                    <option value="_self"${ctx.target === '_self' ? ' selected' : ''}>${e.trans('link_target_same_window')}</option>
                </select>
            </div>
        </div>`,
        mount(root: HTMLElement) {
            const wrapper = root.querySelector<HTMLElement>('#link-localPage-search')!
            const typeSelect = root.querySelector<HTMLSelectElement>('#link-localPage-type')
            const targetSelect = root.querySelector<HTMLSelectElement>('#link-target-localPage')!
            let selectedId: string | null = ctx.localPageId || null
            let selectedLabel: string | null = null

            const search = createSearchLink<{ type: string }>({
                searchUrl: e.profile.config.searchUrl ?? '',
                searchLabel: e.trans('link_internal_search'),
                searchPlaceholder: e.trans('link_internal_search_placeholder'),
                noResultsLabel: e.trans('link_internal_no_results'),
                initialId: ctx.localPageId || undefined,
                extraParams: currentType ? { type: currentType } : {},
                onChange: (value) => {
                    selectedId = value?.id ?? null
                    selectedLabel = value?.title ?? null
                }
            })
            wrapper.appendChild(search.element)

            typeSelect?.addEventListener('change', () => {
                targetSelect.value = e.profile.isUrlTargetDefaultBlank(typeSelect.value)
                    ? '_blank'
                    : ''
                search.setExtraParams(typeSelect.value ? { type: typeSelect.value } : {})
                search.clear()
            })

            return { getId: () => selectedId, getLabel: () => selectedLabel }
        }
    }
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
    },
    localPage: () => null
}

function showFields(root: HTMLElement, urlTypes: UrlType[], type: UrlType) {
    urlTypes.forEach((t) => {
        const el = root.querySelector<HTMLElement>(`#link-fields-${t}`)
        if (el) el.style.display = t === type ? 'flex' : 'none'
    })

    const urlInput = root.querySelector<HTMLInputElement>('#link-url')
    if (urlInput) {
        urlInput.addEventListener('blur', () => {
            const val = urlInput.value.trim()
            if (
                val &&
                !val.match(/^https?:\/\//i) &&
                !val.startsWith('/') &&
                !val.startsWith('#')
            ) {
                urlInput.value = `https://${val}`
            }
        })
    }
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
                text: result.text ?? result.href,
                marks: [{ type: 'link', attrs: { href: result.href, target: result.target } }]
            })
            .run()
        return
    }
    chain.setTextSelection({ from, to }).setMark('link', result).run()
}

function openLinkDialog(e: TiptapEditor) {
    const { from, to } = e.tiptap.state.selection
    const isEdit = e.tiptap.isActive('link')
    const ctx = getLinkContext(e)
    const anchors = getAnchorsFromDoc(e)

    const urlTypes = e.profile.urlTypes
    if (!isEdit || !urlTypes.includes(ctx.type)) {
        ctx.type = urlTypes[0]
    }
    if (!isEdit && !ctx.target && e.profile.isUrlTargetDefaultBlank(ctx.type)) {
        ctx.target = '_blank'
    }

    const localPageWrapper = urlTypes.includes('localPage') ? buildLocalPageWrapper(e, ctx) : null

    const FIELD_BUILDERS: Record<UrlType, () => string> = {
        url: () => buildUrlFields(e, ctx),
        anchor: () => buildAnchorFields(e, ctx, anchors),
        email: () => buildEmailFields(e, ctx),
        phone: () => buildPhoneFields(e, ctx),
        localPage: () => localPageWrapper?.html ?? ''
    }

    const dialog = e.createDialog('link', { resizable: true, minWidth: 400 })
    dialog.setContent(
        `<div style="display: flex; flex-direction: column; gap: 10px;">
            ${buildTypeSection(e, ctx, urlTypes)}
            ${urlTypes.map((t) => FIELD_BUILDERS[t]()).join('')}
        </div>`
    )

    const root = dialog.element
    const localPageSearch = localPageWrapper ? localPageWrapper.mount(root) : null

    const apply = () => {
        const type = root.querySelector<HTMLSelectElement>('#link-type')!.value as UrlType
        let result: LinkResult | null
        if (type === 'localPage') {
            const id = localPageSearch?.getId() ?? null
            if (!id) return
            const target =
                root.querySelector<HTMLSelectElement>('#link-target-localPage')!.value || null
            result = {
                href: `ems://object:${id}`,
                target,
                text: localPageSearch?.getLabel() ?? undefined
            }
        } else {
            result = HREF_BUILDERS[type]?.(root) ?? null
        }
        if (!result) return
        applyLink(e, result, isEdit, from, to)
        dialog.close()
    }

    dialog
        .addButton({ label: e.trans('button_apply'), variant: 'primary', onClick: apply })
        .addButton({
            label: e.trans('button_cancel'),
            variant: 'secondary',
            onClick: (d) => d.close()
        })
        .open()

    showFields(root, urlTypes, ctx.type)

    const typeSelect = root.querySelector<HTMLSelectElement>('#link-type')!
    typeSelect.addEventListener('change', () =>
        showFields(root, urlTypes, typeSelect.value as UrlType)
    )
}
