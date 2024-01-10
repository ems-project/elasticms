export class AddedDomEvent {
  constructor (target) {
    this._event = new CustomEvent('emsAddedDomEvent', { target })
    this._target = target
    document.dispatchEvent(this._event)
  }
}
