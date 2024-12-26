import { NestedSortable, NestedSortableOptions } from '../components/nestedSortable.ts'

export default class NestedSortables {
  sortables: Array<NestedSortable> = []

  load(target: HTMLElement) {
    this._addSortables(target)
    this._addReorderButtons(target)
  }

  private _addReorderButtons(target: HTMLElement) {
    const reorderButtons = target.querySelectorAll('.reorder-button')
    for (let i = 0; i < reorderButtons.length; ++i) {
      const reorderButton = reorderButtons[i]
      reorderButton.addEventListener('click', this.handleReorder.bind(this))
    }
  }

  public handleReorder(reorderEvent: Event) {
    const target = reorderEvent.target
    if (!(target instanceof HTMLElement)) {
      return
    }
    if (this.sortables.length !== 1) {
      throw new Error('Too many sortable list on the same page for a global reorder button')
    }
    const hierarchy = this.sortables[0].getStructure()
    const form = target.closest('form')
    if (null === form) {
      return
    }
    const reorderedItemsField = form.querySelector('input.reorder-items')
    if (!(reorderedItemsField instanceof HTMLInputElement)) {
      return
    }
    reorderedItemsField.value = JSON.stringify(hierarchy)
    const changeEvent = new Event('change', {
      bubbles: true,
      cancelable: true
    })
    reorderedItemsField.dispatchEvent(changeEvent)
  }

  private _addSortables(target: HTMLElement) {
    const sortableLists = target.querySelectorAll('.nested-sortable')
    for (let i = 0; i < sortableLists.length; ++i) {
      const nestedList = sortableLists[i]
      if (!(nestedList instanceof HTMLElement)) {
        continue
      }
      const maxLevels = Number.parseInt(nestedList.getAttribute('data-nested-max-level') ?? '1')
      const isTree = 'true' === (nestedList.getAttribute('data-nested-is-tree') ?? 'false')
      const handle = nestedList.getAttribute('data-nested-handle') ?? 'div'

      this.sortables.push(
        new NestedSortable(new NestedSortableOptions(nestedList, handle, maxLevels, isTree))
      )
    }
  }
}
