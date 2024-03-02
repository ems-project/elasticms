import Cke5 from '../helpers/cke5'

class WYSIWYG {
  editors = []

  load (target) {
    this.loadInAdminUI(target)
    this.loadInRevision(target)
  }

  loadInRevision (target) {
    const wysiwygs = target.querySelectorAll('.ckeditor_ems')
    for (let i = 0; i < wysiwygs.length; ++i) {
      this.createEditor(wysiwygs[i], {
        onChangeEvent: 'keyup',
        styleSet: wysiwygs[i].dataset.stylesSet
      })
    }
  }

  loadInAdminUI (target) {
    const wysiwygs = target.querySelectorAll('.ckeditor')
    for (let i = 0; i < wysiwygs.length; ++i) {
      this.createEditor(wysiwygs[i])
    }
  }

  createEditor (element, options = {}) {
    this.editors.push(new Cke5(element, options))
  }
}

export default WYSIWYG
