import Datatable from './plugins/datatable'
import Image from './plugins/image'
import JsonMenuNested from './plugins/jsonMenuNested'
import MediaLibrary from './plugins/mediaLibrary'
import Select from './plugins/select'
import Sortable from './plugins/sortable'
import WYSIWYG from './plugins/wysiwyg'

class Core {
  constructor () {
    this._domListeners = [
      new Datatable(),
      new Image(),
      new JsonMenuNested(),
      new MediaLibrary(),
      new Select(),
      new Sortable(),
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
