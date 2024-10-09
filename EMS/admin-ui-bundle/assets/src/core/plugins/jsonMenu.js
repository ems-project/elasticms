import $ from 'jquery'
import JsonMenuSimple from '../components/jsonMenu'
import JsonMenuNested from '../components/jsonMenuNested'

class JsonMenu {
  constructor() {
    this.jsonMenus = []
    this.jsonMenusNested = []
  }

  load(target) {
    const self = this
    $(target)
      .find('.json_menu_editor_fieldtype')
      .each(function () {
        const menu = new JsonMenuSimple(this)
        self.jsonMenus.push(menu)
      })

    $(target)
      .find('.json-menu-nested')
      .each(function () {
        const menu = new JsonMenuNested(this)
        self.jsonMenusNested.push(menu)
      })
  }
}

export default JsonMenu
