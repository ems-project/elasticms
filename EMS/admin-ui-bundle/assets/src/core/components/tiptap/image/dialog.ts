import IconFolderOpen from '@tabler/icons/outline/folder-open.svg?raw'
import IconLink from '@tabler/icons/outline/link.svg?raw'
import IconLinkOff from '@tabler/icons/outline/link-off.svg?raw'
import IconRefresh from '@tabler/icons/outline/refresh.svg?raw'
import IconJustifyLeft from '@tabler/icons/outline/align-left.svg?raw'
import IconJustifyCenter from '@tabler/icons/outline/align-center.svg?raw'
import IconJustifyRight from '@tabler/icons/outline/align-right.svg?raw'
import { TiptapEditor } from '../editor.ts'
import { escapeHtml } from '../helper.ts'
import type { ImageAttrs } from './node.ts'
import { getImageBrowserListUrl, openImageBrowser } from './browser.ts'
import { findImageFigure, getImageCaption, removeImage, updateImageCaption } from './caption.ts'

function applyAlignment(editor: TiptapEditor, align: string | null): void {
    const figure = findImageFigure(editor.tiptap.state)
    if (figure) {
        editor.tiptap
            .chain()
            .focus()
            .setNodeSelection(figure.pos + 1)
            .run()
    }
    if (align) {
        editor.tiptap.chain().focus().setTextAlign(align).run()
    } else {
        editor.tiptap.chain().focus().unsetTextAlign().run()
    }
}

function applyFloat(editor: TiptapEditor, float: string | null): void {
    const figure = findImageFigure(editor.tiptap.state)
    if (figure) {
        editor.tiptap
            .chain()
            .focus()
            .setNodeSelection(figure.pos + 1)
            .updateAttributes('imageFigure', { float })
            .run()
        return
    }
    if (editor.tiptap.isActive('image')) {
        editor.tiptap.chain().focus().updateAttributes('image', { float }).run()
    }
}

export function openImageDialog(editor: TiptapEditor): void {
    const figure = editor.tiptap.isActive('imageBlock')
        ? null
        : findImageFigure(editor.tiptap.state)
    const isImageBlockActive = editor.tiptap.isActive('imageBlock') || Boolean(figure)
    const isEdit = isImageBlockActive || editor.tiptap.isActive('image')
    const activeType = isImageBlockActive ? 'imageBlock' : 'image'
    const imagePos = figure ? figure.pos + 1 : editor.tiptap.state.selection.from
    const existing = (
        isEdit
            ? figure
                ? (figure.node.firstChild?.attrs ?? {})
                : editor.tiptap.getAttributes(activeType)
            : {}
    ) as Partial<ImageAttrs>
    const existingCaption = isEdit ? getImageCaption(editor.tiptap.state) : ''
    const figureForLayout = figure ?? findImageFigure(editor.tiptap.state)
    const existingAlign: string | null =
        (figureForLayout
            ? figureForLayout.node.attrs.textAlign
            : editor.tiptap.getAttributes('paragraph').textAlign) ?? null
    const existingFloat: string | null =
        (figureForLayout
            ? figureForLayout.node.attrs.float
            : editor.tiptap.getAttributes('image').float) ?? null

    const dialog = editor.createDialog(isEdit ? 'image_edit' : 'image_insert', {
        bodyClass: 'tiptap-dialog-image',
        resizable: true,
        minWidth: 420
    })

    const preview = document.createElement('div')
    preview.className = 'tiptap-image-preview'
    const previewImg = document.createElement('img')
    if (existing.src) previewImg.src = existing.src
    preview.appendChild(previewImg)

    let ratio: number | null = null
    let naturalWidth: number | null = null
    let naturalHeight: number | null = null
    let sizeSetByUser = Boolean(existing.width || existing.height)
    previewImg.addEventListener('load', () => {
        if (previewImg.naturalWidth && previewImg.naturalHeight) {
            naturalWidth = previewImg.naturalWidth
            naturalHeight = previewImg.naturalHeight
            ratio = naturalHeight / naturalWidth

            if (!widthInput.value && !heightInput.value) {
                widthInput.value = String(naturalWidth)
                heightInput.value = String(naturalHeight)
            }
        }
    })

    const urlInput = document.createElement('input')
    urlInput.type = 'text'
    urlInput.id = 'image-url'
    urlInput.required = true
    urlInput.value = existing.src ?? ''
    urlInput.placeholder = editor.trans('image_url')
    urlInput.addEventListener('change', () => {
        const url = urlInput.value.trim()
        widthInput.value = ''
        heightInput.value = ''
        previewImg.src = url
        if (url) error.hidden = true
    })

    const browseBtn = document.createElement('button')
    browseBtn.type = 'button'
    browseBtn.className = 'tiptap-image-browse-btn'
    browseBtn.innerHTML = `${IconFolderOpen}<span>${escapeHtml(editor.profile.config.emsBrowsers?.browser_image?.label ?? editor.trans('image_browse'))}</span>`
    browseBtn.hidden = !getImageBrowserListUrl(editor)
    browseBtn.addEventListener('click', () => {
        openImageBrowser(editor, (url) => {
            urlInput.value = url
            widthInput.value = ''
            heightInput.value = ''
            previewImg.src = url
            error.hidden = true
        })
    })

    const urlRow = document.createElement('div')
    urlRow.className = 'tiptap-image-url-row'
    urlRow.append(urlInput, browseBtn)

    const urlField = document.createElement('div')
    urlField.className = 'tiptap-image-field'
    urlField.innerHTML = `<label for="image-url">${editor.trans('image_url')} <span style="color: red">*</span></label>`
    urlField.appendChild(urlRow)

    const altInput = document.createElement('input')
    altInput.type = 'text'
    altInput.id = 'image-alt'
    altInput.value = existing.alt ?? ''

    const altField = document.createElement('div')
    altField.className = 'tiptap-image-field'
    altField.innerHTML = `<label for="image-alt">${editor.trans('image_alt')}</label>`
    altField.appendChild(altInput)

    const captionInput = document.createElement('textarea')
    captionInput.id = 'image-caption'
    captionInput.rows = 3
    captionInput.value = existingCaption

    const captionField = document.createElement('div')
    captionField.className = 'tiptap-image-field'
    captionField.innerHTML = `<label for="image-caption">${editor.trans('caption')}</label>`
    captionField.appendChild(captionInput)

    const widthInput = document.createElement('input')
    widthInput.type = 'number'
    widthInput.id = 'image-width'
    widthInput.min = '0'
    widthInput.value = existing.width ?? ''

    const widthField = document.createElement('div')
    widthField.className = 'tiptap-image-field'
    widthField.innerHTML = `<label for="image-width">${editor.trans('image_width')}</label>`
    widthField.appendChild(widthInput)

    const heightInput = document.createElement('input')
    heightInput.type = 'number'
    heightInput.id = 'image-height'
    heightInput.min = '0'
    heightInput.value = existing.height ?? ''

    const heightField = document.createElement('div')
    heightField.className = 'tiptap-image-field'
    heightField.innerHTML = `<label for="image-height">${editor.trans('image_height')}</label>`
    heightField.appendChild(heightInput)

    let locked = true
    const lockBtn = document.createElement('button')
    lockBtn.type = 'button'
    lockBtn.className = 'tiptap-image-lock-btn is-locked'
    lockBtn.title = editor.trans('image_lock_ratio')
    lockBtn.innerHTML = IconLink
    lockBtn.addEventListener('click', () => {
        locked = !locked
        lockBtn.classList.toggle('is-locked', locked)
        lockBtn.innerHTML = locked ? IconLink : IconLinkOff
    })

    const resetBtn = document.createElement('button')
    resetBtn.type = 'button'
    resetBtn.className = 'tiptap-image-reset-btn'
    resetBtn.title = editor.trans('image_reset_size')
    resetBtn.innerHTML = IconRefresh
    resetBtn.addEventListener('click', () => {
        if (!naturalWidth || !naturalHeight) return
        widthInput.value = String(naturalWidth)
        heightInput.value = String(naturalHeight)
        sizeSetByUser = true
    })

    widthInput.addEventListener('input', () => {
        sizeSetByUser = true
        if (!locked || !ratio || !widthInput.value) return
        heightInput.value = String(Math.round(Number(widthInput.value) * ratio))
    })
    heightInput.addEventListener('input', () => {
        sizeSetByUser = true
        if (!locked || !ratio || !heightInput.value) return
        widthInput.value = String(Math.round(Number(heightInput.value) / ratio))
    })

    const dimensionsRow = document.createElement('div')
    dimensionsRow.className = 'tiptap-image-dimensions-row'
    dimensionsRow.innerHTML = `<label>${editor.trans('image_dimensions')}</label>`
    const dimensionsInner = document.createElement('div')
    dimensionsInner.className = 'tiptap-image-dimensions-row'
    dimensionsInner.append(widthField, lockBtn, heightField, resetBtn)

    let align: string | null = existingAlign
    const alignOptions: {
        value: string | null
        icon: string
        label: 'align_left' | 'align_center' | 'align_right'
    }[] = [
        { value: null, icon: IconJustifyLeft, label: 'align_left' },
        { value: 'center', icon: IconJustifyCenter, label: 'align_center' },
        { value: 'right', icon: IconJustifyRight, label: 'align_right' }
    ]

    const alignRow = document.createElement('div')
    alignRow.className = 'tiptap-image-align-row'

    const alignBtnEls = alignOptions.map(({ value, icon, label }) => {
        const btn = document.createElement('button')
        btn.type = 'button'
        btn.className = 'tiptap-image-align-btn'
        btn.title = editor.trans(label)
        btn.innerHTML = icon
        btn.classList.toggle('is-active', align === value)
        btn.addEventListener('click', () => {
            align = value
            alignBtnEls.forEach((el, i) =>
                el.classList.toggle('is-active', alignOptions[i].value === value)
            )
        })
        alignRow.appendChild(btn)
        return btn
    })

    const alignField = document.createElement('div')
    alignField.className = 'tiptap-image-field'
    alignField.innerHTML = `<label>${editor.trans('image_alignment')}</label>`
    alignField.appendChild(alignRow)

    let float: string | null = existingFloat
    const floatOptions: {
        value: string | null
        label: 'image_wrap_none' | 'image_wrap_left' | 'image_wrap_right'
    }[] = [
        { value: null, label: 'image_wrap_none' },
        { value: 'left', label: 'image_wrap_left' },
        { value: 'right', label: 'image_wrap_right' }
    ]

    const floatRow = document.createElement('div')
    floatRow.className = 'tiptap-image-align-row'

    const floatBtnEls = floatOptions.map(({ value, label }) => {
        const btn = document.createElement('button')
        btn.type = 'button'
        btn.className = 'tiptap-image-align-btn tiptap-image-align-btn-text'
        btn.textContent = editor.trans(label)
        btn.classList.toggle('is-active', float === value)
        btn.addEventListener('click', () => {
            float = value
            floatBtnEls.forEach((el, i) =>
                el.classList.toggle('is-active', floatOptions[i].value === value)
            )
        })
        floatRow.appendChild(btn)
        return btn
    })

    const floatField = document.createElement('div')
    floatField.className = 'tiptap-image-field'
    floatField.innerHTML = `<label>${editor.trans('image_wrap')}</label>`
    floatField.appendChild(floatRow)

    const layoutRow = document.createElement('div')
    layoutRow.className = 'tiptap-image-dimensions-row'
    layoutRow.append(alignField, floatField)

    const error = document.createElement('p')
    error.className = 'tiptap-image-error'
    error.hidden = true
    error.textContent = editor.trans('image_url_required')

    dialog.body.append(preview, urlField, dimensionsInner, layoutRow, altField, captionField, error)

    dialog
        .addButton({
            label: editor.trans(isEdit ? 'button_update' : 'button_insert'),
            variant: 'primary',
            onClick: (d) => {
                const src = urlInput.value.trim()
                if (!src) {
                    error.hidden = false
                    return
                }
                const attrs: ImageAttrs = {
                    src,
                    alt: altInput.value.trim() || null,
                    width: sizeSetByUser ? widthInput.value.trim() || null : null,
                    height: sizeSetByUser ? heightInput.value.trim() || null : null
                }
                const caption = captionInput.value.trim()

                if (isEdit) {
                    editor.tiptap
                        .chain()
                        .focus()
                        .setNodeSelection(imagePos)
                        .updateAttributes(activeType, attrs)
                        .setNodeSelection(imagePos)
                        .run()
                    updateImageCaption(editor.tiptap, caption)
                    applyAlignment(editor, align)
                    applyFloat(editor, float)
                } else if (caption) {
                    editor.tiptap
                        .chain()
                        .focus()
                        .insertContent({
                            type: 'imageFigure',
                            attrs: { textAlign: align, float },
                            content: [
                                { type: 'imageBlock', attrs },
                                { type: 'imageCaption', content: [{ type: 'text', text: caption }] }
                            ]
                        })
                        .run()
                } else {
                    editor.tiptap
                        .chain()
                        .focus()
                        .insertContent({ type: 'image', attrs: { ...attrs, float } })
                        .run()
                    applyAlignment(editor, align)
                }
                d.close()
            }
        })
        .addButton({
            label: editor.trans('button_cancel'),
            variant: 'secondary',
            onClick: (d) => d.close()
        })

    if (isEdit) {
        dialog.addButton({
            label: editor.trans('image_remove'),
            variant: 'danger',
            align: 'left',
            onClick: (d) => {
                removeImage(editor.tiptap)
                d.close()
            }
        })
    }

    dialog.open()
}
