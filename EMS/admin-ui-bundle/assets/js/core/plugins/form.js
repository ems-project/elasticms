import $ from 'jquery'
import ajaxRequest from '../components/ajaxRequest'
import ChangeEvent from '../events/changeEvent'
import { EMS_CTRL_SAVE_EVENT } from '../events/ctrlSaveEvent'
import '../../../css/core/components/form.scss'

class Form {
  load (target) {
    this.initAjaxSave(target)
    this.initFormChangeEvent(target)
  }

  initAjaxSave (target) {
    $(target).find('button[data-ajax-save-url]').each(function () {
      const button = $(this)
      const form = button.closest('form')

      const ajaxSave = function (event) {
        event.preventDefault()

        const formContent = form.serialize()
        ajaxRequest.post(button.data('ajax-save-url'), formContent)
          .success(function (message) {
            let response = message
            if (!(response instanceof Object)) {
              response = $.parseJSON(message)
            }

            $(form).find('.has-error').removeClass('has-error')

            $(response.errors).each(function (index, item) {
              $('#' + item.propertyPath).parent().addClass('has-error')
            })
          })
      }

      button.on('click', ajaxSave)
      document.addEventListener(EMS_CTRL_SAVE_EVENT, (event) => ajaxSave(event.detail.parentEvent))
    })
  }

  initFormChangeEvent (target) {
    const inputs = target.querySelectorAll('input,textarea,select')
    for (let i = 0; i < inputs.length; ++i) {
      if (inputs[i].classList.contains('ignore-ems-update') || inputs[i].classList.contains('datetime-picker')) {
        continue
      }
      inputs[i].addEventListener('keyup', function () {
        const event = new ChangeEvent(inputs[i])
        event.dispatch()
      })
    }
  }
}

export default Form
