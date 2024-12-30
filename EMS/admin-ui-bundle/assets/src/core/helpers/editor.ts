import 'ckeditor5/ckeditor5.css'
import { EditorOptions } from './editorOptions.ts'
import { EditorRevisionOptions } from './editorRevisionOptions.ts'

// import { Link } from './ckeditor5-link/src/index'
// import { AssetManager } from './ckeditor5-assetmanager/src/index'
// import { UploadAdapter } from './ck5/uploadAdapter'
// import { PasteAjax } from './ck5/pasteAjax'
// import { LinkTarget } from './ck5/linkTarget'

import ChangeEvent from '../events/changeEvent'
import { ClassicEditor } from 'ckeditor5'

// function initUploadAdaptor(editor: ClassicEditor) {
//   editor.plugins.get('FileRepository').createUploadAdapter = (loader) => {
//     return new UploadAdapter(loader)
//   }
// }

export default class Editor {
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
    const ckeditor5 = await import('ckeditor5')
    const self = this
    ckeditor5.ClassicEditor.create(element, await this.buildCke5Options())
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
    const ckeditor5 = await import('ckeditor5')
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
        ckeditor5.Alignment,
        ckeditor5.Autoformat,
        // AssetManager,
        ckeditor5.BlockQuote,
        ckeditor5.Bold,
        ckeditor5.Essentials,
        ckeditor5.FindAndReplace,
        ckeditor5.FontColor,
        ckeditor5.FontFamily,
        ckeditor5.FontSize,
        ckeditor5.GeneralHtmlSupport,
        ckeditor5.Heading,
        ckeditor5.Image,
        ckeditor5.ImageCaption,
        ckeditor5.ImageInsertViaUrl,
        ckeditor5.ImageStyle,
        ckeditor5.ImageToolbar,
        ckeditor5.ImageResizeEditing,
        ckeditor5.ImageResizeHandles,
        ckeditor5.ImageUpload,
        ckeditor5.Indent,
        ckeditor5.Italic,
        ckeditor5.Link,
        // LinkTarget,
        ckeditor5.List,
        ckeditor5.MediaEmbed,
        ckeditor5.Paragraph,
        // PasteAjax,
        ckeditor5.PictureEditing,
        ckeditor5.RemoveFormat,
        ckeditor5.SourceEditing,
        ckeditor5.SpecialCharacters,
        ckeditor5.SpecialCharactersEssentials,
        ckeditor5.Style,
        ckeditor5.Table,
        ckeditor5.TableCaption,
        ckeditor5.TableCellProperties,
        ckeditor5.TableColumnResize,
        ckeditor5.TableProperties,
        ckeditor5.TableToolbar,
        ckeditor5.TextTransformation,
        ckeditor5.Undo
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
    } catch (e) {
      console.error(
        `The format tags option expect an JSON, did you migrated it? Got: ${this.options.formatTags}`
      )
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
