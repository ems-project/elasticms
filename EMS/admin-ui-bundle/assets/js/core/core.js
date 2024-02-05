import Button from './plugins/button'
import Choice from './plugins/choice'
import CodeEditor from './plugins/codeEditor'
import CollapsibleCollection from './plugins/collapsibleCollection'
import Datatable from './plugins/datatable'
import File from './plugins/file'
import Form from './plugins/form'
import Iframe from './plugins/iframe'
import Image from './plugins/image'
import Job from './plugins/job'
import JsonMenuNested from './plugins/jsonMenuNested'
import MediaLibrary from './plugins/mediaLibrary'
import NestedSortable from './plugins/nestedSortable'
import ObjectPicker from './plugins/objectPicker'
import Select from './plugins/select'
import SortableList from './plugins/sortableList'
import SymfonyCollection from './plugins/symfonyCollection'
import Text from './plugins/text'
import Tooltip from './plugins/tooltip'
import WYSIWYG from './plugins/wysiwyg'

import RevisionTask from './components/revisionTask'

import { EMS_ADDED_DOM_EVENT } from './events/addedDomEvent'

class Core {
  constructor () {
    this._statusUpdateUrl = document.body.getAttribute('data-status-url')
    this._domListeners = [
      new Button(),
      new Choice(),
      new CodeEditor(),
      new CollapsibleCollection(),
      new Datatable(),
      new File(),
      new Form(),
      new Iframe(),
      new Image(),
      new Job(),
      new JsonMenuNested(),
      new MediaLibrary(),
      new NestedSortable(),
      new ObjectPicker(),
      new Select(),
      new SortableList(),
      new SymfonyCollection(),
      new Text(),
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
    this.initStatusRefresh()
    this.components = [
      new RevisionTask()
    ]
  }

  initStatusRefresh () {
    const self = this
    window.setInterval(function () {
      self.updateStatus()
    }, 180000)
  }

  updateStatus () {
    const xhr = new XMLHttpRequest()
    xhr.open('GET', this._statusUpdateUrl, true)

    xhr.onreadystatechange = function (event) {
      if (this.readyState !== 4) {
        return
      }

      const statusLink = document.getElementById('status-overview')
      if (this.status === 200) {
        const json = JSON.parse(xhr.responseText)
        statusLink.innerHTML = json.body
        statusLink.setAttribute('title', json.title)
      } else {
        statusLink.setAttribute('title', `Error ${xhr.status}`)
        statusLink.innerHTML = `<i class="fa fa-circle fa-2xs text-red"></i><span class="visually-hidden">Error ${xhr.status}</span>`
      }
    }

    xhr.send()
  }
}

const core = new Core()

export default core
