import $ from 'jquery'
import { AddedDomEvent } from '../events/addedDomEvent'
import { ChangeEvent } from '../events/changeEvent'
import 'jquery-ui-bundle/jquery-ui.js'

class Collection {
  load(target) {
    this.initSortable(target)
    this.addAddButtonListeners(target)
    this.addRemoveButtonListeners(target)
    this.addCollapsibleListeners(target)
  }

  initSortable(target) {
    $(target).find('.ems-sortable > div').sortable({
      handle: '.ems-handle'
    })
  }

  addAddButtonListeners(target) {
    $(target)
      .find('.add-content-button')
      .on('click', function (e) {
        // prevent the link from creating a "#" on the URL
        e.preventDefault()

        const panel = $(this).closest('.collection-panel')
        const index = panel.data('index')
        const prototype = panel.data('prototype')
        const prototypeName = new RegExp(panel.data('prototype-name'), 'g')
        const prototypeLabel = new RegExp(panel.data('prototype-label'), 'g')

        // Replace '__label__name__$fieldId__' in the prototype's HTML to
        // Replace '__name__$fieldId__' in the prototype's HTML to
        // instead be a number based on how many items we have
        const newForm = $(
          prototype.replace(prototypeLabel, index + 1).replace(prototypeName, index)
        )
        // increase the index with one for the next item
        panel.data('index', index + 1)

        panel.children('.panel-body').children('.collection-panel-container').append(newForm)
        const addedDomEvent = new AddedDomEvent(newForm.get(0))
        addedDomEvent.dispatch()

        const changeEvent = new ChangeEvent(this)
        changeEvent.dispatch()
      })
  }

  addRemoveButtonListeners(target) {
    $(target)
      .find('.remove-content-button')
      .on('click', function (e) {
        // prevent the link from creating a "#" on the URL
        e.preventDefault()

        const panel = $(this).closest('.collection-item-panel')
        panel.find('input._ems_internal_deleted').val('deleted')
        panel.hide()

        const event = new ChangeEvent(this)
        event.dispatch()
      })
  }

  addCollapsibleListeners(target) {
    $(target)
      .find('.collapsible-collection')
      .on('click', '.button-collapse', function () {
        const $isExpanded = $(this).attr('aria-expanded') === 'true'
        $(this).parent().find('button').attr('aria-expanded', !$isExpanded)

        const panel = $(this).closest('.panel')
        panel.find('.collapse').first().collapse('toggle')
      })
      .on('click', '.button-collapse-all', function () {
        const $isExpanded = $(this).attr('aria-expanded') === 'true'
        $(this).parent().find('button').attr('aria-expanded', !$isExpanded)

        const panel = $(this).closest('.panel')
        panel.find('.button-collapse').attr('aria-expanded', !$isExpanded)
        panel.find('.button-collapse-all').attr('aria-expanded', !$isExpanded)

        if (!$isExpanded) {
          panel.find('.collapse').collapse('show')
        } else {
          panel.find('.collapse').collapse('hide')
        }
      })
  }
}

export default Collection
