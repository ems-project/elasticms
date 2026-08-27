import { Extension } from '@tiptap/core'
import { Plugin, PluginKey } from '@tiptap/pm/state'
import { Decoration, DecorationSet, EditorView } from '@tiptap/pm/view'
import { TiptapEditor } from '../../Editor.ts'
import { FileUploader } from '../../../FileUploader.ts'

interface PlaceholderMeta {
    add?: { id: string; pos: number }
    remove?: { id: string }
}

const uploadPluginKey = new PluginKey<DecorationSet>('imageUpload')

const DEFAULT_SVG_SIZE = 150

function isImageFile(file: File): boolean {
    return file.type.startsWith('image/')
}

function isSvgFile(file: File): boolean {
    return file.type
      .split(';', 1)[0]
      .trim()
      .toLowerCase() === 'image/svg+xml' || file.name.toLowerCase().endsWith('.svg')
}

function loadImageDimensions(src: string): Promise<{ width: number; height: number } | null> {
    return new Promise((resolve) => {
        const img = new Image()
        img.onload = () => {
            if (img.naturalWidth && img.naturalHeight) {
                resolve({ width: img.naturalWidth, height: img.naturalHeight })
            } else {
                resolve(null)
            }
        }
        img.onerror = () => resolve(null)
        img.src = src
    })
}

function parseSvgLength(value: string | null): number | null {
    if (!value) return null
    const parsed = parseFloat(value)
    return Number.isFinite(parsed) && parsed > 0 ? parsed : null
}

function getSvgDimensions(file: File): Promise<{ width: number; height: number } | null> {
    return new Promise((resolve) => {
        const reader = new FileReader()
        reader.onload = () => {
            try {
                const text = String(reader.result ?? '')
                const svg = new DOMParser().parseFromString(text, 'image/svg+xml').documentElement
                if (!svg || svg.nodeName.toLowerCase() !== 'svg') {
                    resolve(null)
                    return
                }

                const width = parseSvgLength(svg.getAttribute('width'))
                const height = parseSvgLength(svg.getAttribute('height'))
                if (width && height) {
                    resolve({ width, height })
                    return
                }

                const viewBox = svg.getAttribute('viewBox')
                const parts =
                    viewBox
                        ?.trim()
                        .split(/[\s,]+/)
                        .map(Number) ?? []
                if (parts.length === 4 && parts[2] > 0 && parts[3] > 0) {
                    const ratio = parts[3] / parts[2]
                    resolve({
                        width: width ?? DEFAULT_SVG_SIZE,
                        height: height ?? Math.round((width ?? DEFAULT_SVG_SIZE) * ratio)
                    })
                    return
                }

                resolve(null)
            } catch {
                resolve(null)
            }
        }
        reader.onerror = () => resolve(null)
        reader.readAsText(file)
    })
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

export function createImageUploadExtension(editor: TiptapEditor) {
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
        editor.showNotice(editor.trans('image_upload_error').replace('{file}', file.name), 'error')
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
            const insert = (width: number | null, height: number | null) => {
                const placeholderPos = removePlaceholder()
                if (placeholderPos === null) return

                editor.tiptap
                    .chain()
                    .insertContentAt(placeholderPos, {
                        type: 'image',
                        attrs: {
                            src: assetUrl,
                            alt: file.name.replace(/\.[^.]+$/, ''),
                            width: width !== null ? String(width) : null,
                            height: height !== null ? String(height) : null
                        }
                    })
                    .run()

                editor.showNotice(
                    editor.trans('file_upload_success').replace('{file}', file.name),
                    'success'
                )
            }

            const dimensionsPromise = isSvgFile(file)
                ? getSvgDimensions(file)
                : loadImageDimensions(assetUrl)

            dimensionsPromise.then((dimensions) => {
                if (dimensions) {
                    insert(dimensions.width, dimensions.height)
                } else if (isSvgFile(file)) {
                    insert(DEFAULT_SVG_SIZE, DEFAULT_SVG_SIZE)
                } else {
                    insert(null, null)
                }
            })
        },
        onError: () => fail()
    })
}
