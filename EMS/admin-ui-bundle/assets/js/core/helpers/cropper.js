import 'cropperjs/dist/cropper.css'
import '../../../css/core/plugins/cropper.scss'
import CropperJS from 'cropperjs'

class Cropper {
  constructor (container) {
    const self = this
    this.image = container.querySelector('img')
    console.log(this.image)
    this.cropper = new CropperJS(this.image, {
      viewMode: 0,
      crop (event) { self.cropImage(event) }
    })
    container.querySelector('.ems-cropper-rotate-left').addEventListener('click', () => self.rotate(-90))
    container.querySelector('.ems-cropper-rotate-right').addEventListener('click', () => self.rotate(90))
    container.querySelector('.ems-cropper-flip-horizontal').addEventListener('click', () => self.flip(true, false))
    container.querySelector('.ems-cropper-flip-vertical').addEventListener('click', () => self.flip(false, true))
    container.querySelector('.ems-cropper-zoom-out').addEventListener('click', () => self.zoom(-0.1))
    container.querySelector('.ems-cropper-zoom-in').addEventListener('click', () => self.zoom(0.1))
  }

  cropImage () {
    // console.log(event.detail.x)
    // console.log(event.detail.y)
    // console.log(event.detail.width)
    // console.log(event.detail.height)
    // console.log(event.detail.rotate)
    // console.log(event.detail.scaleX)
    // console.log(event.detail.scaleY)
  }

  rotate (degree) {
    this.cropper.rotate(degree)
  }

  flip (horizontal, vertical) {
    const data = this.cropper.getData()
    const scaleX = horizontal ? -data.scaleX : data.scaleX
    const scaleY = vertical ? -data.scaleY : data.scaleY
    this.cropper.scale(scaleX, scaleY)
  }

  zoom (ratio) {
    this.cropper.zoom(ratio)
  }
}

function cropper (image) {
  return new Cropper(image)
}

export { Cropper, cropper }
