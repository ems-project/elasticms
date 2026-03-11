import '../../../css/core/components/_inline_editor.scss'

import { InlineEditor } from './editor/editor'

const iframe = document.getElementById('preview-iframe') as HTMLIFrameElement
const baseUrl = document.body.dataset.baseUrl as string

new InlineEditor({
  baseUrl: baseUrl,
  iframe: iframe,
  currentUrl: iframe.src
})
