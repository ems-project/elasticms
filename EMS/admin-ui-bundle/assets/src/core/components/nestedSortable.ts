import '../../../css/core/plugins/sortable.scss'
import Sortable from 'sortablejs'

export class NestedSortableOptions {
  handle: string
  target: HTMLElement
  maxLevels: number
  isTree: boolean

  constructor(target: HTMLElement, handle: string, maxLevels: number, isTree: boolean) {
    this.handle = handle
    this.target = target
    this.maxLevels = maxLevels
    this.isTree = isTree
  }
}

export class NestedSortable {
  private options: NestedSortableOptions
  private sortable: Sortable
  private childrenList: Sortable[] = []
  constructor(options: NestedSortableOptions) {
    this.options = options
    const config = {
      handle: this.options.handle,
      group: `ems-nested-sortable-${this.options.target.id}`
    }
    this.sortable = Sortable.create(this.options.target, config)
    const children = this.options.target.querySelectorAll('ol')
    for (let i = 0; i < children.length; ++i) {
      const parentId = children[i].getAttribute('data-parent-id')
      if (null === parentId) {
        continue
      }
      this.childrenList[Number.parseInt(parentId)] = Sortable.create(children[i], config)
    }
    this.handleCollapses()
  }

  getStructure() {
    return this.sortable.toArray().map(this.buildStructure.bind(this))
  }

  buildStructure(itemId: any): any {
    if (undefined === this.childrenList[itemId]) {
      return {
        id: itemId
      }
    }
    return {
      id: itemId,
      children: this.childrenList[itemId].toArray().map(this.buildStructure.bind(this))
    }
  }

  handleCollapses() {
    let findCollapseButtonPrefix = '.json_menu_editor_fieldtype_widget '

    if (this.options.target.querySelectorAll(findCollapseButtonPrefix).length === 0) {
      findCollapseButtonPrefix = '.mjs-nestedSortable '
    }

    if (this.options.target.classList.contains('mjs-nestedSortable')) {
      findCollapseButtonPrefix = ''
    }

    const collapseButtons = this.options.target.querySelectorAll(
      findCollapseButtonPrefix + '.button-collapse'
    )
    for (let i = 0; i < collapseButtons.length; ++i) {
      const button = collapseButtons[i]
      button.addEventListener('click', this.handleCollapse.bind(this))
    }

    const collapseAllButtons = this.options.target.querySelectorAll(
      findCollapseButtonPrefix + '.button-collapse-all'
    )
    for (let i = 0; i < collapseAllButtons.length; ++i) {
      const button = collapseAllButtons[i]
      button.addEventListener('click', this.handleCollapseAll.bind(this))
    }
  }

  handleCollapse(collapseEvent: Event) {
    collapseEvent.preventDefault()
    if (!(collapseEvent.target instanceof HTMLElement)) {
      return
    }
    const isExpanded = collapseEvent.target.getAttribute('aria-expanded') === 'true'
    if (null === collapseEvent.target.parentElement) {
      return
    }
    const nestedButtons = collapseEvent.target.parentElement.querySelectorAll('> button')
    for (let i = 0; i < nestedButtons.length; ++i) {
      nestedButtons[i].setAttribute('aria-expanded', isExpanded ? 'false' : 'true')
    }
    const panel = collapseEvent.target.closest('.collapsible-container')
    if (null === panel) {
      return
    }
    const childrenList = panel.querySelector('ol')
    if (null === childrenList) {
      return
    }
    childrenList.style.display = isExpanded ? 'block' : 'none'
  }

  handleCollapseAll(collapseAllEvent: Event) {
    collapseAllEvent.preventDefault()
    if (!(collapseAllEvent.target instanceof HTMLElement)) {
      return
    }
    const isExpanded = collapseAllEvent.target.getAttribute('aria-expanded') === 'true'
    const panel = collapseAllEvent.target.closest('.collapsible-container')
    if (null === panel) {
      return
    }
    if (null === collapseAllEvent.target.parentElement) {
      return
    }
    const nestedButtons = collapseAllEvent.target.parentElement.querySelectorAll(
      '.button-collapse, .button-collapse-all'
    )
    for (let i = 0; i < nestedButtons.length; ++i) {
      nestedButtons[i].setAttribute('aria-expanded', isExpanded ? 'false' : 'true')
    }

    const childrenLists = panel.querySelectorAll('ol')
    for (let i = 0; i < childrenLists.length; ++i) {
      if (childrenLists[i].classList.contains('not-collapsible')) {
        continue
      }
      childrenLists[i].style.display = isExpanded ? 'block' : 'none'
    }
  }
}
