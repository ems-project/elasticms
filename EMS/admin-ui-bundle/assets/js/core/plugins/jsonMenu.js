import $ from 'jquery'
import JsonMenuSimple from '../components/jsonMenu'
import JsonMenuNested from '../components/jsonMenuNested'

class JsonMenu {
  constructor () {
    this.jsonMenus = []
    this.jsonMenusNested = []
  }

  load (target) {
    $(target).find('.json_menu_editor_fieldtype').each(function () {
      const menu = new JsonMenuSimple(this)
      this.jsonMenus.push(menu)
    })

    $(target).find('.json-menu-nested').each(function () {
      const menu = new JsonMenuNested(this)
      this.jsonMenusNested[menu.getId()] = menu
    })
  }
}

export default JsonMenu
