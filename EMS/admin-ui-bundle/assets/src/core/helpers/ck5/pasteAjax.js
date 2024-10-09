export class PasteAjax {
  constructor(editor) {
    this.editor = editor
    this.pasteUrl = editor.config.get('emsAjaxPaste')
  }

  pluginName() {
    return 'PasteAjax'
  }

  requires() {
    return []
  }

  init() {
    if (undefined === this.pasteUrl || this.pasteUrl === null) {
      return
    }
    const editingView = this.editor.editing.view
    const self = this
    editingView.document.on('clipboardInput', (evt, data) => self._pasteEvent(evt, data))
  }

  async _pasteEvent(evt, data) {
    const pastedText = data.dataTransfer.getData('text/html')
    if (this.editor.isReadOnly || undefined === pastedText || pastedText.length === 0) {
      return
    }

    const json = await fetch(this.pasteUrl, {
      method: 'POST',
      body: JSON.stringify({ content: pastedText }),
      headers: { 'Content-Type': 'application/json' }
    })
      .then(async (response) => {
        return await response.json()
      })
      .catch(() => {
        console.error('error pasting')
      })
    data.content = this.editor.data.htmlProcessor.toView(json.content)
  }
}
