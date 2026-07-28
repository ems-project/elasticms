import { Plugin, PluginKey } from '@tiptap/pm/state'
import { Decoration, DecorationSet, EditorView } from '@tiptap/pm/view'
import { TiptapEditor } from '../../Editor.ts'
import { FileUploader } from '../../../FileUploader.ts'

interface PlaceholderMeta {
    add?: { id: string; pos: number; label: string }
    remove?: { id: string }
}

const linkUploadPluginKey = new PluginKey<DecorationSet>('linkUpload')

function isImageFile(file: File): boolean {
    return file.type.startsWith('image/')
}

function getUploadFiles(dataTransfer: DataTransfer | null): File[] {
    if (!dataTransfer) return []
    return Array.from(dataTransfer.files ?? []).filter((f) => !isImageFile(f))
}

function createPlaceholderElement(label: string): HTMLElement {
    const el = document.createElement('span')
    el.className = 'tiptap-file-upload-placeholder'
    el.textContent = label
    return el
}

function findPlaceholderPos(decorationSet: DecorationSet, id: string): number | null {
    const found = decorationSet.find(undefined, undefined, (spec) => spec.id === id)
    return found.length ? found[0].from : null
}

export function createLinkUploadPlugin(editor: TiptapEditor) {
    return new Plugin({
        key: linkUploadPluginKey,
        state: {
            init: () => DecorationSet.empty,
            apply(tr, set) {
                set = set.map(tr.mapping, tr.doc)

                const meta = tr.getMeta(linkUploadPluginKey) as PlaceholderMeta | undefined
                if (meta?.add) {
                    const deco = Decoration.widget(
                        meta.add.pos,
                        createPlaceholderElement(meta.add.label),
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
                return linkUploadPluginKey.getState(state)
            },
            handleDOMEvents: {
                dragover: (_view, event) => {
                    if (!getUploadFiles(event.dataTransfer).length) return false
                    event.preventDefault()
                    if (event.dataTransfer) event.dataTransfer.dropEffect = 'copy'
                    return true
                },
                drop: (view, event) => {
                    const files = getUploadFiles(event.dataTransfer)
                    if (!files.length) return false

                    event.preventDefault()

                    const coords = { left: event.clientX, top: event.clientY }
                    const target = view.posAtCoords(coords)
                    const pos = target ? target.pos : view.state.doc.content.size

                    files.forEach((file) => uploadLinkFile(editor, view, file, pos))

                    return true
                }
            }
        }
    })
}

function uploadLinkFile(editor: TiptapEditor, view: EditorView, file: File, pos: number) {
    const id = `file-upload-${Date.now()}-${Math.random().toString(36).slice(2)}`

    view.dispatch(
        view.state.tr.setMeta(linkUploadPluginKey, { add: { id, pos, label: file.name } })
    )

    const initUrl = editor.docParent.body.dataset.initUpload
    const hashAlgo = editor.docParent.body.dataset.hashAlgo

    const removePlaceholder = () => {
        const decorationSet = linkUploadPluginKey.getState(view.state) ?? DecorationSet.empty
        const placeholderPos = findPlaceholderPos(decorationSet, id)
        if (placeholderPos === null) return null
        view.dispatch(view.state.tr.setMeta(linkUploadPluginKey, { remove: { id } }))
        return placeholderPos
    }

    const fail = () => {
        removePlaceholder()
        showUploadNotice(editor, editor.trans('file_upload_error').replace('{file}', file.name))
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
                    type: 'text',
                    text: file.name,
                    marks: [{ type: 'link', attrs: { href: assetUrl, target: '_blank' } }]
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
