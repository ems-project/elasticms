import $ from 'jquery'
import 'bootstrap-select'
import 'select2/dist/js/select2.full'
import 'bootstrap-select/sass/bootstrap-select.scss'
import 'select2/src/scss/core.scss'
import '../../../css/core/plugins/select.scss'

class Select {
  load (target) {
    const query = $(target)
    query.find('.select2').select2({
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
