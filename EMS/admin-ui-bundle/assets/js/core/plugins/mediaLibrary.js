import Component from '../components/mediaLibrary'

class MediaLibrary {
  constructor () {
    this.components = []
  }

  load (target) {
    const elements = target.getElementsByClassName('media-lib')
    const bodyData = target.querySelector('body').dataset;

    [].forEach.call(elements, function (el) {
      this.components.push(new Component(el, {
        urlMediaLib: '/component/media-lib',
        urlInitUpload: bodyData.initUpload,
        hashAlgo: bodyData.hashAlgo
      }))
    })
  }
}

export default MediaLibrary
