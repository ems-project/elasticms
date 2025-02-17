import Choices from "choices.js";
import Job from "../plugins/job.js";

class ChoicePicker {
    constructor(element) {
        const querySearchLabel = element.dataset.querySearchLabel
        new Choices(element, {
            placeholderValue: querySearchLabel ?? 'Search',
            removeItemButton: true,
            allowHTML: true,
            callbackOnCreateTemplates: function (template, escapeForTemplate, getClassNames) {
                return {
                    item: ({ classNames }, data) => {
                        let icon = ''
                        if (data.element && data.element.dataset.icon) {
                            icon = `<i class="${data.element.dataset.icon}"></i> `
                        }
                        return template(`
                          <div class="${getClassNames(classNames.item).join(' ')} ${getClassNames(
                            data.highlighted
                                ? classNames.highlightedState
                                : classNames.itemSelectable
                        ).join(' ')} ${
                            data.placeholder ? classNames.placeholder : ''
                        }" data-item data-id="${data.id}" data-value="${data.value}" ${
                            data.active ? 'aria-selected="true"' : ''
                        } ${data.disabled ? 'aria-disabled="true"' : ''}>
                            ${icon}${data.label}
                          </div>`)
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
    }
}

export default ChoicePicker
