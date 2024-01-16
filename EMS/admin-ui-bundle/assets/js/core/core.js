import Button from './plugins/button'
import Choice from './plugins/choice'
import CodeEditor from './plugins/codeEditor'
import CollapsibleCollection from './plugins/collapsibleCollection'
import Datatable from './plugins/datatable'
import File from './plugins/file'
import Image from './plugins/image'
import JsonMenuNested from './plugins/jsonMenuNested'
import MediaLibrary from './plugins/mediaLibrary'
import NestedSortable from './plugins/nestedSortable'
import ObjectPicker from './plugins/objectPicker'
import Select from './plugins/select'
import SortableList from './plugins/sortableList'
import SymfonyCollection from './plugins/symfonyCollection'
import Tooltip from './plugins/tooltip'
import WYSIWYG from './plugins/wysiwyg'

import { EMS_ADDED_DOM_EVENT } from './events/addedDomEvent'

class Core {
  constructor () {
    this._domListeners = [
      new Button(),
      new Choice(),
      new CodeEditor(),
      new CollapsibleCollection(),
      new Datatable(),
      new File(),
      new Image(),
      new JsonMenuNested(),
      new MediaLibrary(),
      new NestedSortable(),
      new ObjectPicker(),
      new Select(),
      new SortableList(),
      new SymfonyCollection(),
      new Tooltip(),
      new WYSIWYG()
    ]
    document.addEventListener(EMS_ADDED_DOM_EVENT, (event) => this.load(event.target))
    this.coreReady()
  }

  load (target) {
    if (target === undefined) {
      console.log('Impossible to add ems listeners as no target is defined')
      return
    }
    this._domListeners.forEach((element) => element.load(target))
  }

  coreReady () {
    if (document.readyState === 'complete' || document.readyState === 'interactive') {
      setTimeout(this.load(document), 1)
    } else {
      document.addEventListener('DOMContentLoaded', this.load(document))
    }
  }
}

const core = new Core()

export default core
