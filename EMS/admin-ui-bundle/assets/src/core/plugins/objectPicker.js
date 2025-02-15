import 'choices.js/public/assets/styles/choices.css'
import Choices from 'choices.js'
import { ChangeEvent } from '../events/changeEvent'

export default class ObjectPicker {
  load(target) {
    const searchApiUrl = document.body.dataset.searchApi
    const elements = target.querySelectorAll('.objectpicker')
    for (let i = 0; i < elements.length; ++i) {
      const element = elements[i]
      const type = element.dataset.type
      const searchId = element.dataset.searchId
      const querySearch = element.dataset.querySearch
      const querySearchLabel = element.dataset.querySearchLabel
      const circleOnly = element.dataset.circleOnly
      const dynamicLoading = element.dataset.dynamicLoading
      const sortable = element.dataset.sortable
      const locale = element.dataset.locale
      const referrerEmsId = element.dataset.referrerEmsId
      const choices = new Choices(element, {
        placeholderValue: querySearchLabel ?? 'Search',
        removeItems: true,
        removeItemButton: true,
        allowHTML: true
      })

      element.addEventListener('change', (event) => {
        const changeEvent = new ChangeEvent(event.target)
        changeEvent.dispatch()
      })

      if (dynamicLoading) {
        element.addEventListener('search', async function (event) {
          const searchValue = event.detail.value.trim()
          // if (searchValue.length < 2) return;

          try {
            const params = new URLSearchParams()
            params.append('q', searchValue)
            params.append('page', 1)
            params.append('type', type)
            params.append('searchId', searchId)
            params.append('querySearch', querySearch)
            if (locale !== undefined) {
              params.append('locale', locale)
            }
            if (referrerEmsId !== undefined) {
              params.append('referrerEmsId', referrerEmsId)
            }
            if (circleOnly !== undefined) {
              params.append('circle', circleOnly)
            }
            const response = await fetch(`${searchApiUrl}?${params}`)
            const results = await response.json()
            choices.clearChoices()
            if (results.items.length) {
              const formattedResults = results.items.map((item) => ({
                value: item.id,
                label: item.text
              }))
              choices.setChoices(formattedResults, 'value', 'label', true)
            }
          } catch (error) {
            console.error('Error while retrieving data :', error)
          }
        })
      }
      if (sortable) {
        const choicesList = element.parentElement.querySelector('div.choices__list')
        const spaceship = (a, b) => (a > b) - (a < b)
        $(choicesList).sortable({
          stop: function () {
            const listItems = Array.from(choicesList.querySelectorAll('div.choices__item')).map(
              (p) => p.dataset.value
            )
            const options = Array.from(element.options).sort((a, b) =>
              spaceship(listItems.indexOf(a.value), listItems.indexOf(b.value))
            )
            element.innerHTML = ''
            options.forEach((option) => element.add(option))
            const changeEvent = new ChangeEvent(element)
            changeEvent.dispatch()
          }
        })
      }
    }
  }
}
