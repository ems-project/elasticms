'use strict'

window.onload = function () {
    const columnCriteria = document.querySelector('#criteria_filter_columnCriteria')
    const rowCriteria = document.querySelector('#criteria_filter_rowCriteria')

    function handleChange() {
        if (columnCriteria.value === rowCriteria.value) {
            if (this.id === 'criteria_filter_columnCriteria') {
                const firstNonSelectedOption = document.querySelector(
                    '#criteria_filter_rowCriteria option:not(:checked)'
                )
                rowCriteria.value = firstNonSelectedOption ? firstNonSelectedOption.value : ''
            } else {
                const firstNonSelectedOption = document.querySelector(
                    '#criteria_filter_columnCriteria option:not(:checked)'
                )
                columnCriteria.value = firstNonSelectedOption ? firstNonSelectedOption.value : ''
            }
        }
    }

    columnCriteria.addEventListener('change', handleChange)
    rowCriteria.addEventListener('change', handleChange)
    handleChange()
}
