import { ClassicEditor } from '@ckeditor/ckeditor5-editor-classic'

import { Alignment } from '@ckeditor/ckeditor5-alignment'
import { Autoformat } from '@ckeditor/ckeditor5-autoformat'
import { Bold, Italic } from '@ckeditor/ckeditor5-basic-styles'
import { BlockQuote } from '@ckeditor/ckeditor5-block-quote'
import { Essentials } from '@ckeditor/ckeditor5-essentials'
import { FindAndReplace } from '@ckeditor/ckeditor5-find-and-replace'
import { FontColor, FontFamily, FontSize } from '@ckeditor/ckeditor5-font'
import { Heading } from '@ckeditor/ckeditor5-heading'
import { GeneralHtmlSupport } from '@ckeditor/ckeditor5-html-support'
import {
  Image,
  ImageCaption,
  ImageStyle,
  ImageToolbar,
  ImageResizeEditing,
  ImageResizeHandles,
  ImageUpload,
  PictureEditing
} from '@ckeditor/ckeditor5-image'
import { Indent } from '@ckeditor/ckeditor5-indent'
import { Link } from '@ckeditor/ckeditor5-link'
import { List } from '@ckeditor/ckeditor5-list'
import { MediaEmbed } from '@ckeditor/ckeditor5-media-embed'
import { Paragraph } from '@ckeditor/ckeditor5-paragraph'
import { RemoveFormat } from '@ckeditor/ckeditor5-remove-format'
import { SourceEditing } from '@ckeditor/ckeditor5-source-editing'
import {
  SpecialCharacters,
  SpecialCharactersEssentials
} from '@ckeditor/ckeditor5-special-characters'
import { Style } from '@ckeditor/ckeditor5-style'
import {
  Table,
  TableCaption,
  TableCellProperties,
  TableProperties,
  TableColumnResize,
  TableToolbar
} from '@ckeditor/ckeditor5-table'
import { TextTransformation } from '@ckeditor/ckeditor5-typing'
import { Undo } from '@ckeditor/ckeditor5-undo'

import { UploadAdapter } from './ck5/uploadAdapter'
import { PasteAjax } from './ck5/pasteAjax'

import ChangeEvent from '../events/changeEvent'

function initUploadAdaptor (editor) {
  editor.plugins.get('FileRepository').createUploadAdapter = (loader) => {
    return new UploadAdapter(loader)
  }
}

export default class Editor {
  constructor (element, options) {
    const self = this
    this.element = element
    this.options = options
    ClassicEditor
      .create(element, this.buildCke5Options())
      .then(editor => {
        self._init(editor)
      })
      .catch(error => {
        console.error(error)
      })
  }

  buildCke5Options () {
    let options = this.getDefaultOptions()
    options = this._applyStyleSet(options)
    options = this._applyHeadings(options)
    options = this._applyLang(options)

    return options
  }

  getDefaultOptions () {
    return {
      heading: {
        options: [
          { model: 'paragraph', title: 'Paragraph', class: '' },
          { model: 'heading2', view: 'h2', title: 'Heading 2', class: '' },
          { model: 'heading3', view: 'h3', title: 'Heading 3', class: '' }
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
      extraPlugins: [
        initUploadAdaptor
      ],
      plugins: [
        Alignment,
        Autoformat,
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
        PasteAjax,
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
          'imageUpload',
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
          'imageStyle:inline',
          'imageStyle:block',
          'imageStyle:side'
        ]
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

  _init (editor) {
    this.editor = editor
    const self = this
    if (undefined !== this.options.styleSet && this.options.styleSet.length > 0) {
      editor.ui.element.classList.add(`ems-styleset-${this.options.styleSet}`)
    }
    if (undefined !== this.options.onChangeEvent && this.options.onChangeEvent.length > 0) {
      editor.editing.view.document.on(this.options.onChangeEvent, () => {
        self._change()
      })
    }
    if (undefined !== this.options.height && this.options.height > 0) {
      editor.editing.view.change(writer => {
        writer.setStyle('height', `${this.options.height}px`, editor.editing.view.document.getRoot())
      })
    }
  }

  _change () {
    this.editor.updateSourceElement()
    const event = new ChangeEvent(this.element)
    event.dispatch()
  }

  _applyStyleSet (options) {
    if (undefined === this.options.styleSet || this.options.styleSet === 0) {
      return options
    }
    const styleSet = this.options.styleSet
    if (undefined === document.body.dataset.wysiwygInfo || document.body.dataset.wysiwygInfo.length === 0) {
      return options
    }
    const config = JSON.parse(document.body.dataset.wysiwygInfo)
    if (undefined === config.styles || config.styles.length === 0) {
      return options
    }
    for (let i = 0; i < config.styles.length; ++i) {
      if (config.styles[i].name !== styleSet || undefined === config.styles[i].config) {
        continue
      }
      options.toolbar.items.unshift('style')
      options.style = {
        definitions: config.styles[i].config
      }
      break
    }
    return options
  }

  _applyHeadings (options) {
    if (undefined === this.options.formatTags || this.options.formatTags.length === 0) {
      return options
    }

    try {
      const formatTags = JSON.parse(this.options.formatTags)
      options.heading.options = formatTags
    } catch (e) {
      console.error(`The format tags option expect an JSON, did you migrated it? Got: ${this.options.formatTags}`)
    }

    return options
  }

  _applyLang (options) {
    if (undefined !== this.options.lang && this.options.lang.length > 0) {
      options.language.content = this.options.lang
    }
    return options
  }
}
