import 'cropperjs/dist/cropper.css'
import '../../../css/core/plugins/cropper.scss'
import CropperJS from 'cropperjs'

class Cropper {
  constructor(container) {
    const self = this
    this.container = container
    this.image = container.querySelector('img')
    this.x = container.querySelector('.ems-cropper-x')
    this.y = container.querySelector('.ems-cropper-y')
    this.width = container.querySelector('.ems-cropper-width')
    this.height = container.querySelector('.ems-cropper-height')
    this.rotate = container.querySelector('.ems-cropper-rotate')
    this.scaleX = container.querySelector('.ems-cropper-scale-x')
    this.scaleY = container.querySelector('.ems-cropper-scale-y')
    this.backgroundColor = container.querySelector('.ems-cropper-background-color')
    this.data = null
    if (this.x.value.length > 0) {
      this.data = {
        x: this.x.value,
        y: this.y.value,
        width: this.width.value,
        height: this.height.value,
        rotate: this.rotate.value,
        scaleX: this.scaleX.value,
        scaleY: this.scaleY.value
      }
    }
    this.cropper = new CropperJS(this.image, {
      viewMode: 0,
      crop(event) {
        self.change(event)
      },
      ready() {
        self.ready()
      }
    })
    container
      .querySelector('.ems-cropper-rotate-left')
      .addEventListener('click', () => self.rotateImage(-90))
    container
      .querySelector('.ems-cropper-rotate-right')
      .addEventListener('click', () => self.rotateImage(90))
    container
      .querySelector('.ems-cropper-flip-horizontal')
      .addEventListener('click', () => self.flip(true, false))
    container
      .querySelector('.ems-cropper-flip-vertical')
      .addEventListener('click', () => self.flip(false, true))
    container
      .querySelector('.ems-cropper-zoom-out')
      .addEventListener('click', () => self.zoom(-0.1))
    container.querySelector('.ems-cropper-zoom-in').addEventListener('click', () => self.zoom(0.1))
    container.querySelector('.ems-cropper-zoom-reset').addEventListener('click', () => self.reset())
    this.backgroundColor.addEventListener('change', () => self.setBackgroundColor())
  }

  change() {
    this.x.value = event.detail.x
    this.y.value = event.detail.y
    this.width.value = event.detail.width
    this.height.value = event.detail.height
    this.rotate.value = event.detail.rotate
    this.scaleX.value = event.detail.scaleX
    this.scaleY.value = event.detail.scaleY
  }

  rotateImage(degree) {
    this.cropper.rotate(degree)
  }

  flip(horizontal, vertical) {
    const data = this.cropper.getData()
    const scaleX = horizontal ? -data.scaleX : data.scaleX
    const scaleY = vertical ? -data.scaleY : data.scaleY
    this.cropper.scale(scaleX, scaleY)
  }

  zoom(ratio) {
    this.cropper.zoom(ratio)
  }

  reset() {
    this.cropper.reset()
  }

  ready() {
    if (this.data === null) {
      return
    }
    this.cropper.scale(this.data.scaleX, this.data.scaleY)
    this.cropper.rotate(this.data.rotate)

    const container = this.container.querySelector('.cropper-container')
    const canvasData = this.cropper.getCanvasData()
    const scaleY = container.clientHeight / canvasData.naturalHeight
    const scaleX = container.clientWidth / canvasData.naturalWidth

    if (this.data.x < 0) {
      canvasData.left = Math.ceil(-this.data.x * scaleX)
      canvasData.width -= canvasData.left
    }
    if (this.data.y < 0) {
      canvasData.top = Math.ceil(-this.data.y * scaleY)
      canvasData.height -= canvasData.top
    }
    if (this.data.width > canvasData.naturalWidth) {
      canvasData.width -= Math.ceil((this.data.width - canvasData.naturalWidth) * scaleX)
    }
    if (this.data.height > canvasData.naturalHeight) {
      canvasData.height -= Math.ceil((this.data.height - canvasData.naturalHeight) * scaleY)
    }
    this.cropper.setCanvasData(canvasData)

    this.cropper.setData({
      x: Math.round(this.data.x),
      y: Math.round(this.data.y),
      width: Math.round(this.data.width),
      height: Math.round(this.data.height)
    })

    this.setBackgroundColor()
  }

  setBackgroundColor() {
    if (!this.backgroundColor.value || this.backgroundColor.value === '#000000') {
      this.container.querySelector('.cropper-crop-box').style.backgroundColor = 'unset'
    } else {
      this.container.querySelector('.cropper-crop-box').style.backgroundColor =
        this.backgroundColor.value
    }
  }
}

function cropper(image) {
  return new Cropper(image)
}

export { Cropper, cropper }
