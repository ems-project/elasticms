import { TiptapEditor } from '../../Editor.ts'
import { escapeHtml } from '../../Helper.ts'
import { TranslationKey } from '../../Translations.ts'
import { createSearch } from './Search.ts'
import { UrlType } from '../../../Wysiwyg/Wysiwyg.ts'
import { FileUploader } from '../../../FileUploader.ts'
import { getMarkRange } from '@tiptap/core'

export function linkDialog(e: TiptapEditor, defaultTarget: string | null = null) {
    const isEdit = e.tiptap.isActive('link')
    const ctx = getLinkContext(e)
    const anchors = getAnchorsFromDoc(e)

    const selection = e.tiptap.state.selection
    const range = isEdit
        ? (getMarkRange(selection.$from, e.tiptap.schema.marks.link) ?? selection)
        : selection
    const from = range.from
    const to = range.to
    const linkText = isEdit ? e.tiptap.state.doc.textBetween(from, to) : ''

    const urlTypes = e.profile.urlTypes
    if (!isEdit || !urlTypes.includes(ctx.type)) {
        ctx.type = urlTypes[0]
    }
    if (!isEdit && !ctx.target && defaultTarget) {
        ctx.target = defaultTarget
    }

    const localPageWrapper = urlTypes.includes('localPage') ? buildLocalPageWrapper(e, ctx) : null

    const FIELD_BUILDERS: Record<UrlType, () => string> = {
        url: () => buildUrlFields(e, ctx),
        anchor: () => buildAnchorFields(e, ctx, anchors),
        email: () => buildEmailFields(e, ctx),
        phone: () => buildPhoneFields(e, ctx),
        localPage: () => localPageWrapper?.html ?? '',
        fileLink: () => buildFileFields(e, ctx, linkText)
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
    if (urlTypes.includes('fileLink')) wireFileField(e, root)

    const typeSelect = root.querySelector<HTMLSelectElement>('#link-type')!
    typeSelect.addEventListener('change', () =>
        showFields(root, urlTypes, typeSelect.value as UrlType)
    )
}

const URL_TYPE_OPTIONS: { value: UrlType; label: TranslationKey }[] = [
    { value: 'url', label: 'link_type_url' },
    { value: 'anchor', label: 'link_type_anchor' },
    { value: 'email', label: 'link_type_email' },
    { value: 'phone', label: 'link_type_phone' },
    { value: 'localPage', label: 'link_type_internal' },
    { value: 'fileLink', label: 'link_type_file' }
]

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

type LinkResult = { href: string; target: string | null; text?: string; class?: string | null }

function getLinkContext(e: TiptapEditor): LinkContext {
    const attrs = e.tiptap.getAttributes('link')
    const href = attrs?.href ?? ''
    const target = attrs?.target ?? ''
    const cls = (attrs?.class ?? '').split(' ')
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
    if (cls.includes('ems-link-file')) {
        return { ...empty, type: 'fileLink', href, target }
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

            const search = createSearch<{ type: string }>({
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

function buildFileFields(e: TiptapEditor, ctx: LinkContext, linkText: string) {
    const existingName = ctx.type === 'fileLink' ? getFileNameFromHref(ctx.href) : ''
    return `<div id="link-fields-fileLink" style="display: none; flex-direction: column; gap: 10px;">
        <div>
            <div style="display: flex; gap: 10px;">
                <button type="button" class="ems-btn" id="link-file-browse">${e.trans('link_file_browse')}</button>
                <button type="button" class="ems-btn" id="link-file-browse-server">${e.trans('link_file_browse_server')}</button>
            </div>
            <input type="file" id="link-file-input" style="display: none;">
            <input type="hidden" id="link-file-href" value="${escapeHtml(ctx.type === 'fileLink' ? ctx.href : '')}">
        </div>
        <div>
            <label>${e.trans('link_file')}</label>
            <span id="link-file-name">${escapeHtml(existingName)}</span>
        </div>
        <div>
            <label for="link-file-text">${e.trans('link_file_text')} <span style="color: red">*</span></label>
            <input type="text" id="link-file-text" value="${escapeHtml(linkText || existingName)}" required>
        </div>
        <div>
            <label for="link-target-fileLink">${e.trans('link_target')}</label>
            <select id="link-target-fileLink">
                <option value=""${!ctx.target ? ' selected' : ''}>${e.trans('select')}</option>
                <option value="_blank"${ctx.target === '_blank' ? ' selected' : ''}>${e.trans('link_target_new_window')}</option>
                <option value="_self"${ctx.target === '_self' ? ' selected' : ''}>${e.trans('link_target_same_window')}</option>
            </select>
        </div>
    </div>`
}

function wireFileField(e: TiptapEditor, root: HTMLElement) {
    const browseBtn = root.querySelector<HTMLButtonElement>('#link-file-browse')
    const fileInput = root.querySelector<HTMLInputElement>('#link-file-input')
    const hrefInput = root.querySelector<HTMLInputElement>('#link-file-href')
    const nameLabel = root.querySelector<HTMLElement>('#link-file-name')
    const textInput = root.querySelector<HTMLInputElement>('#link-file-text')
    if (!browseBtn || !fileInput || !hrefInput || !nameLabel || !textInput) return

    browseBtn.addEventListener('click', () => fileInput.click())
    fileInput.addEventListener('change', () => {
        const file = fileInput.files?.[0]
        if (!file) return

        const initUrl = e.docParent.body.dataset.initUpload
        const hashAlgo = e.docParent.body.dataset.hashAlgo
        if (!initUrl) {
            e.showNotice(e.trans('file_upload_error').replace('{file}', file.name), 'error')
            return
        }

        browseBtn.disabled = true
        nameLabel.textContent = e.trans('link_file_uploading')

        new FileUploader({
            file,
            algo: hashAlgo,
            initUrl,
            onUploaded: (assetUrl: string) => {
                hrefInput.value = assetUrl
                nameLabel.textContent = file.name
                textInput.value = file.name
                browseBtn.disabled = false
            },
            onError: () => {
                nameLabel.textContent = ''
                browseBtn.disabled = false
                e.showNotice(e.trans('file_upload_error').replace('{file}', file.name), 'error')
            }
        })
    })

    const browseServerBtn = root.querySelector<HTMLButtonElement>('#link-file-browse-server')
    browseServerBtn?.addEventListener('click', async () => {
        const dialog = e.createDialog('link_file_browse_server', {
            resizable: true,
            minWidth: 800,
            tiptapModal: false
        })
        dialog.open()
        await dialog.loadUrl(e.profile.config.url.browseUploadedFiles)

        dialog.body.addEventListener('click', (ev) => {
            const target = (ev.target as HTMLElement).closest<HTMLAnchorElement>('td a')
            if (!target) return
            ev.preventDefault()

            const wrapper = target.closest<HTMLElement>('div[data-url]')
            const fileUrl = wrapper?.dataset.url
            if (!fileUrl) return
            const text = target.textContent?.trim() ?? fileUrl

            hrefInput.value = fileUrl
            nameLabel.textContent = text
            textInput.value = text
            dialog.close()
        })
    })
}

function getFileNameFromHref(href: string): string {
    const query = href.split('?')[1]
    if (!query) return decodeURIComponent(href.split('/').pop() ?? '')
    return new URLSearchParams(query).get('name') ?? ''
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
    localPage: () => null,
    fileLink: (root) => {
        const hrefInput = root.querySelector<HTMLInputElement>('#link-file-href')!
        const textInput = root.querySelector<HTMLInputElement>('#link-file-text')!
        if (!hrefInput.value || !textInput.reportValidity()) return null
        const target = root.querySelector<HTMLSelectElement>('#link-target-fileLink')!.value || null
        return {
            href: hrefInput.value,
            target,
            text: textInput.value.trim(),
            class: 'ems-link-file'
        }
    }
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
        if (result.text !== undefined) {
            chain
                .setTextSelection({ from, to })
                .insertContent({
                    type: 'text',
                    text: result.text,
                    marks: [
                        {
                            type: 'link',
                            attrs: {
                                href: result.href,
                                target: result.target,
                                class: result.class ?? null
                            }
                        }
                    ]
                })
                .run()
            return
        }
        chain.extendMarkRange('link').setMark('link', result).run()
        return
    }
    if (from === to) {
        chain
            .insertContent({
                type: 'text',
                text: result.text ?? result.href,
                marks: [
                    {
                        type: 'link',
                        attrs: {
                            href: result.href,
                            target: result.target,
                            class: result.class ?? null
                        }
                    }
                ]
            })
            .run()
        return
    }
    chain.setTextSelection({ from, to }).setMark('link', result).run()
}
