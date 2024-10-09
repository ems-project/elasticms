export const EMS_SELECT_LINK_EVENT = 'emsSelectLinkEvent'
export class SelectLinkEvent {
  constructor(href, target) {
    this._event = new CustomEvent(EMS_SELECT_LINK_EVENT, {
      detail: {
        href,
        target
      }
    })
  }

  dispatch() {
    document.dispatchEvent(this._event)
  }
}

export default SelectLinkEvent
