import ajaxRequest from '../components/ajaxRequest'
import AddedDomEvent from '../events/addedDomEvent'
import SelectLinkEvent from '../events/selectLinkEvent'
import Link from './link'
import { EMS_FORM_RESPONSE_EVENT_EVENT } from '../events/formResponseEvent'
import { EMS_FORM_FAIL_EVENT_EVENT } from '../events/formFailEvent'

export default class CkeModal {
  constructor (initDatasetAttr, title) {
    this.modalElement = document.getElementById('cke-modal')
    const titleElement = document.getElementById('cke-modal-title')
    titleElement.innerHTML = title
    this.postUrl = this.modalElement.dataset[initDatasetAttr]
    this.modal = new window.bootstrap.Modal(this.modalElement, {
      keyboard: false,
      backdrop: 'static'
    })
  }

  show (value, target = null, content = null) {
    this.modal.show()
    this._loadModal(value, target, content)
  }

  setLoading (showLoading) {
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

  closeModal () {
    this.setLoading(true)
    this.modal.hide()
  }

  isVisible () {
    return this.modal._isShown
  }

  _loadModal (value, target, content) {
    const self = this
    const link = new Link(value)
    ajaxRequest.post(this.postUrl, { url: link.href, target, content })
      .success(response => self._treatResponse(response))
  }

  _treatResponse (response) {
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

  _onResponse (event) {
    this.closeModal()
    const selectEvent = new SelectLinkEvent(event.detail.response.url, event.detail.response.target)
    selectEvent.dispatch()
  }

  _onFail (event) {
    this._treatResponse(event.detail.response)
  }
}
