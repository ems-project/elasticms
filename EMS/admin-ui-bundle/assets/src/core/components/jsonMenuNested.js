import ajaxModal from '../helpers/ajaxModal'
import { ajaxJsonPost } from '../helpers/ajax'
import collapse from '../helpers/collapse'
import $ from 'jquery'
import { v4 } from 'uuid'

export default class JsonMenuNested {
  copyName = 'json_menu_nested_copy'
  nodes = {}
  urls = {}
  selectItemId = false
  config = null

  getId() {
    return this.target.getAttribute('id')
  }

  getStructureJson(includeRoot = false) {
    const makeChildren = (element) => {
      const children = []
      const childList = element.querySelector('ol.json-menu-nested-list')
      if (childList) {
        childList.querySelectorAll(':scope > li.json-menu-nested-item').forEach((li) => {
          const childItem = JSON.parse(li.dataset.item)
          childItem.children = makeChildren(li)
          children.push(childItem)
        })
      }
      return children
    }

    const children = makeChildren(this.target)

    if (!includeRoot) {
      return JSON.stringify(children)
    }

    const rootItem = JSON.parse(this.target.dataset.item)
    rootItem.children = makeChildren(this.target)

    return JSON.stringify(rootItem)
  }

  loading(flag) {
    const loading = this.target.querySelector('.json-menu-nested-loading')
    loading.style.display = flag ? 'flex' : 'none'
  }

  selectItem(itemId, scroll = false) {
    const item = this._getElementItem(itemId)
    if (item === null) {
      return
    }

    this.target.querySelectorAll('li.json-menu-nested-item').forEach((li) => {
      li.classList.remove('json-menu-nested-item-selected')
    })
    item.classList.add('json-menu-nested-item-selected')

    let parentNode = item.parentNode
    if (parentNode === null) {
      return
    }

    while (parentNode) {
      if (parentNode.classList.contains('json-menu-nested-root')) {
        break
      }
      if (parentNode.classList.contains('json-menu-nested-item')) {
        const btnCollapse = parentNode.querySelector('.btn-collapse')
        if (btnCollapse) {
          btnCollapse.dispatchEvent(new CustomEvent('show'))
        }
      }

      parentNode = parentNode.parentNode
    }

    if (scroll) {
      setTimeout(() => {
        item.scrollIntoView()
      }, 1000)
    }
  }

  constructor(target) {
    const self = this
    this.target = target

    this._parseAttributes()

    if (this.target.classList.contains('json-menu-nested-sortable')) {
      // this.nestedSortable = $(this.target)
      //   .find('ol.json-menu-nested-root')
      //   .nestedSortable({
      //     handle: 'a.btn-json-menu-nested-move',
      //     items: 'li.json-menu-nested-sortable',
      //     isTree: true,
      //     expression: /.*/,
      //     toleranceElement: '> div',
      //     update: function () {
      //       self._relocate()
      //     },
      //     isAllowed: function (placeholder, parent, current) {
      //       const li = $(current).data()
      //       const parentData = parent ? $(parent).data() : $(self.target).data()
      //
      //       const draggingNode = self.nodes[li.nodeId]
      //       const targetNode = self.nodes[parentData.nodeId]
      //
      //       return targetNode.addNodes.includes(draggingNode.name)
      //     }
      //   })
    }

    this._addEventListeners(this.target)
    window.addEventListener('focus', () => {
      this._refreshPasteButtons()
    })
    this._initSilentPublish()

    if (this.selectItemId) {
      this.selectItem(this.selectItemId, true)
      this.loading(false)
    } else {
      this.loading(false)
    }
  }

  _parseAttributes() {
    if (this.target.hasAttribute('data-hidden-field-id')) {
      this.hiddenField = document.getElementById(this.target.dataset.hiddenFieldId)
    }

    this.config = this.target.dataset.config
    this.nodes = JSON.parse(this.target.dataset.nodes)
    this.urls = JSON.parse(this.target.dataset.urls)
    this.selectItemId = this.target.hasAttribute('data-select-item-id')
      ? this.target.dataset.selectItemId
      : false
  }

  _addEventListeners(element) {
    this._relocate()
    this._buttonItemAdd(element)
    this._buttonItemEdit(element)
    this._buttonItemDelete(element)
    this._buttonItemPreview(element)
    this._buttonItemCopy(element)
    this._buttonItemCopyAll(element)
    this._buttonItemPaste(element)
    this._refreshPasteButtons()
  }

  _relocate() {
    this.target.querySelectorAll('li.json-menu-nested-item').forEach((li) => {
      li.classList.remove('json-menu-nested-item-selected')
    })

    this.target.querySelectorAll('ol').forEach((ol) => {
      if (!ol.hasAttribute('data-list')) {
        ol.setAttribute('data-list', ol.parentElement.dataset.item)
      }
      if (!ol.classList.contains('json-menu-nested-list')) {
        ol.classList.add('json-menu-nested-list')
      }
      if (!ol.classList.contains('collapse') && !ol.classList.contains('json-menu-nested-root')) {
        ol.classList.add('collapse')
      }
    })

    collapse()

    if (Object.hasOwn(this, 'hiddenField') && this.hiddenField !== null) {
      if (this.hiddenField.classList.contains('json-menu-nested-silent-publish')) {
        this.hiddenField.value = this.getStructureJson(true)
        this.hiddenField.dispatchEvent(new CustomEvent('silentPublish'))
      } else {
        this.hiddenField.value = this.getStructureJson()
        $(this.hiddenField).trigger('input').trigger('change')
      }
    }
  }

  _getElementItem(itemId) {
    return this.target.parentElement.querySelector(`[data-item-id="${itemId}"]`)
  }

  _getElementItemList(itemId) {
    return this.target.querySelector(`[data-list="${itemId}"]`)
  }

  _getCopy() {
    if (Object.hasOwn(localStorage, this.copyName)) {
      const loopJson = (json, callback, result = {}) => {
        for (const [key, value] of Object.entries(json)) {
          if (key === 'children') {
            result[key] = value.map((e) => loopJson(e, callback))
          } else {
            result[key] = callback(key, value)
          }
        }
        return result
      }

      const json = JSON.parse(localStorage.getItem(this.copyName))

      return loopJson(json, (key, value) => (key === 'id' && value !== '_root' ? v4() : value))
    }

    return false
  }

  _setCopy(value) {
    localStorage.setItem(this.copyName, JSON.stringify(value))
    this._refreshPasteButtons()
  }

  _buttonItemAdd(element) {
    element.querySelectorAll('.btn-json-menu-nested-add').forEach((btnAdd) => {
      btnAdd.onclick = (e) => {
        e.preventDefault()

        const itemId = btnAdd.dataset.itemId
        const nodeId = btnAdd.dataset.nodeId
        const level = btnAdd.dataset.level

        if (!Object.hasOwn(this.nodes, nodeId)) {
          return
        }

        const node = this.nodes[nodeId]
        const addItemId = v4()

        const params = new URLSearchParams(window.location.search)

        ajaxModal.load(
          {
            url: node.urlAdd,
            title: (node.icon ? `<i class="${node.icon}"></i> ` : '') + `Add: ${node.label}`,
            data: JSON.stringify({
              _data: {
                level,
                item_id: addItemId,
                config: this.config,
                defaultData: params.get('defaultData')
              }
            }),
            size: 'lg'
          },
          (json) => {
            if (Object.hasOwn(json, 'success') && json.success === true) {
              this._appendHtml(itemId, json.html)
              this.selectItem(addItemId)
            }
          }
        )
      }
    })
  }

  _buttonItemEdit(element) {
    element.querySelectorAll('.btn-json-menu-nested-edit').forEach((btnEdit) => {
      btnEdit.onclick = (e) => {
        e.preventDefault()

        const itemId = btnEdit.dataset.itemId
        const item = JSON.parse(this._getElementItem(itemId).dataset.item)
        const nodeId = btnEdit.dataset.nodeId
        const level = btnEdit.dataset.level

        if (!Object.hasOwn(this.nodes, nodeId)) {
          return
        }

        const node = this.nodes[nodeId]

        const callback = (json) => {
          if (!Object.hasOwn(json, 'success') || json.success === false) {
            return
          }

          const ol = this._getElementItemList(itemId)
          this._getElementItem(itemId).outerHTML = json.html

          if (ol) {
            this._getElementItem(itemId).insertAdjacentHTML('beforeend', ol.outerHTML)
          }

          this._addEventListeners(this._getElementItem(itemId).parentNode)
        }

        ajaxModal.load(
          {
            url: node.urlEdit,
            title: (node.icon ? `<i class="${node.icon}"></i> ` : '') + `Edit: ${node.label}`,
            data: JSON.stringify({
              _data: {
                level,
                item_id: itemId,
                object: item.object,
                config: this.config
              }
            }),
            size: 'lg'
          },
          callback
        )
      }
    })
  }

  _buttonItemDelete(element) {
    element.querySelectorAll('.btn-json-menu-nested-delete').forEach((btnDelete) => {
      btnDelete.onclick = (e) => {
        e.preventDefault()
        const itemId = btnDelete.dataset.itemId
        const li = this._getElementItem(itemId)
        li.parentNode.removeChild(li)
        this._relocate()
      }
    })
  }

  _buttonItemCopyAll(element) {
    const btnCopyAll = element.querySelector('.btn-json-menu-nested-copy-all')
    if (btnCopyAll === null) {
      return
    }

    btnCopyAll.onclick = (e) => {
      e.preventDefault()
      this._setCopy({
        id: '_root',
        label: '_root',
        type: '_root',
        children: JSON.parse(this.getStructureJson())
      })
    }
  }

  _buttonItemCopy(element) {
    element.querySelectorAll('.btn-json-menu-nested-copy').forEach((btnCopy) => {
      btnCopy.onclick = (e) => {
        e.preventDefault()
        const itemId = btnCopy.dataset.itemId
        const li = this._getElementItem(itemId)

        const liToObject = (li) => {
          const item = JSON.parse(li.dataset.item)

          const children = []
          const childList = this._getElementItemList(li.dataset.itemId)
          if (childList) {
            childList.querySelectorAll(':scope > li').forEach((childLi) => {
              children.push(liToObject(childLi))
            })
          }

          return {
            id: v4(),
            label: item.label,
            type: item.type,
            object: item.object,
            children
          }
        }

        const value = liToObject(li)
        this._setCopy(value)
      }
    })
  }

  _buttonItemPaste(element) {
    element.querySelectorAll('.btn-json-menu-nested-paste').forEach((btnPaste) => {
      btnPaste.onclick = (e) => {
        e.preventDefault()

        const copied = this._getCopy()

        if (copied === false) {
          return
        }

        this.loading(true)

        const itemId = btnPaste.dataset.itemId
        ajaxJsonPost(
          this.urls.paste,
          JSON.stringify({
            _data: { copied, config: this.config }
          }),
          (json) => {
            this._appendHtml(itemId, json.html)
            this.loading(false)
          }
        )
      }
    })
  }

  _buttonItemPreview(element) {
    element.querySelectorAll('.btn-json-menu-nested-preview').forEach((btnPreview) => {
      btnPreview.onclick = (e) => {
        e.preventDefault()
        const itemId = btnPreview.dataset.itemId
        const li = this._getElementItem(itemId)
        const item = JSON.parse(li.dataset.item)

        ajaxModal.load({
          url: this.urls.preview,
          title: btnPreview.dataset.title,
          size: 'lg',
          data: JSON.stringify({
            _data: { type: item.type, object: item.object }
          })
        })
      }
    })
  }

  _appendHtml(itemId, html) {
    const ol = this._getElementItemList(itemId)
    if (ol) {
      ol.insertAdjacentHTML('beforeend', html)
    } else {
      const itemList = `<ol data-list="${itemId}" class="collapse">${html}</ol>`
      this._getElementItem(itemId).insertAdjacentHTML('beforeend', itemList)
    }

    this._addEventListeners(this._getElementItem(itemId).parentElement)
  }

  _initSilentPublish() {
    if (
      this.hiddenField === null ||
      !this.hiddenField.classList.contains('json-menu-nested-silent-publish')
    ) {
      return
    }

    this.hiddenField.addEventListener('silentPublish', () => {
      const value = this.hiddenField.value
      this.loading(true)

      ajaxJsonPost(
        this.urls.silentPublish,
        JSON.stringify({
          _data: { update: value, config: this.config }
        }),
        (json, response) => {
          if (response.status === 200) {
            if (Object.hasOwn(json, 'urls')) {
              this.urls = json.urls
              this.target.setAttribute('data-urls', JSON.stringify(this.urls))
            }
            if (Object.hasOwn(json, 'nodes')) {
              this.nodes = json.nodes
              this.target.setAttribute('data-nodes', JSON.stringify(this.nodes))
            }
            setTimeout(() => this.loading(false), 250)
            return
          }

          if (Object.hasOwn(json, 'alert')) {
            document.getElementById(this.getId() + '-alerts').innerHTML = json.alert
          }
        }
      )
    })
  }

  _refreshPasteButtons() {
    const copy = this._getCopy()

    document.querySelectorAll('.btn-json-menu-nested-paste').forEach((btnPaste) => {
      const buttonLi = btnPaste.parentElement
      if (copy === null) {
        buttonLi.style.display = 'none'
        return
      }

      const nodeId = btnPaste.dataset.nodeId
      const node = this.nodes[nodeId]

      const copyType = copy.type
      const allow = btnPaste.dataset.allow

      if ((node !== undefined && node.addNodes.includes(copyType)) || allow === copyType) {
        buttonLi.style.display = 'list-item'
      } else {
        buttonLi.style.display = 'none'
      }
    })
  }
}
