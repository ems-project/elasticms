'use strict'
import ChoicePicker from './core/helpers/choicePicker.js'
import ajaxRequest from './core/components/ajaxRequest.js'
import notifications from './core/components/notifications.js'

const form = document.querySelector('#criteria-form')

window.onload = function () {
    const selects = Array.from(document.querySelectorAll('#CriteriaUpdateCustomViewTable select'))
    selects.forEach((element) => {
        const params = new URLSearchParams()
        params.append('environment', form.dataset.environmentName ?? '')
        params.append('category', form.dataset.category ?? '')

        new ChoicePicker(element, {
            dynamicLoading: true,
            searchApiUrl: form.dataset.apiSearch,
            searchParams: params,
            type: form.dataset.typeName
        })
        element.addEventListener('addItem', (event) => {
            const filters = {
                ...JSON.parse(element.closest('td').dataset.filters),
                ...JSON.parse(element.closest('table').dataset.filters)
            }

            const data = {
                filters: filters,
                target: event.detail.value,
                category: form.dataset.category,
                criteriaField: form.dataset.criteriaField
            }
            ajaxRequest.post(form.dataset.addUrl, data).fail(function () {
                notifications.outOfSync()
            })
        })
        element.addEventListener('removeItem', (event) => {
            const filters = {
                ...JSON.parse(element.closest('td').dataset.filters),
                ...JSON.parse(element.closest('table').dataset.filters)
            }

            const data = {
                filters,
                target: event.detail.value,
                category: form.dataset.category,
                criteriaField: form.dataset.criteriaField
            }
            ajaxRequest.post(form.dataset.removeUrl, data).fail(function () {
                notifications.outOfSync()
            })
        })
    })
}
