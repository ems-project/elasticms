'use strict'

import { EMS_CHANGE_EVENT } from './core/events/changeEvent'

function change (form, input, event) {
  return undefined
}

window.onload = function () {
  const form = document.querySelector('form[name=revision]')
  form.addEventListener(EMS_CHANGE_EVENT, (event) => change(form, event.detail.input, event))
}
