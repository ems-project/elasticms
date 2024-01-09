import Image from './plugins/image'
import Select from './plugins/select'
import Sortable from './plugins/sortable'

class Core {
  constructor () {
    this._domListeners = [
      new Image(),
      new Select(),
      new Sortable()
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
