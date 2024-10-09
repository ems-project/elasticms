'use strict'
import $ from 'jquery'
import queryString from '../helpers/queryString'
import ajaxModal from '../helpers/ajaxModal'
import { Modal as BootstrapModal } from 'bootstrap'

export default class Modal {
  constructor() {
    this.autoOpenModal()
    this.closeModalNotification()
    this.intAjaxModalLinks()
  }

  autoOpenModal() {
    const queryStringObject = queryString()
    if (queryStringObject.open) {
      const modalElement = document.getElementById(
        `content_type_structure_fieldType${queryStringObject.open}`
      )
      const modal = BootstrapModal.getOrCreateInstance(modalElement)
      modal.show()
    }
  }

  closeModalNotification() {
    $('#modal-notification-close-button').on('click', function () {
      $('#modal-notifications .modal-body').empty()
      $('#modal-notifications').modal('hide')
    })
  }

  intAjaxModalLinks() {
    const ajaxModalLinks = document.querySelectorAll('a[data-ajax-modal-url]')
    ;[].forEach.call(ajaxModalLinks, function (link) {
      link.onclick = (event) => {
        ajaxModal.load(
          {
            url: event.target.dataset.ajaxModalUrl,
            size: event.target.dataset.ajaxModalSize
          },
          (json) => {
            if (Object.hasOwn(json, 'success') && json.success === true) {
              location.reload()
            }
          }
        )
      }
    })
  }
}
