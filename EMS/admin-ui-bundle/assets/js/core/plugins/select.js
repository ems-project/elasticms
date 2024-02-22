import $ from 'jquery'
import 'select2/dist/js/select2'

class Select {
  load (target) {
    const query = $(target)
    query.find('.select2').select2({
      theme: 'bootstrap-5',
      allowClear: true,
      placeholder: '',
      escapeMarkup: function (markup) { return markup }
    })
    this.checkboxAll(target)
  }

  checkboxAll (target) {
    const checkboxesAll = target.querySelectorAll('input[data-grouped-checkbox-target]')
    for (let i = 0; i < checkboxesAll.length; ++i) {
      const selector = checkboxesAll[i].dataset.groupedCheckboxTarget
      checkboxesAll[i].addEventListener('change', function () {
        const targets = document.querySelectorAll(selector)
        for (let j = 0; j < targets.length; ++j) {
          targets[j].checked = this.checked
        }
      })
    }
  }
}

export default Select
