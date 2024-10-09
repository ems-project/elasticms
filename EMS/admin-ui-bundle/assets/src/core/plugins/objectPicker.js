import $ from 'jquery'
import { formatRepo, formatRepoSelection } from '../helpers/repo'

export default class ObjectPicker {
  load(target) {
    const searchApiUrl = $('body').data('search-api')
    const targetQuery = $(target)

    targetQuery.find('.objectpicker').each(function () {
      const selectItem = $(this)

      const type = selectItem.data('type')
      const searchId = selectItem.data('search-id')
      const querySearch = selectItem.data('query-search')
      const querySearchLabel = selectItem.data('query-search-label')
      const circleOnly = selectItem.data('circleOnly')
      const dynamicLoading = selectItem.data('dynamic-loading')
      const sortable = selectItem.data('sortable')
      const locale = selectItem.data('locale')
      const referrerEmsId = selectItem.data('referrer-ems-id')

      const params = {
        escapeMarkup: function (markup) {
          return markup
        }, // let our custom formatter work
        templateResult: formatRepo, // omitted for brevity, see the source of this page
        templateSelection: formatRepoSelection, // omitted for brevity, see the source of this page
        allowClear: true,
        // https://github.com/select2/select2/issues/3781
        placeholder: querySearchLabel && querySearchLabel !== '' ? querySearchLabel : 'Search',
        theme: 'bootstrap-5',
        dropdownParent: targetQuery
      }

      if (selectItem.attr('multiple')) {
        params.closeOnSelect = false
      }

      if (dynamicLoading) {
        params.minimumInputLength = 1
        params.ajax = {
          url: searchApiUrl,
          dataType: 'json',
          delay: 250,
          data: function (params) {
            const data = {
              q: params.term, // search term
              page: params.page,
              type,
              searchId,
              querySearch
            }

            if (locale !== undefined) {
              data.locale = locale
            }
            if (referrerEmsId !== undefined) {
              data.referrerEmsId = referrerEmsId
            }

            if (circleOnly !== undefined) {
              data.circle = circleOnly
            }

            return data
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

      selectItem.select2(params)

      if (sortable) {
        selectItem
          .parent()
          .find('ul.select2-selection__rendered')
          .sortable({
            stop: function () {
              // http://stackoverflow.com/questions/45888/what-is-the-most-efficient-way-to-sort-an-html-selects-options-by-value-while
              const selected = selectItem.val()
              const options = selectItem.find('option')

              const ul = $(this)

              options.sort(function (a, b) {
                const indexA = ul.find("li[title='" + a.title.replace(/'/g, "\\'") + "']").index()
                const indexB = ul.find("li[title='" + b.title.replace(/'/g, "\\'") + "']").index()

                if (indexA > indexB) return 1
                if (indexA < indexB) return -1
                return 0
              })
              selectItem.empty().append(options)
              selectItem.val(selected)
            }
          })
      }
    })
  }
}
