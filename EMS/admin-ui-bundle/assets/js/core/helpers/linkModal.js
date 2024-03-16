import ajaxRequest from '../components/ajaxRequest'
import AddedDomEvent from '../events/addedDomEvent'
import SelectLinkEvent from '../events/selectLinkEvent'

export default class LinkModal {
  constructor () {
    this.linkModal = document.getElementById('link-modal')
    this.modal = new window.bootstrap.Modal(this.linkModal, {
      keyboard: false,
      backdrop: 'static'
    })
  }

  show (value) {
    this.modal.show()
    this._loadModal(value)
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

  _loadModal (value) {
    const self = this
    ajaxRequest.post(this.linkModal.dataset.modalInitUrl, { url: value })
      .success(response => self._treatResponse(response))
  }

  _treatResponse (response) {
    const self = this
    const body = this.linkModal.querySelector('.ajax-modal-body')
    body.innerHTML = response.body
    this.setLoading(false)
    const event = new AddedDomEvent(body)
    event.dispatch()
    const links = body.querySelectorAll('.editor-link-picker')
    for (let i = 0; i < links.length; ++i) {
      links[i].addEventListener('click', (event) => self._onClick(event))
    }
  }

  _onClick (event) {
    event.preventDefault()
    if (undefined === event.target.href) {
      return
    }
    this.closeModal()
    const selectEvent = new SelectLinkEvent(event.target.href)
    selectEvent.dispatch()
  }
}
