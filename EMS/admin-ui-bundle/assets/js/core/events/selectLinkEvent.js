export const EMS_SELECT_LINK_EVENT = 'emsSelectLinkEvent'
export class SelectLinkEvent {
  constructor (target) {
    this._event = new CustomEvent(EMS_SELECT_LINK_EVENT, {
      detail: {
        target
      }
    })
    this._target = target
  }

  dispatch () {
    document.dispatchEvent(this._event)
  }
}

export default SelectLinkEvent
