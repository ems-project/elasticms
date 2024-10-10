import {
  Alignment,
  Autoformat,
  BlockQuote,
  Bold,
  ClassicEditor,
  Essentials,
  FindAndReplace,
  FontColor,
  FontFamily,
  FontSize,
  GeneralHtmlSupport,
  Heading,
  Image,
  ImageCaption,
  ImageInsertViaUrl,
  ImageResizeEditing,
  ImageResizeHandles,
  ImageStyle,
  ImageToolbar,
  ImageUpload,
  Indent,
  Italic,
  Link,
  List,
  MediaEmbed,
  Paragraph,
  PictureEditing,
  RemoveFormat,
  SourceEditing,
  SpecialCharacters,
  SpecialCharactersEssentials,
  Style,
  Table,
  TableCaption,
  TableCellProperties,
  TableColumnResize,
  TableProperties,
  TableToolbar,
  TextTransformation,
  Undo
} from 'ckeditor5'
import 'ckeditor5/ckeditor5.css'
import { EditorOptions } from './editorOptions.ts'
import { EditorRevisionOptions } from './editorRevisionOptions.ts'

// import { Link } from './ckeditor5-link/src/index'
// import { AssetManager } from './ckeditor5-assetmanager/src/index'
// import { UploadAdapter } from './ck5/uploadAdapter'
// import { PasteAjax } from './ck5/pasteAjax'
// import { LinkTarget } from './ck5/linkTarget'
//
// import ChangeEvent from '../events/changeEvent'
//
// function initUploadAdaptor(editor) {
//   editor.plugins.get('FileRepository').createUploadAdapter = (loader) => {
//     return new UploadAdapter(loader)
//   }
// }

export default class Editor {
  constructor(element: HTMLElement, options: EditorRevisionOptions | null) {
    console.log(options)
    ClassicEditor.create(element, this.buildCke5Options())
      // .then((editor) => {
      //   self._init(editor)
      // })
      .catch((error) => {
        console.error(error)
      })
  }

  buildCke5Options(): EditorOptions {
    const options = this.getDefaultOptions()
    console.log(options)
    // options = this._applyProfile(options)
    // options = this._applyStyleSet(options)
    // options = this._applyHeadings(options)
    // options = this._applyLang(options)

    return options
  }

  getDefaultOptions(): EditorOptions {
    return {
      heading: {
        options: [
          { model: 'paragraph', title: 'Paragraph', class: '' },
          {
            model: 'heading2',
            view: 'h2',
            title: 'Heading 2',
            class: ''
          },
          {
            model: 'heading3',
            view: 'h3',
            title: 'Heading 3',
            class: ''
          }
        ]
      },
      htmlSupport: {
        allow: [
          {
            name: /.*/,
            attributes: true,
            classes: true,
            styles: true
          }
        ]
      },
      // extraPlugins: [initUploadAdaptor],
      plugins: [
        Alignment,
        Autoformat,
        // AssetManager,
        BlockQuote,
        Bold,
        Essentials,
        FindAndReplace,
        FontColor,
        FontFamily,
        FontSize,
        GeneralHtmlSupport,
        Heading,
        Image,
        ImageCaption,
        ImageInsertViaUrl,
        ImageStyle,
        ImageToolbar,
        ImageResizeEditing,
        ImageResizeHandles,
        ImageUpload,
        Indent,
        Italic,
        Link,
        // LinkTarget,
        List,
        MediaEmbed,
        Paragraph,
        // PasteAjax,
        PictureEditing,
        RemoveFormat,
        SourceEditing,
        SpecialCharacters,
        SpecialCharactersEssentials,
        Style,
        Table,
        TableCaption,
        TableCellProperties,
        TableColumnResize,
        TableProperties,
        TableToolbar,
        TextTransformation,
        Undo
      ],
      toolbar: {
        items: [
          'heading',
          '|',
          'bold',
          'italic',
          'bulletedList',
          'numberedList',
          'removeFormat',
          '|',
          'outdent',
          'indent',
          'undo',
          'redo',
          '|',
          'link',
          'insertImage',
          'insertTable',
          'mediaEmbed',
          'specialCharacters',
          '|',
          'findAndReplace',
          'sourceEditing'
        ],
        shouldNotGroupWhenFull: true
      },
      language: {
        ui: 'en',
        content: 'en'
      },
      image: {
        toolbar: [
          'imageTextAlternative',
          'toggleImageCaption',
          '|',
          'imageStyle:inline',
          'imageStyle:block',
          'imageStyle:side',
          '|',
          'editImage'
        ],
        insert: {
          integrations: ['upload', 'assetManager']
        }
      },
      table: {
        defaultHeadings: { rows: 1 },
        contentToolbar: [
          'tableColumn',
          'tableRow',
          'mergeTableCells',
          'tableProperties',
          'tableCellProperties'
        ]
      }
    }
  }

  // _init(editor) {
  //   this.editor = editor
  //   const self = this
  //   if (undefined !== this.options.styleSet && this.options.styleSet.length > 0) {
  //     editor.ui.element.classList.add(`ems-styleset-${this.options.styleSet}`)
  //   }
  //   if (undefined !== this.options.onChangeEvent && this.options.onChangeEvent.length > 0) {
  //     editor.editing.view.document.on(this.options.onChangeEvent, () => {
  //       self._change()
  //     })
  //   }
  //   if (undefined !== this.options.height && this.options.height > 0) {
  //     editor.editing.view.change((writer) => {
  //       writer.setStyle(
  //         'height',
  //         `${this.options.height}px`,
  //         editor.editing.view.document.getRoot()
  //       )
  //     })
  //   }
  // }
  //
  // _change() {
  //   this.editor.updateSourceElement()
  //   const event = new ChangeEvent(this.element)
  //   event.dispatch()
  // }
  //
  // _applyStyleSet(options) {
  //   if (undefined === this.options.styleSet || this.options.styleSet === 0) {
  //     options.toolbar.items = options.toolbar.items.filter((e) => e !== 'style')
  //     return options
  //   }
  //   const styleSet = this.options.styleSet
  //   if (
  //     undefined === document.body.dataset.wysiwygInfo ||
  //     document.body.dataset.wysiwygInfo.length === 0
  //   ) {
  //     options.toolbar.items = options.toolbar.items.filter((e) => e !== 'style')
  //     return options
  //   }
  //   const config = JSON.parse(document.body.dataset.wysiwygInfo)
  //   if (undefined === config.styles || config.styles.length === 0) {
  //     options.toolbar.items = options.toolbar.items.filter((e) => e !== 'style')
  //     return options
  //   }
  //   for (let i = 0; i < config.styles.length; ++i) {
  //     if (config.styles[i].name !== styleSet || undefined === config.styles[i].config) {
  //       continue
  //     }
  //     if (!options.toolbar.items.includes('style')) {
  //       options.toolbar.items.unshift('style')
  //     }
  //     options.style = {
  //       definitions: config.styles[i].config
  //     }
  //     break
  //   }
  //   return options
  // }
  //
  // _applyHeadings(options) {
  //   if (undefined === this.options.formatTags || this.options.formatTags.length === 0) {
  //     return options
  //   }
  //
  //   try {
  //     const formatTags = JSON.parse(this.options.formatTags)
  //     options.heading.options = formatTags
  //   } catch (e) {
  //     console.error(
  //       `The format tags option expect an JSON, did you migrated it? Got: ${this.options.formatTags}`
  //     )
  //   }
  //
  //   return options
  // }
  //
  // _applyLang(options) {
  //   if (undefined !== this.options.lang && this.options.lang.length > 0) {
  //     options.language.content = this.options.lang
  //   }
  //   return options
  // }
  //
  // _applyProfile(options) {
  //   if (
  //     undefined === document.body.dataset.wysiwygInfo ||
  //     (document.body.dataset.wysiwygInfo === 0) | length
  //   ) {
  //     return options
  //   }
  //
  //   try {
  //     const profile = JSON.parse(document.body.dataset.wysiwygInfo)
  //     if (typeof profile.config !== 'object') {
  //       return options
  //     }
  //
  //     return { ...options, ...profile.config }
  //   } catch (e) {
  //     console.error(`Impossible to apply the WYSIWYG profile: ${e}`)
  //   }
  //
  //   return options
  // }
}
