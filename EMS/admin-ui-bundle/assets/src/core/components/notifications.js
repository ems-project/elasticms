import { Toast, Modal } from 'bootstrap'
class Notifications {
  constructor() {
    this.counter = 0
    const toasts = document.querySelectorAll('.toast')
    for (let i = 0; i < toasts.length; ++i) {
      const toast = new Toast(toasts[i])
      toast.show()
    }
  }

  startActivity() {
    if (++this.counter > 0) {
      document.getElementById('ajax-activity').classList.add('fa-spin')
    }
  }

  stopActivity() {
    if (--this.counter === 0) {
      document.getElementById('ajax-activity').classList.remove('fa-spin')
    }
  }

  addActivityMessages(messages) {
    if (!Array.isArray(messages) || messages.length === 0) {
      return
    }
    const activityList = document.getElementById('activity-log')
    for (let index = 0; index < messages.length; ++index) {
      const listItem = document.createElement('li')
      listItem.innerHTML = messages[index]
      listItem.setAttribute('title', listItem.textContent)
      activityList.insertAdjacentElement('beforeend', listItem)
    }
    this.updateCounter()
  }

  updateCounter() {
    const numberOfElem = document.querySelectorAll('ul#activity-log>li').length
    document.getElementById('activity-counter').innerHTML = numberOfElem === 0 ? '' : numberOfElem
  }

  addNoticeMessages(notices) {
    this.addToastMessages(notices, 'Notice', 'info', 'fa-info', true)
  }

  addWarningMessages(warnings) {
    this.addToastMessages(warnings, 'Warning', 'warning', 'fa-warning', true)
  }

  addErrorMessages(warnings) {
    this.addToastMessages(warnings, 'Error', 'danger', 'fa-ban', false)
  }

  addToastMessages(messages, title, level, iconClass, autoHide) {
    if (!Array.isArray(messages) || messages.length === 0) {
      return
    }
    for (let index = 0; index < messages.length; ++index) {
      this.addToastMessage(messages[index], title, level, iconClass, autoHide)
    }
  }

  addToastMessage(message, title, level, iconClass, autoHide) {
    const div = document.createElement('div')
    div.setAttribute('class', `toast bg-${level}`)
    div.setAttribute('role', 'alert')
    div.setAttribute('aria-live', 'assertive')
    div.setAttribute('aria-atomic', 'true')
    div.innerHTML = `
            <div class="toast-header">
                <span class="me-auto"><i class="icon fa ${iconClass}"></i>&nbsp;${title}</span>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                ${message}
            </div>
        `
    const toaster = document.getElementById('toaster')
    toaster.insertAdjacentElement('beforeend', div)

    const toast = new Toast(div, {
      animation: true,
      autohide: autoHide,
      delay: 5000
    })
    toast.show()
  }

  outOfSync() {
    const outOfSync = document.getElementById('data-out-of-sync')
    const modal = new Modal(outOfSync, {
      keyboard: false,
      backdrop: 'static'
    })
    modal.show()
  }
}

const notifications = new Notifications()

export default notifications
