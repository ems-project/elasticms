import $ from 'jquery'
import 'jquery-lazyload'
import { cropper } from '../helpers/cropper'

class Image {
  load(target) {
    this.loadLazy(target)
    this.loadCropper(target)
  }

  loadLazy(target) {
    const query = $(target)
    query.find('img.lazy').show().lazyload({
      effect: 'fadeIn',
      threshold: 200
    })
  }

  loadCropper(target) {
    const images = target.querySelectorAll('.ems-cropper')
    for (let i = 0; i < images.length; ++i) {
      cropper(images[i])
    }
  }
}

export default Image
