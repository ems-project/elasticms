import $ from 'jquery'

export default class Button {
  load (target) {
    this.addRemoveButtonListeners(target)
    this.addDisabledButtonTreatListeners(target)
  }

  addRemoveButtonListeners (target) {
    $(target).find('.remove-item')
      .on('click', function (event) {
        event.preventDefault()
        $(this).closest('li').remove()
      })

    $(target).find('.remove-filter')
      .on('click', function (event) {
        event.preventDefault()
        $(this).closest('.filter-container').remove()
      })
  }

  addDisabledButtonTreatListeners (target) {
    const treat = target.querySelector('form[name="treat_notifications"] #treat_notifications_publishTo')
    if (treat) {
      treat.addEventListener('change', function () {
        const form = treat.closest('form')
        const isDisabledAccept = this.value.length === 0
        form.elements.treat_notifications_accept.disabled = isDisabledAccept
        form.elements.treat_notifications_reject.disabled = !isDisabledAccept
      })
    }
  }
}
