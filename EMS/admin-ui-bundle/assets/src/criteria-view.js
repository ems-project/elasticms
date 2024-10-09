'use strict'
import $ from 'jquery'
import { formatRepo, formatRepoSelection } from './core/helpers/repo'

function objectPickerListeners(objectPicker, maximumSelectionLength) {
  const type = objectPicker.data('type')
  const dynamicLoading = objectPicker.data('dynamic-loading')
  const searchId = objectPicker.data('search-id')
  const querySearch = objectPicker.data('query-search')

  const params = {
    escapeMarkup: function (markup) {
      return markup
    }, // let our custom formatter work
    templateResult: formatRepo, // omitted for brevity, see the source of this page
    templateSelection: formatRepoSelection // omitted for brevity, see the source of this page
  }

  if (maximumSelectionLength) {
    params.maximumSelectionLength = maximumSelectionLength
  } else if (objectPicker.attr('multiple')) {
    params.allowClear = true
    params.closeOnSelect = false
  }

  if (dynamicLoading) {
    const objectSearchUrl = document.querySelector('BODY').getAttribute('data-search-api')
    // params.minimumInputLength = 1,
    params.ajax = {
      url: objectSearchUrl,
      dataType: 'json',
      delay: 250,
      data: function (params) {
        return {
          q: params.term, // search term
          page: params.page,
          type,
          searchId,
          querySearch
        }
      },
      processResults: function (data, params) {
        // parse the results into the format expected by Select2
        // since we are using custom formatting functions we do not need to
        // alter the remote JSON data, except to indicate that infinite
        // scrolling can be used
        params.page = params.page || 1

        return {
          results: data.items,
          pagination: {
            more: params.page * 30 < data.total_count
          }
        }
      },
      cache: true
    }
  }

  objectPicker.select2(params)
}

window.onload = function () {
  const columnCriteria = $('.criteria-filter-columnrow')

  columnCriteria.change(function () {
    if (
      $('#criteria_filter_columnCriteria option:selected').val() ===
      $('#criteria_filter_rowCriteria  option:selected').val()
    ) {
      if ($(this).attr('id') === 'criteria_filter_columnCriteria') {
        $('#criteria_filter_rowCriteria').val(
          $('#criteria_filter_rowCriteria option:not(:selected)').first().val()
        )
      } else {
        $('#criteria_filter_columnCriteria').val(
          $('#criteria_filter_columnCriteria option:not(:selected)').first().val()
        )
      }
    }

    $('div#criterion select').each(function () {
      const criterionName = $(this).closest('div[data-name]').data('name')
      const colCriteria = $('#criteria_filter_columnCriteria').val()
      const rowCriteria = $('#criteria_filter_rowCriteria').val()

      // TODO: multiple not supported?
      // const attr = $(this).attr('multiple');

      if (criterionName === colCriteria || criterionName === rowCriteria) {
        objectPickerListeners($(this))
      } else {
        if ($(this).val() && $(this).val().length > 1) {
          $(this).val('')
        }
        objectPickerListeners($(this), 1)
      }
    })
  })

  columnCriteria.trigger('change')
}
