import $ from 'jquery'
import * as select2 from 'select2'

class Select {
  load(target) {
    this.select2(target)
    this.checkboxAll(target)
  }

  checkboxAll(target) {
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

  async select2(target) {
    const targetQuery = $(target)
    if (undefined === targetQuery.select2) {
      console.warn('Select 2 is not yet available, probably because you are in vite dev mode')
      select2.default()
    }
    targetQuery.find('select.select2').select2({
      theme: 'bootstrap-5',
      allowClear: true,
      placeholder: '',
      escapeMarkup: function (markup) {
        return markup
      },
      dropdownParent: target === document ? $(target.body) : targetQuery,
      templateResult: (state) => {
        const text = state.text
        const element = state.element
        const dataset = element ? element.dataset : false

        if (dataset && Object.hasOwn(dataset, 'icon')) {
          return `<i class="${dataset.icon}"></i> ${text}`
        }

        return text
      }
    })
  }
}

export default Select
