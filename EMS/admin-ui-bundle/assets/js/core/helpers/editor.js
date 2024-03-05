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
  TableColumnResize,
  TableToolbar
} from '@ckeditor/ckeditor5-table'
import { TextTransformation } from '@ckeditor/ckeditor5-typing'
import { Undo } from '@ckeditor/ckeditor5-undo'

import { UploadAdapter } from './ck5/uploadAdapter'

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
    return {
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
      language: 'en',
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
        contentToolbar: [
          'tableColumn',
          'tableRow',
          'mergeTableCells',
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
  }

  _change () {
    this.editor.updateSourceElement()
    const event = new ChangeEvent(this.element)
    event.dispatch()
  }
}
