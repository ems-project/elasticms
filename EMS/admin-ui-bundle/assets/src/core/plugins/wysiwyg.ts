import { EditorRevisionOptions } from '../helpers/editorRevisionOptions.ts'

class WYSIWYG {
  editors: object[] = []

  load(target: HTMLElement) {
    this.loadInAdminUI(target)
    // this.loadInRevision(target)
  }

  // loadInRevision(target) {
  //   const wysiwygs = target.querySelectorAll('.ckeditor_ems')
  //   for (let i = 0; i < wysiwygs.length; ++i) {
  //     this.createEditor(wysiwygs[i], {
  //       onChangeEvent: 'keyup',
  //       styleSet: wysiwygs[i].dataset.stylesSet,
  //       formatTags: wysiwygs[i].dataset.formatTags,
  //       contentCss: wysiwygs[i].dataset.contentCss,
  //       height: wysiwygs[i].dataset.height,
  //       referrerEmsId: wysiwygs[i].dataset.referrerEmsId,
  //       tableDefaultCss: wysiwygs[i].dataset.tableDefaultCss,
  //       lang: wysiwygs[i].dataset.lang
  //     })
  //   }
  // }

  loadInAdminUI(target: HTMLElement): void {
    const wysiwygs = target.querySelectorAll('.ckeditor')
    for (let i = 0; i < wysiwygs.length; ++i) {
      const element = wysiwygs.item(i)
      if (!(element instanceof HTMLElement)) {
        console.warn('Unexpected non HTMLElement object')
        continue
      }
      this.createEditor(element)
    }
  }

  async createEditor(
    element: HTMLElement,
    options: EditorRevisionOptions | null = null
  ): Promise<void> {
    const Editor = await import('../helpers/editor.ts')
    this.editors.push(new Editor.default(element, options))
  }
}

export default WYSIWYG
