import Component from '../components/mediaLibrary'

class MediaLibrary {
  constructor() {
    this.components = []
  }

  load(target) {
    const elements = target.getElementsByClassName('media-lib')
    const body = document.querySelector('body')

    for (const el of elements) {
      this.components.push(
        new Component(el, {
          urlMediaLib: '/component/media-lib',
          urlInitUpload: body.dataset.initUpload,
          hashAlgo: body.dataset.hashAlgo
        })
      )
    }
  }
}

export default MediaLibrary
