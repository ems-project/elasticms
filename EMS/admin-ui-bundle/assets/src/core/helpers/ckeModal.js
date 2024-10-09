import ajaxRequest from '../components/ajaxRequest'
import AddedDomEvent from '../events/addedDomEvent'
import SelectLinkEvent from '../events/selectLinkEvent'
import EditImageEvent from '../events/editImageEvent'
import { EMS_FORM_RESPONSE_EVENT_EVENT } from '../events/formResponseEvent'
import { EMS_FORM_FAIL_EVENT_EVENT } from '../events/formFailEvent'
import { Modal } from 'bootstrap'

export default class CkeModal {
  constructor(initDatasetAttr, title) {
    this.title = title
    this.modalElement = document.getElementById('cke-modal')
    this.postUrl = this.modalElement.dataset[initDatasetAttr]
    this.modal = new Modal(this.modalElement, {
      keyboard: false,
      backdrop: 'static'
    })
  }

  show(data) {
    const titleElement = document.getElementById('cke-modal-title')
    titleElement.innerHTML = this.title
    this.modal.show()
    this._loadModal(data)
  }

  setLoading(showLoading) {
    const loading = this.modalElement.querySelector('.modal-loading')
    const body = this.modalElement.querySelector('.ajax-modal-body')
    if (showLoading) {
      loading.style.display = 'block'
      body.style.display = 'none'
    } else {
      loading.style.display = 'none'
      body.style.display = 'block'
    }
  }

  closeModal() {
    this.setLoading(true)
    this.modal.hide()
  }

  isVisible() {
    return this.modal._isShown
  }

  _loadModal(data) {
    const self = this
    ajaxRequest.post(this.postUrl, data).success((response) => self._treatResponse(response))
  }

  _treatResponse(response) {
    const self = this
    const body = this.modalElement.querySelector('.ajax-modal-body')
    body.innerHTML = response.body
    this.setLoading(false)
    const event = new AddedDomEvent(body)
    event.dispatch()
    const forms = body.querySelectorAll('form')
    for (let i = 0; i < forms.length; ++i) {
      forms[i].addEventListener(EMS_FORM_RESPONSE_EVENT_EVENT, (event) => self._onResponse(event))
      forms[i].addEventListener(EMS_FORM_FAIL_EVENT_EVENT, (event) => self._onFail(event))
    }
  }

  _onResponse(event) {
    this.closeModal()
    switch (event.detail.response.type) {
      case 'select-link': {
        const selectEvent = new SelectLinkEvent(
          event.detail.response.url,
          event.detail.response.target
        )
        selectEvent.dispatch()
        break
      }
      case 'edit-image': {
        const editImageEvent = new EditImageEvent(event.detail.response.url)
        editImageEvent.dispatch()
        break
      }
      default:
        console.warn('Unknown response type')
    }
  }

  _onFail(event) {
    this._treatResponse(event.detail.response)
  }
}
