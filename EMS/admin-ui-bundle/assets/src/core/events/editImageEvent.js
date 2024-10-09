export const EMS_EDIT_IMAGE_EVENT = 'emsEditImageEvent'
export class EditImageEvent {
  constructor(url) {
    this._event = new CustomEvent(EMS_EDIT_IMAGE_EVENT, {
      detail: {
        url
      }
    })
  }

  dispatch() {
    document.dispatchEvent(this._event)
  }
}

export default EditImageEvent
