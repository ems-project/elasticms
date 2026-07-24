import IconFolderOpen from '@tabler/icons/outline/folder-open.svg?raw'
import IconLink from '@tabler/icons/outline/link.svg?raw'
import IconLinkOff from '@tabler/icons/outline/link-off.svg?raw'
import IconRefresh from '@tabler/icons/outline/refresh.svg?raw'
import { TiptapEditor } from '../editor.ts'
import { escapeHtml } from '../helper.ts'
import type { ImageAttrs } from './node.ts'
import { getImageBrowserListUrl, openImageBrowser } from './browser.ts'
import { getImageCaption, removeImage, updateImageCaption } from './caption.ts'

export function openImageDialog(editor: TiptapEditor): void {
    const isImageBlockActive = editor.tiptap.isActive('imageBlock')
    const isEdit = isImageBlockActive || editor.tiptap.isActive('image')
    const activeType = isImageBlockActive ? 'imageBlock' : 'image'
    const existing = (isEdit ? editor.tiptap.getAttributes(activeType) : {}) as Partial<ImageAttrs>
    const existingCaption = isEdit ? getImageCaption(editor.tiptap.state) : ''

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

    const captionInput = document.createElement('input')
    captionInput.type = 'text'
    captionInput.id = 'image-caption'
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
    })

    widthInput.addEventListener('input', () => {
        if (!locked || !ratio || !widthInput.value) return
        heightInput.value = String(Math.round(Number(widthInput.value) * ratio))
    })
    heightInput.addEventListener('input', () => {
        if (!locked || !ratio || !heightInput.value) return
        widthInput.value = String(Math.round(Number(heightInput.value) / ratio))
    })

    const dimensionsRow = document.createElement('div')
    dimensionsRow.className = 'tiptap-image-dimensions-row'
    dimensionsRow.innerHTML = `<label>${editor.trans('image_dimensions')}</label>`
    const dimensionsInner = document.createElement('div')
    dimensionsInner.className = 'tiptap-image-dimensions-row'
    dimensionsInner.append(widthField, lockBtn, heightField, resetBtn)

    const error = document.createElement('p')
    error.className = 'tiptap-image-error'
    error.hidden = true
    error.textContent = editor.trans('image_url_required')

    dialog.body.append(preview, urlField, altField, captionField, dimensionsInner, error)

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
                    width: widthInput.value.trim() || null,
                    height: heightInput.value.trim() || null
                }
                const caption = captionInput.value.trim()

                if (isEdit) {
                    const pos = editor.tiptap.state.selection.from
                    editor.tiptap
                        .chain()
                        .focus()
                        .updateAttributes(activeType, attrs)
                        .setNodeSelection(pos)
                        .run()
                    updateImageCaption(editor.tiptap, caption)
                } else if (caption) {
                    editor.tiptap
                        .chain()
                        .focus()
                        .insertContent({
                            type: 'imageFigure',
                            content: [
                                { type: 'imageBlock', attrs },
                                { type: 'imageCaption', content: [{ type: 'text', text: caption }] }
                            ]
                        })
                        .run()
                } else {
                    editor.tiptap.chain().focus().insertContent({ type: 'image', attrs }).run()
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
