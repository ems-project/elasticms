export const EMS_FORM_FAIL_EVENT_EVENT = 'emsFormFailEvent'
export class FormFailEvent {
  constructor(form, response) {
    this._form = form

    this._event = new CustomEvent(EMS_FORM_FAIL_EVENT_EVENT, {
      detail: {
        form,
        response
      }
    })
  }

  dispatch() {
    if (undefined === this._form) {
      return
    }
    this._form.dispatchEvent(this._event)
  }
}
export default FormFailEvent
