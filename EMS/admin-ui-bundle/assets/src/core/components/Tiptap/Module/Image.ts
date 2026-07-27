import IconPhoto from '@tabler/icons/outline/photo.svg?raw'
import IconTrash from '@tabler/icons/outline/trash.svg?raw'
import '../../../../../css/core/components/tiptap/_image.scss'
import { TiptapModule } from '../Types.ts'
import { TiptapEditor } from '../Editor.ts'
import { createImageBlockNode, createImageNode } from './Image/Node.ts'
import { createImageUploadExtension } from './Image/Upload.ts'
import { openImageDialog } from './Image/Dialog.ts'
import { ImageCaption, ImageFigure, removeImage } from './Image/Caption.ts'

export const ImageModule: TiptapModule = {
    extensions: (e) => [
        createImageNode(e),
        createImageBlockNode(e),
        ImageFigure,
        ImageCaption,
        createImageUploadExtension(e)
    ],
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
                isActive: (editor: TiptapEditor) =>
                    editor.tiptap.isActive('image') || editor.tiptap.isActive('imageBlock')
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
            },
            {
                label: 'image_remove',
                icon: IconTrash,
                order: 1,
                command: (editor: TiptapEditor) => removeImage(editor.tiptap)
            }
        ]
    },
    htmlTransforms: [
        {
            name: 'imageFloat',
            toEditor(doc) {
                doc.querySelectorAll('figure[data-type="image"], img').forEach((el) => {
                    const htmlEl = el as HTMLElement
                    const dataFloat = htmlEl.getAttribute('data-float')
                    const styleFloat = htmlEl.style.float

                    if (dataFloat) {
                        htmlEl.style.removeProperty('float')
                    } else if (styleFloat === 'left' || styleFloat === 'right') {
                        htmlEl.setAttribute('data-float', styleFloat)
                        htmlEl.style.removeProperty('float')
                    }
                })
            },
            toOutput(doc) {
                doc.querySelectorAll('[data-float]').forEach((el) => {
                    const htmlEl = el as HTMLElement
                    htmlEl.style.float = htmlEl.getAttribute('data-float') ?? ''
                    htmlEl.removeAttribute('data-float')
                })
            }
        }
    ]
}
