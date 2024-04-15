import $ from 'jquery'
import 'jquery-lazyload'
import 'cropperjs/dist/cropper.css'
import '../../../css/core/plugins/cropper.scss'
import Cropper from 'cropperjs'

class Image {
  load (target) {
    this.loadLazy(target)
    this.loadCropper(target)
  }

  loadLazy (target) {
    const query = $(target)
    query.find('img.lazy').show().lazyload({
      effect: 'fadeIn',
      threshold: 200
    })
  }

  loadCropper (target) {
    const images = target.querySelectorAll('img.ems-cropper')
    for (let i = 0; i < images.length; ++i) {
      const cropper = new Cropper(images[i], {
        aspectRatio: 16 / 9,
        crop (event) {
          console.log(event.detail.x)
          console.log(event.detail.y)
          console.log(event.detail.width)
          console.log(event.detail.height)
          console.log(event.detail.rotate)
          console.log(event.detail.scaleX)
          console.log(event.detail.scaleY)
        }
      })
    }
  }
}

export default Image
