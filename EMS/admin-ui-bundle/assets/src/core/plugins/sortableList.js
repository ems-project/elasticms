import $ from 'jquery'

export default class SortableList {
  load(target) {
    $(target).find('ul.sortable').sortable()
  }
}
