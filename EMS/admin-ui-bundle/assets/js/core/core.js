import Image from './plugins/image'
import Select from './plugins/select'
import Sortable from './plugins/sortable'
import Datatable from './plugins/datatable'
import WYSIWYG from './plugins/wysiwyg'

class Core {
  constructor () {
    this._domListeners = [
      new Datatable(),
      new Image(),
      new Select(),
      new Sortable(),
      new WYSIWYG()
    ]
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
