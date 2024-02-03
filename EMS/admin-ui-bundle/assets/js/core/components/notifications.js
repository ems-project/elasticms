import $ from 'jquery'
import { Toast } from 'bootstrap'

class Notifications {
  constructor () {
    this.counter = 0
  }

  startActivity () {
    if (++this.counter > 0) {
      $('#ajax-activity').addClass('fa-spin')
    }
  }

  stopActivity () {
    if (--this.counter === 0) {
      $('#ajax-activity').removeClass('fa-spin')
    }
  }

  addActivityMessages (messages) {
    if (!Array.isArray(messages) || messages.length === 0) {
      return
    }
    const activityList = $('ul#activity-log')
    for (let index = 0; index < messages.length; ++index) {
      const message = $($.parseHTML(messages[index]))
      activityList.append(`<li title="${message.text()}">${messages[index]}</li>`)
    }
    this.updateCounter()
  }

  updateCounter () {
    const numberOfElem = $('ul#activity-log>li').length
    if (numberOfElem) {
      $('#activity-counter').text(numberOfElem)
    } else {
      $('#activity-counter').empty()
    }
  }

  addWarningMessages (warnings) {
    this.addToastMessages(warnings, 'Warning', 'warning', 'fa-warning', true)
  }

  addToastMessages (messages, title, level, iconClass, autoHide) {
    if (!Array.isArray(messages) || messages.length === 0) {
      return
    }
    for (let index = 0; index < messages.length; ++index) {
      this.addToastMessage(messages[index], title, level, iconClass, autoHide)
    }
  }

  addToastMessage (message, title, level, iconClass, autoHide) {
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
}

const notifications = new Notifications()

export default notifications
