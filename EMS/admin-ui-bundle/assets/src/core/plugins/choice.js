export default class Choice {
  load(target) {
    this.addFieldsToDisplayByValue(target)
  }

  addFieldsToDisplayByValue(target) {
    const elements = target.getElementsByClassName('fields-to-display-by-input-value')
    for (let i = 0; i < elements.length; i++) {
      const fieldsToDisplay = elements[i]
        .closest('.fields-to-display-by-value')
        .getElementsByClassName('fields-to-display-for')
      elements[i].onchange = function () {
        const value = elements[i].value
        for (let j = 0; j < fieldsToDisplay.length; j++) {
          fieldsToDisplay[j].closest('.form-group').style.display = fieldsToDisplay[
            j
          ].classList.contains('fields-to-display-for-' + value)
            ? 'block'
            : 'none'
        }
      }
      elements[i].onchange()
    }
  }
}
