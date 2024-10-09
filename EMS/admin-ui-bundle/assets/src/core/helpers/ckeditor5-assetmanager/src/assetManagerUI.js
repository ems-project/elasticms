import { Plugin, icons } from 'ckeditor5/src/core.js'
import { ButtonView } from 'ckeditor5/src/ui.js'
import UploadImageCommand from '@ckeditor/ckeditor5-image/src/imageupload/uploadimagecommand.js'
import CkeModal from '../../ckeModal'
import { EMS_EDIT_IMAGE_EVENT } from '../../../events/editImageEvent'

export default class AssetManagerUI extends Plugin {
  static get requires() {
    return []
  }

  static get pluginName() {
    return 'AssetManagerUI'
  }

  init() {
    const editor = this.editor
    if (!editor.commands.get('uploadImage')) {
      const uploadImageCommand = new UploadImageCommand(editor)
      editor.commands.add('uploadImage', uploadImageCommand)
    }

    this._initEditImageButton()
    this._initToolbarButton()
  }

  _initEditImageButton() {
    const self = this
    const editor = this.editor
    // const editingView = editor.editing.view;
    const t = editor.t
    editor.ui.componentFactory.add('editImage', (locale) => {
      const view = new ButtonView(locale)
      view.set({
        icon: icons.image,
        tooltip: true,
        isToggleable: true,
        label: t('Edit image')
      })
      this.listenTo(view, 'execute', () => {
        self._editImage()
        // editor.execute('toggleImageCaption', { focusCaptionOnShow: true });
        // // Scroll to the selection and highlight the caption if the caption showed up.
        // const modelCaptionElement = imageCaptionUtils.getCaptionFromModelSelection(editor.model.document.selection);
        // if (modelCaptionElement) {
        //     const figcaptionElement = editor.editing.mapper.toViewElement(modelCaptionElement);
        //     editingView.scrollToTheSelection();
        //     editingView.change(writer => {
        //         writer.addClass('image__caption_highlighted', figcaptionElement);
        //     });
        // }
        // editor.editing.view.focus();
      })
      return view
    })
  }

  _initToolbarButton() {
    const editor = this.editor
    const t = editor.t
    const command = editor.commands.get('uploadImage')
    const selection = editor.model.document.selection
    const imageUtils = editor.plugins.get('ImageUtils')
    this.set('isImageSelected', false)
    const componentCreator = (locale) => {
      const view = new ButtonView(locale)
      view.set({
        label: t('Insert image from server'),
        icon: icons.imageUpload,
        tooltip: true
        // isEnabled: true,
      })
      view.bind('isEnabled').to(command)
      this.listenTo(view, 'execute', () => {
        const selectedElement = selection.getSelectedElement()
        let currentPath = null
        if (imageUtils.isImage(selectedElement)) {
          currentPath = selectedElement.getAttribute('src')
        }
        console.log(currentPath)
      })
      // view.on('done', (evt, files) => {
      //     console.log('Open asset manager')
      //     const imagesToUpload = Array.from(files).filter(file => imageTypesRegExp.test(file.type));
      //     if (imagesToUpload.length) {
      //         editor.execute('uploadImage', { file: imagesToUpload });
      //         editor.editing.view.focus();
      //     }
      // });
      return view
    }
    editor.ui.componentFactory.add('browseImage', componentCreator)
    if (editor.plugins.has('ImageInsertUI')) {
      const imageInsertUI = editor.plugins.get('ImageInsertUI')
      const command = editor.commands.get('uploadImage')
      imageInsertUI.registerIntegration({
        name: 'assetManager',
        observable: command,
        buttonViewCreator: () => {
          const uploadImageButton = editor.ui.componentFactory.create('browseImage')
          uploadImageButton
            .bind('label')
            .to(imageInsertUI, 'isImageSelected', (isImageSelected) =>
              isImageSelected ? t('Replace image from server') : t('Insert image from server')
            )
          return uploadImageButton
        },
        formViewCreator: () => {
          const uploadImageButton = editor.ui.componentFactory.create('browseImage')
          uploadImageButton.withText = true
          uploadImageButton
            .bind('label')
            .to(imageInsertUI, 'isImageSelected', (isImageSelected) =>
              isImageSelected ? t('Replace from server') : t('Insert from server')
            )
          uploadImageButton.on('execute', () => {
            imageInsertUI.dropdownView.isOpen = false
          })
          return uploadImageButton
        }
      })
    }
  }

  _createModal() {
    const editor = this.editor
    const t = this.editor.t
    this.formModal = new CkeModal('initEditImage', t('Edit image'))
    document.addEventListener(EMS_EDIT_IMAGE_EVENT, (event) => {
      editor.execute('insertImage', { source: event.detail.url })
    })
  }

  _editImage() {
    const editor = this.editor
    const selection = editor.model.document.selection
    const imageUtils = editor.plugins.get('ImageUtils')
    const selectedElement = selection.getSelectedElement()
    let currentPath = null
    if (!imageUtils.isImage(selectedElement)) {
      return
    }
    if (!this.formModal) {
      this._createModal()
    }
    currentPath = selectedElement.getAttribute('src')
    this.formModal.show({ path: currentPath })
  }
}
