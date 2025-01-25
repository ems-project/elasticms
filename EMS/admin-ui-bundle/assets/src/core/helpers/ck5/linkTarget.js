import { Plugin } from 'ckeditor5'

export class LinkTarget extends Plugin {
  init() {
    const editor = this.editor

    editor.model.schema.extend('$text', { allowAttributes: 'linkTarget' })

    editor.conversion.for('downcast').attributeToElement({
      model: 'linkTarget',
      view: (attributeValue, caster) => {
        return caster.writer.createAttributeElement(
          'a',
          { target: attributeValue },
          { priority: 5 }
        )
      },
      converterPriority: 'low'
    })

    editor.conversion.for('upcast').attributeToAttribute({
      view: {
        name: 'a',
        key: 'target'
      },
      model: 'linkTarget',
      converterPriority: 'low'
    })
  }
}
