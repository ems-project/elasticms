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

class WYSIWYG {
  load (target) {
    this.loadInAdminUI(target)
    this.loadInRevision(target)
  }

  loadInRevision (target) {
    const wysiwygs = target.querySelectorAll('.ckeditor_ems')
    for (let i = 0; i < wysiwygs.length; ++i) {
      this.createEditor(wysiwygs[i], this.buildOptions(), wysiwygs[i].dataset.stylesSet)
    }
  }

  loadInAdminUI (target) {
    const wysiwygs = target.querySelectorAll('.ckeditor')
    for (let i = 0; i < wysiwygs.length; ++i) {
      this.createEditor(wysiwygs[i], this.buildOptions())
    }
  }

  buildOptions () {
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

  createEditor (wysiwyg, option, styleSet) {
    ClassicEditor
      .create(wysiwyg, option)
      .then(editor => {
        if (undefined !== styleSet && styleSet.length > 0) {
          editor.ui.element.classList.add(`ems-styleset-${styleSet}`)
        }
      })
      .catch(error => {
        console.error(error)
      })
  }
}

export default WYSIWYG
