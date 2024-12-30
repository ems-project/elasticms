export const EMS_CHANGE_EVENT = 'emsChangeEvent'

export class ChangeEvent {
  private _form: HTMLFormElement | null
  private _event: CustomEvent

  constructor(input: HTMLElement) {
    this._form = input.closest('form')
    this._event = new CustomEvent(EMS_CHANGE_EVENT, {
      detail: {
        form: this._form,
        input
      }
    })
  }

  dispatch(): void {
    if (this._form === null) {
      return
    }
    this._form.dispatchEvent(this._event)
  }
}

export default ChangeEvent
