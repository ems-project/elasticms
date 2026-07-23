import IconPhoto from '@tabler/icons/outline/photo.svg?raw'
import IconFolderOpen from '@tabler/icons/outline/folder-open.svg?raw'
import IconLink from '@tabler/icons/outline/link.svg?raw'
import IconLinkOff from '@tabler/icons/outline/link-off.svg?raw'
import IconRefresh from '@tabler/icons/outline/refresh.svg?raw'
// @ts-expect-error - @elasticms/file-uploader ships without type declarations
import FileUploaderImpl from '@elasticms/file-uploader'
import { Extension, Node, mergeAttributes } from '@tiptap/core'
import { Plugin, PluginKey } from '@tiptap/pm/state'
import { Decoration, DecorationSet, EditorView } from '@tiptap/pm/view'
import '../../../../../css/core/components/tiptap/_image.scss'
import { TiptapModule } from '../types.ts'
import { TiptapEditor } from '../editor.ts'
import { escapeHtml } from '../helper.ts'

interface FileUploaderOptions {
    file: File
    algo?: string
    initUrl: string
    onUploaded?: (assetUrl: string, previewUrl: string) => void
    onError?: (message: string, code?: number) => void
}

const FileUploader = FileUploaderImpl as unknown as new (options: FileUploaderOptions) => void

interface ImageAttrs {
    src: string | null
    alt: string | null
    width: string | null
    height: string | null
}

export const imageModule: TiptapModule = {
    extensions: (e) => [createImageNode(e), createImageUploadExtension(e)],
    isEnabled: (profile) => profile.hasPlugin('image2'),
    toolbar: {
        group: 'insert',
        items: [
            {
                name: 'Image',
                icon: IconPhoto,
                tooltip: 'image_insert',
                order: 1,
                command: (editor: TiptapEditor) => openImageDialog(editor),
                isActive: (editor: TiptapEditor) => editor.tiptap.isActive('image')
            }
        ]
    },
    contextMenu: {
        node: 'image',
        items: [
            {
                label: 'image_edit',
                icon: IconPhoto,
                order: 0,
                command: (editor: TiptapEditor) => openImageDialog(editor)
            }
        ]
    }
}

function stripFalsy(attrs: Record<string, unknown>): Record<string, string> {
    const result: Record<string, string> = {}
    for (const [k, v] of Object.entries(attrs)) {
        if (v !== null && v !== undefined && v !== false && v !== '') {
            result[k] = String(v)
        }
    }
    return result
}

function createImageNode(editor: TiptapEditor) {
    return Node.create({
        name: 'image',
        group: 'inline',
        inline: true,
        atom: true,
        draggable: true,

        addAttributes() {
            return {
                src: { default: null },
                alt: { default: null },
                width: { default: null },
                height: { default: null }
            }
        },

        parseHTML() {
            return [{ tag: 'img[src]' }]
        },

        renderHTML({ HTMLAttributes }) {
            return ['img', mergeAttributes(stripFalsy(HTMLAttributes))]
        },

        addNodeView() {
            return ({ node, getPos }) => {
                const img = document.createElement('img')
                img.className = 'tiptap-image'

                const sync = (attrs: ImageAttrs) => {
                    img.src = attrs.src ?? ''
                    if (attrs.alt) img.setAttribute('alt', attrs.alt)
                    else img.removeAttribute('alt')
                    if (attrs.width) img.setAttribute('width', attrs.width)
                    else img.removeAttribute('width')
                    if (attrs.height) img.setAttribute('height', attrs.height)
                    else img.removeAttribute('height')
                }
                sync(node.attrs as ImageAttrs)

                img.addEventListener('dblclick', () => {
                    const pos = getPos()
                    if (typeof pos !== 'number') return
                    editor.tiptap.chain().setNodeSelection(pos).run()
                    openImageDialog(editor)
                })

                return {
                    dom: img,
                    update: (updatedNode) => {
                        if (updatedNode.type.name !== 'image') return false
                        sync(updatedNode.attrs as ImageAttrs)
                        return true
                    }
                }
            }
        },

        addProseMirrorPlugins() {
            const key = new PluginKey('imageSelectionHighlight')
            return [
                new Plugin({
                    key,
                    props: {
                        decorations: (state) => {
                            const { from, to, empty } = state.selection
                            if (empty) return null

                            const decorations: Decoration[] = []
                            state.doc.nodesBetween(from, to, (node, pos) => {
                                if (node.type.name === 'image') {
                                    decorations.push(
                                        Decoration.node(pos, pos + node.nodeSize, {
                                            class: 'is-in-selection'
                                        })
                                    )
                                }
                            })
                            return DecorationSet.create(state.doc, decorations)
                        }
                    }
                })
            ]
        }
    })
}

// --- Drag & drop upload -----------------------------------------------------

interface PlaceholderMeta {
    add?: { id: string; pos: number }
    remove?: { id: string }
}

const uploadPluginKey = new PluginKey<DecorationSet>('imageUpload')

function isImageFile(file: File): boolean {
    return file.type.startsWith('image/')
}

function getImageFiles(dataTransfer: DataTransfer | null): File[] {
    if (!dataTransfer) return []
    return Array.from(dataTransfer.files ?? []).filter(isImageFile)
}

function createPlaceholderElement(): HTMLElement {
    const el = document.createElement('span')
    el.className = 'tiptap-image-placeholder'
    return el
}

function findPlaceholderPos(decorationSet: DecorationSet, id: string): number | null {
    const found = decorationSet.find(undefined, undefined, (spec) => spec.id === id)
    return found.length ? found[0].from : null
}

function createImageUploadExtension(editor: TiptapEditor) {
    return Extension.create({
        name: 'imageUpload',

        addProseMirrorPlugins() {
            return [
                new Plugin({
                    key: uploadPluginKey,
                    state: {
                        init: () => DecorationSet.empty,
                        apply(tr, set) {
                            set = set.map(tr.mapping, tr.doc)

                            const meta = tr.getMeta(uploadPluginKey) as PlaceholderMeta | undefined
                            if (meta?.add) {
                                const deco = Decoration.widget(
                                    meta.add.pos,
                                    createPlaceholderElement(),
                                    { id: meta.add.id, side: -1 }
                                )
                                set = set.add(tr.doc, [deco])
                            }
                            if (meta?.remove) {
                                const toRemove = set.find(
                                    undefined,
                                    undefined,
                                    (spec) => spec.id === meta.remove!.id
                                )
                                set = set.remove(toRemove)
                            }

                            return set
                        }
                    },
                    props: {
                        decorations(state) {
                            return uploadPluginKey.getState(state)
                        },
                        handleDOMEvents: {
                            dragover: (_view, event) => {
                                if (!getImageFiles(event.dataTransfer).length) return false
                                event.preventDefault()
                                if (event.dataTransfer) event.dataTransfer.dropEffect = 'copy'
                                return true
                            },
                            drop: (view, event) => {
                                const files = getImageFiles(event.dataTransfer)
                                if (!files.length) return false

                                event.preventDefault()

                                const coords = { left: event.clientX, top: event.clientY }
                                const target = view.posAtCoords(coords)
                                const pos = target ? target.pos : view.state.doc.content.size

                                files.forEach((file) => uploadImageFile(editor, view, file, pos))

                                return true
                            }
                        }
                    }
                })
            ]
        }
    })
}

function uploadImageFile(editor: TiptapEditor, view: EditorView, file: File, pos: number) {
    const id = `img-upload-${Date.now()}-${Math.random().toString(36).slice(2)}`

    view.dispatch(view.state.tr.setMeta(uploadPluginKey, { add: { id, pos } }))

    const initUrl = editor.docParent.body.dataset.initUpload
    const hashAlgo = editor.docParent.body.dataset.hashAlgo

    const removePlaceholder = () => {
        const decorationSet = uploadPluginKey.getState(view.state) ?? DecorationSet.empty
        const placeholderPos = findPlaceholderPos(decorationSet, id)
        if (placeholderPos === null) return null
        view.dispatch(view.state.tr.setMeta(uploadPluginKey, { remove: { id } }))
        return placeholderPos
    }

    const fail = () => {
        removePlaceholder()
        showUploadNotice(editor, editor.trans('image_upload_error').replace('{file}', file.name))
    }

    if (!initUrl) {
        fail()
        return
    }

    new FileUploader({
        file,
        algo: hashAlgo,
        initUrl,
        onUploaded: (assetUrl: string) => {
            const placeholderPos = removePlaceholder()
            if (placeholderPos === null) return

            editor.tiptap
                .chain()
                .insertContentAt(placeholderPos, {
                    type: 'image',
                    attrs: { src: assetUrl, alt: file.name.replace(/\.[^.]+$/, '') }
                })
                .run()
        },
        onError: () => fail()
    })
}

function showUploadNotice(editor: TiptapEditor, message: string): void {
    const doc = editor.docParent
    const notice = doc.createElement('div')
    notice.className = 'tiptap-image-upload-error'
    notice.textContent = message
    notice.style.cssText =
        'position:fixed;top:16px;right:16px;z-index:100000;background:#f8d7da;color:#842029;' +
        'border:1px solid #f5c2c7;border-radius:4px;padding:8px 14px;font-size:13px;box-shadow:0 2px 8px rgba(0,0,0,.15);'
    doc.body.appendChild(notice)
    setTimeout(() => notice.remove(), 5000)
}

// --- Browse existing uploads -------------------------------------------------
//
// Self-contained: fetches the images list JSON directly and renders the picker
// in our own dialog. No dependency on the legacy CKEditor4 image-browser popup
// (core-bundle's cke-plugins/imagebrowser), since that plugin is going away.

interface BrowsableImage {
    image: string
    thumb: string
    folder: string
}

function getImageBrowserListUrl(editor: TiptapEditor): string | null {
    return (
        editor.profile.config.emsBrowsers?.browser_image?.url ??
        editor.profile.config.imageBrowser_listUrl ??
        null
    )
}

function groupImagesByFolder(items: BrowsableImage[]): Map<string, BrowsableImage[]> {
    const folders = new Map<string, BrowsableImage[]>()
    for (const item of items) {
        const folder = item.folder || 'Images'
        if (!folders.has(folder)) folders.set(folder, [])
        folders.get(folder)!.push(item)
    }
    return folders
}

function openImageBrowser(editor: TiptapEditor, onSelect: (url: string) => void): void {
    const listUrl = getImageBrowserListUrl(editor)
    if (!listUrl) return

    const dialog = editor.createDialog('image_browse', {
        bodyClass: 'tiptap-dialog-image-browser',
        resizable: true,
        minWidth: 640
    })

    const folderTabs = document.createElement('div')
    folderTabs.className = 'tiptap-image-browser-folders'

    const grid = document.createElement('div')
    grid.className = 'tiptap-image-browser-grid'

    const status = document.createElement('p')
    status.className = 'tiptap-image-browser-status'
    status.textContent = editor.trans('image_browse_loading')

    dialog.body.append(folderTabs, status, grid)
    dialog.addButton({
        label: editor.trans('button_cancel'),
        variant: 'secondary',
        onClick: (d) => d.close()
    })
    dialog.open()

    fetch(listUrl, { credentials: 'same-origin' })
        .then((response) => {
            if (!response.ok) throw new Error(`HTTP ${response.status}`)
            return response.json() as Promise<unknown>
        })
        .then((list) => {
            const items = Array.isArray(list) ? (list as BrowsableImage[]) : []
            renderImageBrowser(editor, folderTabs, grid, status, items, (url) => {
                onSelect(url)
                dialog.close()
            })
        })
        .catch(() => {
            status.textContent = editor.trans('image_browse_error')
            status.classList.add('is-error')
        })
}

function renderImageBrowser(
    editor: TiptapEditor,
    folderTabs: HTMLElement,
    grid: HTMLElement,
    status: HTMLElement,
    items: BrowsableImage[],
    onSelect: (url: string) => void
): void {
    if (!items.length) {
        status.textContent = editor.trans('image_browse_empty')
        return
    }
    status.remove()

    const folders = groupImagesByFolder(items)
    const folderNames = Array.from(folders.keys())

    const renderGrid = (folder: string) => {
        grid.innerHTML = ''
        for (const item of folders.get(folder) ?? []) {
            const thumb = document.createElement('button')
            thumb.type = 'button'
            thumb.className = 'tiptap-image-browser-item'
            thumb.title = item.image
            const img = document.createElement('img')
            img.src = item.thumb || item.image
            img.alt = ''
            thumb.appendChild(img)
            thumb.addEventListener('click', () => onSelect(item.image))
            grid.appendChild(thumb)
        }
    }

    if (folderNames.length > 1) {
        folderNames.forEach((folder, idx) => {
            const tab = document.createElement('button')
            tab.type = 'button'
            tab.className = 'tiptap-image-browser-folder' + (idx === 0 ? ' is-active' : '')
            tab.textContent = folder
            tab.addEventListener('click', () => {
                folderTabs
                    .querySelectorAll('.tiptap-image-browser-folder')
                    .forEach((el) => el.classList.remove('is-active'))
                tab.classList.add('is-active')
                renderGrid(folder)
            })
            folderTabs.appendChild(tab)
        })
    }

    renderGrid(folderNames[0])
}

// --- Insert / edit dialog ----------------------------------------------------

function openImageDialog(editor: TiptapEditor): void {
    const isEdit = editor.tiptap.isActive('image')
    const existing = (isEdit ? editor.tiptap.getAttributes('image') : {}) as Partial<ImageAttrs>

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
    urlInput.readOnly = true
    urlInput.required = true
    urlInput.value = existing.src ?? ''
    urlInput.placeholder = editor.trans('image_url')

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

    dialog.body.append(preview, urlField, altField, dimensionsInner, error)

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

                if (isEdit) {
                    editor.tiptap.chain().focus().updateAttributes('image', attrs).run()
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
            onClick: (d) => {
                editor.tiptap.chain().focus().deleteSelection().run()
                d.close()
            }
        })
    }

    dialog.open()
}
