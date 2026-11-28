import ChoicePicker from '../helpers/choicePicker.js'

class Select {
    load(target) {
        this.choices(target)
        this.checkboxAll(target)
    }

    checkboxAll(target) {
        const checkboxesAll = target.querySelectorAll('input[data-grouped-checkbox-target]')
        for (let i = 0; i < checkboxesAll.length; ++i) {
            const selector = checkboxesAll[i].dataset.groupedCheckboxTarget
            checkboxesAll[i].addEventListener('change', function () {
                const targets = document.querySelectorAll(selector)
                for (let j = 0; j < targets.length; ++j) {
                    const input =
                        'INPUT' === targets[j].tagName
                            ? targets[j]
                            : targets[j].querySelector('input')
                    input.checked = checkboxesAll[i].checked
                }
            })
        }
    }

    choices(target) {
        const elements = target.querySelectorAll('select.select2')
        for (let i = 0; i < elements.length; ++i) {
            new ChoicePicker(elements[i])
        }
    }
}

export default Select
