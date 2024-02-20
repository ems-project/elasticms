import Component from '../components/mediaLibrary'

class MediaLibrary {
  constructor () {
    this.components = []
    this.bodyData = document.querySelector('body').dataset
  }

  load (target) {
    const elements = target.getElementsByClassName('media-lib')
    const self = this;

    [].forEach.call(elements, function (el) {
      self.components.push(new Component(el, {
        urlMediaLib: '/component/media-lib',
        urlInitUpload: this.bodyData.initUpload,
        hashAlgo: this.bodyData.hashAlgo
      }))
    })
  }
}

export default MediaLibrary
