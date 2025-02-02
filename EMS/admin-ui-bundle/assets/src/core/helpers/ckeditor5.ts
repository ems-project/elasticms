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
  ImageStyle,
  ImageToolbar,
  ImageResizeEditing,
  ImageResizeHandles,
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
import { EditorOptions } from './editorOptions.ts'
import { EditorRevisionOptions } from './editorRevisionOptions.ts'

// import { Link } from './ckeditor5-link/src/index'
// import { AssetManager } from './ckeditor5-assetmanager/src/index'
// import { UploadAdapter } from './ck5/uploadAdapter'
// import { PasteAjax } from './ck5/pasteAjax'
// import { LinkTarget } from './ck5/linkTarget'

import ChangeEvent from '../events/changeEvent'

// function initUploadAdaptor(editor: ClassicEditor) {
//   editor.plugins.get('FileRepository').createUploadAdapter = (loader) => {
//     return new UploadAdapter(loader)
//   }
// }
import 'ckeditor5/ckeditor5.css'

export default class Ckeditor5 {
  private options: EditorRevisionOptions
  private editor: ClassicEditor | null
  private element: HTMLElement
  constructor(element: HTMLElement, options: EditorRevisionOptions | null) {
    this.options = options ?? ({} as EditorRevisionOptions)
    this.element = element
    this.editor = null
    this.create(element)
  }

  async create(element: HTMLElement) {
    const self = this
    ClassicEditor.create(element, await this.buildCke5Options())
      .then((editor: ClassicEditor) => {
        self._init(editor)
      })
      .catch((error) => {
        console.error(error)
      })
  }

  async buildCke5Options(): Promise<EditorOptions> {
    let options = await this.getDefaultOptions()
    options = this._applyProfile(options)
    options = this._applyStyleSet(options)
    options = this._applyHeadings(options)
    options = this._applyLang(options)

    return options
  }

  async getDefaultOptions(): Promise<EditorOptions> {
    return {
      licenseKey: 'GPL',
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
    } as EditorOptions
  }

  _init(editor: ClassicEditor) {
    this.editor = editor
    const self = this
    if (null !== this.options.styleSet && this.options.styleSet.length > 0) {
      editor.ui.element?.classList?.add(`ems-styleset-${this.options.styleSet}`)
    }
    if (null !== this.options.onChangeEvent && this.options.onChangeEvent.length > 0) {
      editor.editing.view.document.on(this.options.onChangeEvent, () => {
        self._change()
      })
    }
    if (null !== this.options.height && this.options.height > 0) {
      editor.editing.view.change((writer) => {
        const root = editor.editing.view.document.getRoot()
        if (null === root) {
          return
        }
        writer.setStyle('height', `${this.options.height}px`, root)
      })
    }
  }

  _getEditor(): ClassicEditor {
    if (null === this.editor) {
      throw new Error('Unexpected null editor')
    }
    return this.editor
  }

  _change() {
    this._getEditor().updateSourceElement()
    const event = new ChangeEvent(this.element)
    event.dispatch()
  }

  _applyStyleSet(options: EditorOptions): EditorOptions {
    if (null === this.options.styleSet || this.options.styleSet.length === 0) {
      options.toolbar.items = options.toolbar.items.filter((e: any): boolean => e !== 'style')
      return options
    }
    const styleSet = this.options.styleSet
    if (
      undefined === document.body.dataset.wysiwygInfo ||
      document.body.dataset.wysiwygInfo.length === 0
    ) {
      options.toolbar.items = options.toolbar.items.filter((e: any): boolean => e !== 'style')
      return options
    }
    const config = JSON.parse(document.body.dataset.wysiwygInfo)
    if (undefined === config.styles || config.styles.length === 0) {
      options.toolbar.items = options.toolbar.items.filter((e: any): boolean => e !== 'style')
      return options
    }
    for (let i = 0; i < config.styles.length; ++i) {
      if (config.styles[i].name !== styleSet || undefined === config.styles[i].config) {
        continue
      }
      if (!options.toolbar.items.includes('style')) {
        options.toolbar.items.unshift('style')
      }
      options.style = {
        definitions: config.styles[i].config
      }
      break
    }
    return options
  }

  _applyHeadings(options: EditorOptions): EditorOptions {
    if (null === this.options.formatTags || this.options.formatTags.length === 0) {
      return options
    }

    try {
      const formatTags = JSON.parse(this.options.formatTags)
      options.heading.options = formatTags
    } catch {
      console.warn(
        `The format tags option expect an JSON, did you migrated it? Got: ${this.options.formatTags}`
      )
      const formatTags: any[] = []
      this.options.formatTags.split(';').forEach((tag) => {
        formatTags.push({
          model: tag,
          view: tag,
          title: tag,
          class: ''
        })
      })
      options.heading.options = formatTags
    }

    return options
  }

  _applyLang(options: EditorOptions): EditorOptions {
    if (null !== this.options.lang && this.options.lang.length > 0) {
      options.language.content = this.options.lang
    }
    return options
  }

  _applyProfile(options: EditorOptions): EditorOptions {
    if (
      undefined === document.body.dataset.wysiwygInfo ||
      document.body.dataset.wysiwygInfo.length === 0
    ) {
      return options
    }

    try {
      const profile = JSON.parse(document.body.dataset.wysiwygInfo)
      if (typeof profile.config !== 'object') {
        return options
      }

      return { ...options, ...profile.config }
    } catch (e) {
      console.error(`Impossible to apply the WYSIWYG profile: ${e}`)
    }

    return options
  }
}
