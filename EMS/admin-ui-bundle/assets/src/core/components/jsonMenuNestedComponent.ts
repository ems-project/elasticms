import Sortable from 'sortablejs'
import ajaxModal from '../helpers/ajaxModal'

export default class JsonMenuNestedComponent {
  id
  #tree: HTMLElement | null
  #top: HTMLElement | null
  #header: HTMLElement | null
  #footer: HTMLElement | null
  element
  #pathPrefix
  #loadParentIds: string[] = []
  #sortableLists: { [key: string]: Sortable } = {}
  modalSize = 'md'
  #dragBlocked = false

  constructor(element: HTMLElement) {
    this.id = element.id
    this.element = element
    this.#tree = element.querySelector('.jmn-tree')
    this.#top = element.querySelector('.jmn-top')
    this.#header = element.querySelector('.jmn-header')
    this.#footer = element.querySelector('.jmn-footer')
    this.#pathPrefix = `/component/json-menu-nested/${element.dataset.hash}`
    this._addClickListeners()
    this._addClickLongPressListeners()
    this.load({
      activeItemId: 'activeItemId' in element.dataset ? element.dataset.activeItemId : null
    })
  }

  load({
    activeItemId = null,
    copyItemId = null,
    loadChildrenId = null
  }: {
    activeItemId?: string | null
    copyItemId?: string | null
    loadChildrenId?: string | null
  } = {}) {
    this._post('/render', {
      active_item_id: activeItemId,
      copy_item_id: copyItemId,
      load_parent_ids: this.#loadParentIds,
      load_children_id: loadChildrenId
    }).then((json) => {
      const { tree, loadParentIds, top, header, footer } = json

      if (top) this.#top!.innerHTML = top
      if (header) this.#header!.innerHTML = header
      if (footer) this.#footer!.innerHTML = footer

      if (!tree || !loadParentIds) return
      this.#loadParentIds = loadParentIds
      this.#tree!.innerHTML = tree

      let eventCanceled = this._dispatchEvent('jmn-load', {
        data: json,
        elements: this._sortables()
      })
      if (!eventCanceled) this.loading(false)
    })
  }
  itemGet(itemId: string) {
    return this._get(`/item/${itemId}`)
  }
  itemAdd(itemId: string, add: string, position: number | null = null) {
    return this._post(`/item/${itemId}/add`, { position: position, add: add })
  }
  itemDelete(itemId: string) {
    this._post(`/item/${itemId}/delete`).then((json) => {
      let eventCanceled = this._dispatchEvent('jmn-delete', { data: json, itemId: itemId })
      if (!eventCanceled) this.load()
    })
  }
  loading(flag: boolean) {
    const element = this.element.querySelector('.jmn-node-loading') as HTMLElement
    element.style.display = flag ? 'flex' : 'none'
  }

  _addClickListeners() {
    this.element.addEventListener(
      'click',
      (event) => {
        const element = event.target as HTMLElement
        const node = element.parentElement?.closest('.jmn-node') as HTMLElement
        const itemId = node ? (node.dataset.id as string) : '_root'

        if (element.classList.contains('jmn-btn-add')) this._onClickButtonAdd(element, itemId)
        if (element.classList.contains('jmn-btn-edit')) this._onClickButtonEdit(element, itemId)
        if (element.classList.contains('jmn-btn-view')) this._onClickButtonView(element, itemId)
        if (element.classList.contains('jmn-btn-delete')) this._onClickButtonDelete(itemId)
        if (element.classList.contains('jmn-btn-copy')) this._onClickButtonCopy(itemId)
        if (element.classList.contains('jmn-btn-paste')) this._onClickButtonPaste(itemId)

        if (Object.hasOwn(element.dataset, 'jmnModalCustom'))
          this._onClickModalCustom(element, itemId)
      },
      true
    )
  }
  _onClickButtonAdd(element: HTMLElement, itemId: string) {
    this._ajaxModal(element, `/item/${itemId}/modal-add/${element.dataset.add}`, 'jmn-add')
  }
  _onClickButtonEdit(element: HTMLElement, itemId: string) {
    this._ajaxModal(element, `/item/${itemId}/modal-edit`, 'jmn-edit')
  }
  _onClickButtonView(element: HTMLElement, itemId: string) {
    this._ajaxModal(element, `/item/${itemId}/modal-view`, 'jmn-view')
  }
  _onClickButtonDelete(itemId: string) {
    this.itemDelete(itemId)
  }
  _onClickButtonCopy(itemId: string) {
    this._post(`/item/${itemId}/copy`).then((json) => {
      const { success, copyId } = json
      if (!success) return
      document.dispatchEvent(
        new CustomEvent('jmn.copy', {
          cancelable: true,
          detail: { copyId: copyId, originalId: itemId }
        })
      )
    })
  }
  _onClickButtonPaste(itemId: string) {
    this._post(`/item/${itemId}/paste`).then((json) => {
      const { success, pasteId } = json
      if (!success) return

      this.load({ activeItemId: pasteId })
    })
  }

  onCopy(event: { originalId: string }) {
    const { originalId } = event
    this.load({ activeItemId: originalId })
  }
  _onClickModalCustom(element: HTMLElement, itemId: string) {
    const modalCustomName = element.dataset.jmnModalCustom
    this._ajaxModal(element, `/item/${itemId}/modal-custom/${modalCustomName}`, 'jmn-modal-custom')
  }
  _onClickButtonCollapse(button: HTMLElement, longPressed = false) {
    const expanded = button.getAttribute('aria-expanded')
    const node = button?.parentElement?.closest('.jmn-node') as HTMLElement
    const nodeId = node?.dataset.id as string

    if ('true' === expanded) {
      button.setAttribute('aria-expanded', 'false')

      const childNodes = node?.querySelectorAll(`.jmn-node`) ?? []
      const childIds = Array.from(childNodes).map((child) => (child as HTMLElement).dataset.id)
      childNodes.forEach((child) => child.remove())

      this.#loadParentIds = this.#loadParentIds.filter(
        (id) => id !== nodeId && !childIds.includes(id)
      )
      this.load()
    } else {
      button.setAttribute('aria-expanded', 'true')
      this.#loadParentIds.push(nodeId)
      this.load({ loadChildrenId: longPressed ? nodeId : null })
    }
  }
  _addClickLongPressListeners(): void {
    let delay: ReturnType<typeof setTimeout>
    let longPressed = false
    let longPressTime = 300

    this.element.addEventListener(
      'mousedown',
      (event) => {
        const target = event.target as HTMLElement

        if (target.classList.contains('jmn-btn-collapse')) {
          delay = setTimeout(() => {
            longPressed = true
          }, longPressTime)
        }
      },
      true
    )
    this.element.addEventListener('mouseup', (event) => {
      const target = event.target as HTMLElement

      if (target.classList.contains('jmn-btn-collapse')) {
        this._onClickButtonCollapse(target, longPressed)
        clearTimeout(delay)
        longPressed = false
      }
    })
  }
  _sortables() {
    const options = {
      group: 'shared',
      draggable: '.jmn-node',
      handle: '.jmn-btn-move',
      dragoverBubble: true,
      ghostClass: 'jmn-move-ghost',
      chosenClass: 'jmn-move-chosen',
      dragClass: 'jmn-move-drag',
      animation: 10,
      fallbackOnBody: true,
      swapThreshold: 0.5,
      onMove: (event: any) => {
        return this._onMove(event)
      },
      onEnd: (event: any) => {
        return this._onMoveEnd(event)
      }
    }

    const sortables = this.element.querySelectorAll('.jmn-sortable')
    sortables.forEach((element) => {
      this.#sortableLists[element.id] = Sortable.create(element as HTMLElement, options)
    })
    return sortables
  }
  _onMove(event: { dragged: HTMLElement; to: HTMLElement; from: HTMLElement }) {
    const dragged = event.dragged
    const targetList = event.to

    const types = targetList.dataset.types
    const type = dragged.dataset.type

    if (!types || !type) return false

    const allowedMove = types.includes(type)

    let eventCanceled = this._dispatchEvent('jmn-move', {
      dragged: dragged,
      from: event.from,
      to: event.to,
      allowed: allowedMove
    })

    if (eventCanceled) {
      this.#dragBlocked = true
      return false
    }

    return allowedMove
  }
  _onMoveEnd(event: {
    item: HTMLElement
    to: HTMLElement
    from: HTMLElement
    newIndex: number
  }): void {
    if (this.#dragBlocked) {
      this.#dragBlocked = false
      return
    }

    const itemId = event.item.dataset.id
    if (!itemId) return

    const targetComponent =
      window.jsonMenuNestedComponents[event.to.closest('.json-menu-nested-component')?.id as string]
    const fromComponent =
      window.jsonMenuNestedComponents[
        event.from.closest('.json-menu-nested-component')?.id as string
      ]

    console.debug(targetComponent, fromComponent, window.jsonMenuNestedComponents)

    const position = event.newIndex
    const toParentId = (event.to.closest('.jmn-node') as HTMLElement)?.dataset.id as string
    const fromParentId = (event.from.closest('.jmn-node') as HTMLElement)?.dataset.id

    if (targetComponent.id === fromComponent.id) {
      this._post(`/item/${itemId}/move`, {
        fromParentId: fromParentId,
        toParentId: toParentId,
        position: position
      }).finally(() => targetComponent.load({ activeItemId: itemId }))
    } else {
      fromComponent
        .itemGet(itemId)
        .then((json: any) => {
          if (!Object.hasOwn(json, 'item')) throw new Error(JSON.stringify(json))
          return targetComponent.itemAdd(toParentId, json.item, position)
        })
        .then((response: any) => {
          if (!Object.hasOwn(response, 'success') || !response.success)
            throw new Error(JSON.stringify(response))
          return fromComponent.itemDelete(itemId)
        })
        .catch(() => {})
        .finally(() => {
          targetComponent.load({ activeItemId: itemId })
          fromComponent.load()
        })
    }
  }
  _ajaxModal(element: HTMLElement, path: string, eventType: string) {
    let activeItemId: string | null = null
    const modalSize = element.dataset.modalSize ?? this.modalSize

    let handlerClose = () => {
      this.load({ activeItemId: activeItemId })
      ajaxModal.modal.removeEventListener('ajax-modal-close', handlerClose)
    }

    ajaxModal.modal.addEventListener('ajax-modal-close', handlerClose)
    ajaxModal.load({ url: `${this.#pathPrefix}${path}`, size: modalSize }, (json: any) => {
      let eventCanceled = this._dispatchEvent(eventType, { data: json, ajaxModal: ajaxModal })
      if (eventCanceled) ajaxModal.modal.removeEventListener('ajax-modal-close', handlerClose)

      if (eventType === 'jmn-add' || eventType === 'jmn-edit') {
        if (!Object.hasOwn(json, 'success') || !json.success) return
        if (Object.hasOwn(json, 'load')) this.#loadParentIds.push(json.load)
        if (Object.hasOwn(json, 'item') && Object.hasOwn(json.item, 'id'))
          activeItemId = json.item.id

        ajaxModal.close()
      }
    })
  }
  _dispatchEvent(eventType: string, detail: any) {
    detail.jmn = this
    return !this.element.dispatchEvent(
      new CustomEvent(eventType, {
        bubbles: true,
        cancelable: true,
        detail: detail
      })
    )
  }
  async _get(path: string) {
    this.loading(true)
    const response = await fetch(`${this.#pathPrefix}${path}`, {
      method: 'GET',
      headers: { 'Content-Type': 'application/json' }
    })
    return response.json()
  }
  async _post(path: string, data = {}) {
    this.loading(true)
    const response = await fetch(`${this.#pathPrefix}${path}`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(data)
    })
    return response.json()
  }
}
