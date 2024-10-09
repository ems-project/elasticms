import { AddedDomEvent } from '../events/addedDomEvent'
import { tooltipDataLinks } from './tooltip'
import { Modal } from 'bootstrap'
import Promise from 'promise'

class AjaxModal {
  constructor(selector) {
    this.modal = document.querySelector(selector)
    this.bsModal = new Modal(this.modal)

    if (this.modal) {
      this.loadingElement = this.modal.querySelector('.modal-loading')
      this.modal.addEventListener('hide.bs.modal', (e) => {
        e.target.dispatchEvent(new Event('ajax-modal-close'))
      })
    }

    this.onKeyDown = (event) => {
      if (event.key !== 'Enter' || event.shiftKey) return

      const btnAjaxSubmit = this.modal.querySelector('#ajax-modal-submit')
      const blockTargetElements = ['textarea', 'input', 'select', 'button', 'a']

      if (
        btnAjaxSubmit &&
        !Array.from(blockTargetElements).includes(event.target.nodeName.toLowerCase()) &&
        !event.target.classList.contains('select2-selection')
      ) {
        event.preventDefault()
        btnAjaxSubmit.click()
      }
    }
  }

  getBodyElement() {
    return this.modal.querySelector('.ajax-modal-body')
  }

  close() {
    this.bsModal.hide()
  }

  reset() {
    this.loadingElement.style.display = 'block'
    document.removeEventListener('keydown', this.onKeyDown)

    this.modal.querySelectorAll('.ckeditor_ems').each(() => {
      // TODO
      /* if (CKEDITOR.instances.hasOwnProperty($(this).attr('id'))) {
        CKEDITOR.instances[$(this).attr('id')].destroy()
      } */
    })

    this.modal.querySelector('.modal-title').innerHTML = ''
    this.modal.querySelector('.ajax-modal-body').innerHTML = ''
    this.modal.querySelector('.ajax-modal-body').style.display = 'none'
    this.modal.querySelector('.ajax-modal-footer').innerHTML =
      '<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>'
  }

  stateLoading() {
    this.modal.querySelectorAll('.ajax-modal-body > div.alert').forEach((e) => {
      e.remove()
    })
    this.loadingElement.style.display = 'block'

    this.modal.querySelectorAll('input, button, .select2, textarea').forEach((e) => {
      e.classList.add('emsco-modal-has-been-disabled')
      e.setAttribute('disabled', 'disabled')
    })
  }

  stateReady() {
    this.loadingElement.style.display = 'none'
    this.modal.querySelector('.ajax-modal-body').style.display = 'block'

    this.modal
      .querySelectorAll(
        'input.emsco-modal-has-been-disabled, button.emsco-modal-has-been-disabled, .select2.emsco-modal-has-been-disabled, textarea.emsco-modal-has-been-disabled'
      )
      .forEach((e) => {
        e.removeAttribute('disabled')
        e.classList.remove('emsco-modal-has-been-disabled')
      })
  }

  load(options, callback) {
    const dialog = this.modal.querySelector('.modal-dialog')
    dialog.classList.remove('modal-xs', 'modal-sm', 'modal-md', 'modal-lg')
    if (Object.hasOwn(options, 'size')) {
      dialog.classList.add('modal-' + options.size)
    } else {
      dialog.classList.add('modal-md')
    }

    this.stateLoading()
    if (Object.hasOwn(options, 'title')) {
      this.modal.querySelector('.modal-title').innerHTML = options.title
    }
    this.bsModal.show()

    const fetchOptions = {
      method: 'GET',
      headers: { 'Content-Type': 'application/json' }
    }
    if (Object.hasOwn(options, 'data')) {
      fetchOptions.method = 'POST'
      fetchOptions.body = options.data
    }

    fetch(options.url, fetchOptions)
      .then((response) => {
        return response.ok
          ? response.json().then((json) => {
              this.ajaxReady(json, response.url, callback)
              this.stateReady()
            })
          : Promise.reject(response)
      })
      .catch(() => {
        this.printMessage('error', 'Error loading ...')
      })
  }

  submitForm(url, callback) {
    // TODO
    /* for (const i in CKEDITOR.instances) {
      if (CKEDITOR.instances.hasOwnProperty(i)) { CKEDITOR.instances[i].updateElement() }
    } */

    const formData = new FormData(this.modal.querySelector('form')) // before disabling form
    this.stateLoading()

    fetch(url, { method: 'POST', body: formData })
      .then((response) => {
        return response.ok
          ? response.json().then((json) => {
              this.ajaxReady(json, response.url, callback)
              this.stateReady()
            })
          : Promise.reject(response)
      })
      .catch(() => {
        this.printMessage('error', 'Error loading ...')
      })
  }

  ajaxReady(json, url, callback) {
    if (Object.hasOwn(json, 'modalClose') && json.modalClose === true) {
      if (typeof callback === 'function') {
        callback(json, this.modal)
      }
      this.bsModal.hide()
      return
    }

    if (Object.hasOwn(json, 'modalTitle')) {
      this.modal.querySelector('.modal-title').innerHTML = json.modalTitle
    }

    if (Object.hasOwn(json, 'modalBody')) {
      this.modal.querySelector('.ajax-modal-body').innerHTML = json.modalBody

      this.modal.querySelectorAll('input').forEach((input) => {
        input.classList.add('ignore-ems-update')
      })
      const event = new AddedDomEvent(this.modal)
      event.dispatch()
    }
    if (Object.hasOwn(json, 'modalFooter')) {
      this.modal.querySelector('.ajax-modal-footer').innerHTML = json.modalFooter
    } else {
      this.modal.querySelector('.ajax-modal-footer').innerHTML =
        '<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>'
    }

    const messages = Object.hasOwn(json, 'modalMessages') ? json.modalMessages : []
    messages.forEach((m) => {
      const messageType = Object.keys(m)[0]
      const message = m[messageType]
      this.printMessage(messageType, message)
    })

    const modelForm = this.modal.querySelector('form')
    if (modelForm) {
      modelForm.addEventListener('submit', (event) => {
        ajaxModal.submitForm(url, callback)
        event.preventDefault()
      })
    }

    const btnAjaxSubmit = this.modal.querySelector('#ajax-modal-submit')
    if (btnAjaxSubmit) {
      btnAjaxSubmit.addEventListener('click', () => {
        ajaxModal.submitForm(url, callback)
      })
      document.addEventListener('keydown', this.onKeyDown)
    }

    tooltipDataLinks(this.modal)

    if (typeof callback === 'function') {
      callback(json, this.modal)
    }
  }

  printMessage(messageType, message) {
    let messageClass
    switch (messageType) {
      case 'warning':
        messageClass = 'alert-warning'
        break
      case 'error':
        messageClass = 'alert-danger'
        break
      case 'info':
        messageClass = 'alert-info'
        break
      default:
        messageClass = 'alert-success'
    }

    this.modal
      .querySelector('.ajax-modal-body')
      .insertAdjacentHTML(
        'afterbegin',
        '<div class="alert ' +
          messageClass +
          '" role="alert">' +
          message.replace(/\n/g, '<br>') +
          '</div>'
      )
  }
}

const ajaxModal = new AjaxModal('#ajax-modal')
const pickFileModal = new AjaxModal('#pick-file-server-modal')

export default ajaxModal
export { pickFileModal }
