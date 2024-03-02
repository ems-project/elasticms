import { ClassicEditor } from '@ckeditor/ckeditor5-editor-classic'
import { SourceEditing } from '@ckeditor/ckeditor5-source-editing'
import { Essentials } from '@ckeditor/ckeditor5-essentials'
import { Autoformat } from '@ckeditor/ckeditor5-autoformat'
import { Bold, Italic } from '@ckeditor/ckeditor5-basic-styles'
import { BlockQuote } from '@ckeditor/ckeditor5-block-quote'
import { Heading } from '@ckeditor/ckeditor5-heading'
import { Link } from '@ckeditor/ckeditor5-link'
import { List } from '@ckeditor/ckeditor5-list'
import { Paragraph } from '@ckeditor/ckeditor5-paragraph'
import { GeneralHtmlSupport } from '@ckeditor/ckeditor5-html-support'
import ChangeEvent from '../events/changeEvent'

export default class Cke5 {
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
      plugins: [
        Autoformat,
        SourceEditing,
        GeneralHtmlSupport,
        Essentials,
        Bold,
        Italic,
        BlockQuote,
        Heading,
        Link,
        List,
        Paragraph
      ],
      toolbar: [
        'heading',
        'bold',
        'italic',
        'link',
        'bulletedList',
        'numberedList',
        'blockQuote',
        'sourceEditing',
        'undo',
        'redo'
      ]
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
