import Editor from '../helpers/editor'

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
        styleSet: wysiwygs[i].dataset.stylesSet,
        formatTags: wysiwygs[i].dataset.formatTags,
        contentCss: wysiwygs[i].dataset.contentCss,
        height: wysiwygs[i].dataset.height,
        referrerEmsId: wysiwygs[i].dataset.referrerEmsId,
        tableDefaultCss: wysiwygs[i].dataset.tableDefaultCss,
        lang: wysiwygs[i].dataset.lang
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
    this.editors.push(new Editor(element, options))
  }
}

export default WYSIWYG
