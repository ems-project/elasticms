'use strict'
import queryString from '../helpers/queryString'

export default class Modal {
  constructor () {
    this.autoOpenModal()
  }

  autoOpenModal () {
    const queryStringObject = queryString()
    if (queryStringObject.open) {
      const modalElement = document.getElementById(`content_type_structure_fieldType${queryStringObject.open}`)
      const modal = window.bootstrap.Modal.getOrCreateInstance(modalElement)
      modal.show()
    }
  }
}
