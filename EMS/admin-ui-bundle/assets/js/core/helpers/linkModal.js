import ajaxRequest from '../components/ajaxRequest'
import AddedDomEvent from '../events/addedDomEvent'
import SelectLinkEvent from '../events/selectLinkEvent'
import Link from './link'
import { EMS_FORM_RESPONSE_EVENT_EVENT } from '../events/formResponseEvent'

export default class LinkModal {
  constructor () {
    this.linkModal = document.getElementById('link-modal')
    this.modal = new window.bootstrap.Modal(this.linkModal, {
      keyboard: false,
      backdrop: 'static'
    })
  }

  show (value, target = null) {
    this.modal.show()
    this._loadModal(value, target)
  }

  setLoading (showLoading) {
    const loading = this.linkModal.querySelector('.modal-loading')
    const body = this.linkModal.querySelector('.ajax-modal-body')
    if (showLoading) {
      loading.style.display = 'block'
      body.style.display = 'none'
    } else {
      loading.style.display = 'none'
      body.style.display = 'block'
    }
  }

  closeModal () {
    this.modal.hide()
  }

  isVisible () {
    return this.modal._isShown
  }

  _loadModal (value, target) {
    const self = this
    const link = new Link(value)
    ajaxRequest.post(this.linkModal.dataset.modalInitUrl, { url: link.href, target })
      .success(response => self._treatResponse(response))
  }

  _treatResponse (response) {
    const self = this
    const body = this.linkModal.querySelector('.ajax-modal-body')
    body.innerHTML = response.body
    this.setLoading(false)
    const event = new AddedDomEvent(body)
    event.dispatch()
    const forms = body.querySelectorAll('form')
    for (let i = 0; i < forms.length; ++i) {
      forms[i].addEventListener(EMS_FORM_RESPONSE_EVENT_EVENT, (event) => self._onResponse(event))
    }
  }

  _onResponse (event) {
    this.closeModal()
    const selectEvent = new SelectLinkEvent(event.detail.response.url, event.detail.response.target)
    selectEvent.dispatch()
  }
}
