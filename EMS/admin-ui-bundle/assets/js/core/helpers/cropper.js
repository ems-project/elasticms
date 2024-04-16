import 'cropperjs/dist/cropper.css'
import '../../../css/core/plugins/cropper.scss'
import CropperJS from 'cropperjs'

class Cropper {
  constructor (container) {
    const self = this
    this.image = container.querySelector('img')
    this.x = container.querySelector('.ems-cropper-x')
    this.y = container.querySelector('.ems-cropper-y')
    this.width = container.querySelector('.ems-cropper-width')
    this.height = container.querySelector('.ems-cropper-height')
    this.rotate = container.querySelector('.ems-cropper-rotate')
    this.scaleX = container.querySelector('.ems-cropper-scale-x')
    this.scaleY = container.querySelector('.ems-cropper-scale-y')
    this.cropper = new CropperJS(this.image, {
      viewMode: 0,
      crop (event) { self.change(event) }
    })
    container.querySelector('.ems-cropper-rotate-left').addEventListener('click', () => self.rotateImage(-90))
    container.querySelector('.ems-cropper-rotate-right').addEventListener('click', () => self.rotateImage(90))
    container.querySelector('.ems-cropper-flip-horizontal').addEventListener('click', () => self.flip(true, false))
    container.querySelector('.ems-cropper-flip-vertical').addEventListener('click', () => self.flip(false, true))
    container.querySelector('.ems-cropper-zoom-out').addEventListener('click', () => self.zoom(-0.1))
    container.querySelector('.ems-cropper-zoom-in').addEventListener('click', () => self.zoom(0.1))
    container.querySelector('.ems-cropper-zoom-reset').addEventListener('click', () => self.reset())
  }

  change () {
    this.x.value = event.detail.x
    this.y.value = event.detail.y
    this.width.value = event.detail.width
    this.height.value = event.detail.height
    this.rotate.value = event.detail.rotate
    this.scaleX.value = event.detail.scaleX
    this.scaleY.value = event.detail.scaleY
  }

  rotateImage (degree) {
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

  reset () {
    this.cropper.reset()
  }
}

function cropper (image) {
  return new Cropper(image)
}

export { Cropper, cropper }
