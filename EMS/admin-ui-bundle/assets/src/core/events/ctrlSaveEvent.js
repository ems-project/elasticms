export const EMS_CTRL_SAVE_EVENT = 'emsCtrlSaveEvent'
export class CtrlSaveEvent {
  constructor(event) {
    this._event = new CustomEvent(EMS_CTRL_SAVE_EVENT, {
      detail: {
        parentEvent: event
      }
    })
  }

  dispatch() {
    document.dispatchEvent(this._event)
  }
}
export default CtrlSaveEvent
