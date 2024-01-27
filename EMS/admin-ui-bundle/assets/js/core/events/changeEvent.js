export const EMS_CHANGE_EVENT = 'emsChangeEvent'
export class ChangeEvent {
  constructor (input) {
    this._event = new CustomEvent(EMS_CHANGE_EVENT, { input })
    this._input = input
  }

  dispatch () {
    document.dispatchEvent(this._event)
  }
}
export default ChangeEvent
