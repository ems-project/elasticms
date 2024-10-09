export const EMS_FORM_RESPONSE_EVENT_EVENT = 'emsFormResponseEvent'
export class FormResponseEvent {
  constructor(form, response) {
    this._form = form

    this._event = new CustomEvent(EMS_FORM_RESPONSE_EVENT_EVENT, {
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
export default FormResponseEvent
