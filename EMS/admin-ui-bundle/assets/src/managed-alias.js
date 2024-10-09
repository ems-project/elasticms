'use strict'

const managedAliasIndexes = document.getElementById('managed_alias_align_indexes')
managedAliasIndexes.addEventListener('change', function () {
  try {
    const indexes = JSON.parse(this.options[this.selectedIndex].dataset.indexes)
    const options = document.querySelectorAll('form[name=managed_alias] input.align-index')
    for (let i = 0; i < options.length; i++) {
      options[i].checked = indexes.includes(options[i].value)
    }
  } catch (e) {
    console.log(e)
  }
})
