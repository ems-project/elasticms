import $ from 'jquery'
import { AddedDomEvent } from '../events/addedDomEvent'

class SearchForm {
  constructor() {
    $('#add-search-filter-button').on('click', function (e) {
      // prevent the link to scroll to the top ("#" anchor)
      e.preventDefault()

      const $listFilters = $('#list-of-search-filters')
      const prototype = $listFilters.data('prototype')
      const index = $listFilters.data('index')
      // Replace '__name__' in the prototype's HTML to
      // instead be a number based on how many items we have
      const newForm = $(prototype.replace(/__name__/g, index))

      // increase the index with one for the next item
      $listFilters.data('index', index + 1)

      console.log(newForm.get(0))
      const addedDomEvent = new AddedDomEvent(newForm.get(0))
      addedDomEvent.dispatch()
      $listFilters.append(newForm)
    })
  }

  load(target) {
    $(target)
      .find('.remove-filter')
      .on('click', function (event) {
        event.preventDefault()
        $(this).closest('.filter-container').remove()
      })
  }
}

export default SearchForm
