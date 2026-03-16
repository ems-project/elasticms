import $ from 'jquery'
import Choices from 'choices.js'
import { ChangeEvent } from '../events/changeEvent'
import { luma } from './color'
import { stripHtmlTags } from './text'

class ChoicePicker {
    constructor(element, config) {
        const querySearchLabel = element.dataset.querySearchLabel
        const type = config?.type ?? element.dataset.type
        const searchId = element.dataset.searchId
        const querySearch = element.dataset.querySearch
        const circleOnly = element.dataset.circleOnly
        const dynamicLoading = config?.dynamicLoading ?? element.dataset.dynamicLoading
        const sortable = element.dataset.sortable
        const locale = element.dataset.locale
        const referrerEmsId = element.dataset.referrerEmsId
        const choices = new Choices(element, {
            placeholderValue: querySearchLabel ?? 'Search',
            removeItemButton: true,
            allowHTML: true,
            removeItems: true,
            callbackOnCreateTemplates: function (template, escapeForTemplate, getClassNames) {
                return {
                    item: ({ classNames }, data) => {
                        let icon = ''
                        if (data.element?.dataset.icon ?? data.customProperties?.icon) {
                            icon = `<i class="${data.element?.dataset.icon ?? data.customProperties.icon}"></i> `
                        }
                        let style = ''
                        if (data.element?.dataset.color ?? data.customProperties?.color) {
                            const blackOrWhite =
                                luma(
                                    (
                                        data.element?.dataset.color ?? data.customProperties.color
                                    ).replace('#', '')
                                ) >= 165
                                    ? 'black'
                                    : 'white'
                            style = ` style="color: ${blackOrWhite};background-color: ${data.element?.dataset.color ?? data.customProperties.color};"`
                        }
                        const itemTemplate = template(`
                          <div class="${getClassNames(classNames.item).join(' ')} ${getClassNames(
                              data.highlighted
                                  ? classNames.highlightedState
                                  : classNames.itemSelectable
                          ).join(' ')} ${
                              data.placeholder ? classNames.placeholder : ''
                          }" data-item data-id="${data.id}" data-value="${data.value}" ${
                              data.active ? 'aria-selected="true"' : ''
                          } ${data.disabled ? 'aria-disabled="true"' : ''}${style}>
                            ${icon}${data.label}
                            <button type="button" class="choices__button" aria-label="Remove item: ${stripHtmlTags(data.label)}" data-button="">Remove item</button>
                          </div>`)

                        const button = itemTemplate.querySelector('button')
                        if (button) {
                            button.addEventListener('click', () => {
                                choices.removeChoice(data.value)
                            })
                        }

                        return itemTemplate
                    },
                    choice: ({ classNames }, data) => {
                        let icon = ''
                        if (data.element && data.element.dataset.icon) {
                            icon = `<i class="${data.element.dataset.icon}"></i> `
                        }
                        return template(`
                          <div class="${getClassNames(classNames.item).join(' ')} ${getClassNames(classNames.itemChoice).join(' ')} ${getClassNames(
                              data.disabled ? classNames.itemDisabled : classNames.itemSelectable
                          ).join(
                              ' '
                          )}" data-select-text="${this.config.itemSelectText}" data-choice ${
                              data.disabled
                                  ? 'data-choice-disabled aria-disabled="true"'
                                  : 'data-choice-selectable'
                          } data-id="${data.id}" data-value="${data.value}" ${
                              data.groupId > 0 ? 'role="treeitem"' : 'role="option"'
                          }>
                            ${icon}${data.label}
                          </div>`)
                    }
                }
            }
        })

        element.addEventListener('change', (event) => {
            const changeEvent = new ChangeEvent(event.target)
            changeEvent.dispatch()
        })

        if (dynamicLoading) {
            const searchApiUrl = config?.searchApiUrl ?? document.body.dataset.searchApi
            element.addEventListener('search', async function (event) {
                const searchValue = event.detail.value.trim()
                // if (searchValue.length < 2) return;

                try {
                    const params = config?.searchParams ?? new URLSearchParams()
                    params.append('q', searchValue ?? '')
                    params.append('page', 1)
                    params.append('type', type ?? '')
                    params.append('searchId', searchId ?? '')
                    params.append('querySearch', querySearch ?? '')
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
                            label: item.text,
                            customProperties: {
                                icon: item.icon ?? null,
                                color: item.color ?? null
                            }
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
                    const listItems = Array.from(
                        choicesList.querySelectorAll('div.choices__item')
                    ).map((p) => p.dataset.value)
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

export default ChoicePicker
