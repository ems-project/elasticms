import $ from 'jquery'
import ajaxRequest from '../components/ajaxRequest'

export default class Button {
  load(target) {
    this.addRemoveButtonListeners(target)
    this.addDisabledButtonTreatListeners(target)
    this.addRequestNotificationListeners(target)
    this.addPostButtonListeners(target)
    this.addToggleButtonListeners(target)
  }

  addRemoveButtonListeners(target) {
    $(target)
      .find('.remove-item')
      .on('click', function (event) {
        event.preventDefault()
        $(this).closest('li').remove()
      })
  }

  addDisabledButtonTreatListeners(target) {
    const treat = target.querySelector(
      'form[name="treat_notifications"] #treat_notifications_publishTo'
    )
    if (treat) {
      treat.addEventListener('change', function () {
        const form = treat.closest('form')
        const isDisabledAccept = this.value.length === 0
        form.elements.treat_notifications_accept.disabled = isDisabledAccept
        form.elements.treat_notifications_reject.disabled = !isDisabledAccept
      })
    }
  }

  addRequestNotificationListeners(target) {
    const links = target.querySelectorAll('a.request-notification')
    for (let i = 0; i < links.length; ++i) {
      links[i].addEventListener('click', function (event) {
        event.preventDefault()
        const url = this.dataset.dataUrl
        const data = {
          templateId: this.dataset.templateId,
          environmentName: this.dataset.environmentName,
          contentTypeId: this.dataset.contentTypeId,
          ouuid: this.dataset.ouuid
        }
        ajaxRequest.post(url, data, 'modal-notifications')
      })
    }
  }

  addPostButtonListeners(target) {
    const buttons = target.querySelectorAll('.core-post-button')
    for (let i = 0; i < buttons.length; ++i) {
      buttons[i].addEventListener('click', function (e) {
        e.preventDefault()

        const button = e.target
        const postSettings = JSON.parse(button.dataset.postSettings)
        const url = button.href
        const f = Object.hasOwn(postSettings, 'form')
          ? document.getElementById(postSettings.form)
          : document.createElement('form')

        if (Object.hasOwn(postSettings, 'form')) {
          const inputField = document.createElement('INPUT')
          inputField.style.display = 'none'
          inputField.type = 'TEXT'
          inputField.name = 'source_url'
          inputField.value = url
          f.appendChild(inputField)

          if (postSettings.action) {
            f.action = JSON.parse(postSettings.action)
          }
        } else {
          f.style.display = 'none'
          f.method = 'post'
          f.action = url
          button.parentNode.appendChild(f)
        }

        if (Object.hasOwn(postSettings, 'value') && Object.hasOwn(postSettings, 'name')) {
          const inputField = document.createElement('INPUT')
          inputField.style.display = 'none'
          inputField.type = 'TEXT'
          inputField.name = JSON.parse(postSettings.name)
          inputField.value = JSON.parse(postSettings.value)
          f.appendChild(inputField)
        }

        f.submit()
      })
    }
  }

  addToggleButtonListeners(target) {
    const buttons = target.querySelectorAll('[data-bs-toggle-contain].toggle-button')
    for (let i = 0; i < buttons.length; ++i) {
      const button = buttons[i]
      button.addEventListener('click', function () {
        const toggleText = button.dataset.bsToggleContain
        const previousText = button.innerHTML
        button.innerHTML = toggleText
        button.dataset.bsToggleContain = previousText
      })
    }
  }
}
