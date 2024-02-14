import $ from 'jquery'
import ajaxRequest from '../components/ajaxRequest'
import ChangeEvent from '../events/changeEvent'

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

      $(document).keydown(function (e) {
        let key
        const possible = [e.key, e.keyIdentifier, e.keyCode, e.which]

        while (key === undefined && possible.length > 0) {
          key = possible.pop()
        }

        if (typeof key === 'number' && (key === 115 || key === 83) && (e.ctrlKey || e.metaKey) && !(e.altKey)) {
          ajaxSave(e)
          return false
        }
        return true
      })
    })
  }

  initFormChangeEvent (target) {
    const inputs = document.querySelectorAll('input,textarea,select')
    for (let i = 0; i < inputs.length; ++i) {
      if (inputs[i].classList.contains('ignore-ems-update') || inputs[i].classList.contains('datetime-picker')) {
        continue
      }
      inputs[i].addEventListener('change', function () {
        const event = new ChangeEvent(inputs[i])
        event.dispatch()
      })
    }
  }
}

export default Form
