export const EMS_ADDED_DOM_EVENT = 'emsAddedDomEvent'
export class AddedDomEvent {
  constructor(target) {
    this._event = new CustomEvent(EMS_ADDED_DOM_EVENT, {
      detail: {
        target
      }
    })
    this._target = target
  }

  dispatch() {
    document.dispatchEvent(this._event)
  }
}

export default AddedDomEvent
