'use strict'

export default class Sidebar {
  constructor() {
    this.activateMenu()
  }

  activateMenu() {
    let bestMatch = null
    const menuLinks = document.querySelectorAll('div.sidebar a.nav-link')
    const pathname = window.location.pathname

    for (let i = 0; i < menuLinks.length; ++i) {
      if (
        pathname.startsWith(menuLinks[i].attributes.getNamedItem('href').value) &&
        (bestMatch === null ||
          menuLinks[i].attributes.getNamedItem('href').value.length >
            bestMatch.attributes.getNamedItem('href').value.length)
      ) {
        bestMatch = menuLinks[i]
      }
    }
    if (bestMatch === null) {
      return
    }
    while (bestMatch) {
      if (undefined !== bestMatch.classList && bestMatch.classList.contains('nav-item')) {
        for (let i = 0; i < bestMatch.children.length; ++i) {
          if (
            undefined !== bestMatch.children[i].classList &&
            bestMatch.children[i].classList.contains('nav-treeview')
          ) {
            bestMatch.classList.add('menu-is-opening')
            bestMatch.classList.add('menu-open')
            bestMatch.children[i].style.display = 'block'
          }
          if (
            undefined !== bestMatch.children[i].classList &&
            bestMatch.children[i].classList.contains('nav-link')
          ) {
            bestMatch.children[i].classList.add('active')
          }
        }
      }
      bestMatch = bestMatch.parentNode
    }
  }
}
