'use strict'
import queryString from '../helpers/queryString'
import { Modal as BSModal } from 'bootstrap'

export default class Modal {
  constructor () {
    this.autoOpenModal()
  }

  autoOpenModal () {
    const queryStringObject = queryString()
    if (queryStringObject.open) {
      const modalElement = document.getElementById(`content_type_structure_fieldType${queryStringObject.open}`)
      const modal = BSModal.getOrCreateInstance(modalElement)
      modal.show()
    }
  }
}
