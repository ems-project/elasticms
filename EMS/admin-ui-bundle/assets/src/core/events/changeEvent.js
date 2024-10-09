export const EMS_CHANGE_EVENT = 'emsChangeEvent'
export class ChangeEvent {
  constructor(input) {
    this._form = input.closest('form')
    this._input = input

    this._event = new CustomEvent(EMS_CHANGE_EVENT, {
      detail: {
        form: this._form,
        input
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
export default ChangeEvent
