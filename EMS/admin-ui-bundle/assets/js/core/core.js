import Choice from './plugins/choice'
import CodeEditor from './plugins/codeEditor'
import Datatable from './plugins/datatable'
import File from './plugins/file'
import Image from './plugins/image'
import JsonMenuNested from './plugins/jsonMenuNested'
import MediaLibrary from './plugins/mediaLibrary'
import Select from './plugins/select'
import Sortable from './plugins/sortable'
import Tooltip from './plugins/tooltip'
import WYSIWYG from './plugins/wysiwyg'

class Core {
  constructor () {
    this._domListeners = [
      new Choice(),
      new CodeEditor(),
      new Datatable(),
      new File(),
      new Image(),
      new JsonMenuNested(),
      new MediaLibrary(),
      new Select(),
      new Sortable(),
      new Tooltip(),
      new WYSIWYG()
    ]
    document.addEventListener('emsAddedDomEvent', (event) => this.load(event.target))
    this.documentReady()
  }

  load (target) {
    if (target === undefined) {
      console.log('Impossible to add ems listeners as no target is defined')
      return
    }
    this._domListeners.forEach((element) => element.load(target))
  }

  documentReady () {
    if (document.readyState === 'complete' || document.readyState === 'interactive') {
      setTimeout(this.load(document), 1)
    } else {
      document.addEventListener('DOMContentLoaded', this.load(document))
    }
  }
}

const core = new Core()

export default core
