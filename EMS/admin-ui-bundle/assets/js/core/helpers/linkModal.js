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

  _loadModal (value) {
    console.log(`Current selected url ${value}`)
  }
}
