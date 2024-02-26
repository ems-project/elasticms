import { ClassicEditor } from '@ckeditor/ckeditor5-editor-classic'
import { Essentials } from '@ckeditor/ckeditor5-essentials'
import { Autoformat } from '@ckeditor/ckeditor5-autoformat'
import { Bold, Italic } from '@ckeditor/ckeditor5-basic-styles'
import { BlockQuote } from '@ckeditor/ckeditor5-block-quote'
import { Heading } from '@ckeditor/ckeditor5-heading'
import { Link } from '@ckeditor/ckeditor5-link'
import { List } from '@ckeditor/ckeditor5-list'
import { Paragraph } from '@ckeditor/ckeditor5-paragraph'

class WYSIWYG {
  load (target) {
    this.loadInAdminUI(target)
    this.loadInRevision(target)
  }

  loadInRevision (target) {
    const wysiwygs = target.querySelectorAll('.ckeditor_ems')
    for (let i = 0; i < wysiwygs.length; ++i) {
      console.log(wysiwygs[i])
      this.createEditor(wysiwygs[i], {
        plugins: [
          Essentials,
          Autoformat,
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
          'undo',
          'redo'
        ]
      })
    }
  }

  loadInAdminUI (target) {
    const wysiwygs = target.querySelectorAll('.ckeditor')
    for (let i = 0; i < wysiwygs.length; ++i) {
      this.createEditor(wysiwygs[i], {
        plugins: [
          Essentials,
          Autoformat,
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
          'undo',
          'redo'
        ]
      })
    }
  }

  createEditor (wysiwyg, option) {
    ClassicEditor
      .create(wysiwyg, option)
      .then(editor => {
        console.log(editor)
      })
      .catch(error => {
        console.error(error)
      })
  }
}

export default WYSIWYG
