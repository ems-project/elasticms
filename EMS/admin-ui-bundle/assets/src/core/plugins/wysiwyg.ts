import { EditorRevisionOptions } from '../helpers/editorRevisionOptions.ts'

class WYSIWYG {
  editors: object[] = []

  load(target: HTMLElement) {
    this.loadInAdminUI(target)
    this.loadInRevision(target)
  }

  loadInRevision(target: HTMLElement) {
    const wysiwygs = target.querySelectorAll('.ckeditor_ems')
    for (let i = 0; i < wysiwygs.length; ++i) {
      const element = wysiwygs.item(i)
      const height = element.getAttribute('data-height')
      this.createEditor(element as HTMLElement, {
        onChangeEvent: 'keyup',
        styleSet: element.getAttribute('data-styles-set'),
        formatTags: element.getAttribute('data-format-tags'),
        contentCss: element.getAttribute('data-content-css'),
        height: null === height ? null : Number.parseInt(height),
        referrerEmsId: element.getAttribute('data-referrer-ems-id'),
        tableDefaultCss: element.getAttribute('data-table-default-css'),
        lang: element.getAttribute('data-lang')
      })
    }
  }

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
    if (undefined === document.body.dataset.wysiwygInfo) {
      console.error('WysiwygInfo is missing')
      return
    }
    const profile = JSON.parse(document.body.dataset.wysiwygInfo)
    if (typeof profile.editor !== "string") {
      console.error('Editor is not defined')
      return
    }
    const Editor = await import(`../helpers/${profile.editor}.ts`)
    this.editors.push(new Editor.default(element, options, profile))
  }
}

export default WYSIWYG
