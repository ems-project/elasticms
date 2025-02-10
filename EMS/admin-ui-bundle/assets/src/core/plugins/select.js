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
    const formatFn = (state) => {
      const text = state.text
      const element = state.element
      const dataset = element ? element.dataset : false

      if (dataset && Object.hasOwn(dataset, 'icon')) {
        return `<i class="${dataset.icon}"></i> ${text}`
      }

      return text
    }
    targetQuery.find('select.select2').each(function() {
        const select = $(this)
        const querySearchLabel = select.data('query-search-label');
        const modal = select.parents('.modal')
        select.select2({
            theme: 'bootstrap-5',
            allowClear: true,
            //https://github.com/select2/select2/issues/3781
            placeholder: querySearchLabel && '' !== querySearchLabel ? querySearchLabel : 'Search',
            escapeMarkup: function (markup) {
                return markup
            },
            width: '100%',
            //https://select2.org/troubleshooting/common-problems#select2-does-not-function-properly-when-i-use-it-inside-a-bootst
            dropdownParent: 0 === modal.length ? $(target.body) : select.parent(),
            templateSelection: formatFn,
            templateResult: formatFn
        })
    })
  }
}

export default Select
