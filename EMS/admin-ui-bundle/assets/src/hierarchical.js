'use strict'
import $ from 'jquery'
import { AddedDomEvent } from './core/events/addedDomEvent'

$(document).ready(function () {
  const prototype = $('#hierarchical-row').data('hierarchical-item-url')
  const contentType = $('#hierarchical-row').data('content-type')

  $('#reorganize_addItem_value').on('select2:select', function () {
    if (
      $(this)
        .val()
        .startsWith(contentType + ':') &&
      $('li#' + $(this).val().replace(':', '\\:')).length > 0
    ) {
      $('#modal-notifications .modal-body').append(
        '<p>This item is already presents in this structure/menu</p>'.replace(
          '%ouuid%',
          $(this).val()
        )
      )
      $('#modal-notifications').modal('show')
    } else {
      $.get(prototype.replace('__key__', $(this).val()), function (data) {
        const item = $(data)

        $('#root-list').append(item)
        const elements = item.get()
        for (let i = 0; i < elements.length; ++i) {
          const event = new AddedDomEvent(elements[i])
          event.dispatch()
        }
      })
    }

    $(this).val(null).trigger('change')
  })
})
