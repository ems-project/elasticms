import FileUploader from '@elasticms/file-uploader'
import Promise from 'promise'

export class UploadAdapter {
  constructor(loader) {
    this.loader = loader
    this.initUpload = document.body.dataset.initUpload
    this.hashAlgo = document.body.dataset.hashAlgo
  }

  upload() {
    return new Promise((resolve, reject) => this._init(resolve, reject))
  }

  abort() {
    if (typeof this.fileUploader.abort === 'function') {
      this.fileUploader.abort()
    }
  }

  _init(resolve, reject) {
    this.resolve = resolve
    this.reject = reject
    this.loader.file.then((file) => this._start(file))
  }

  _start(fileHandler) {
    this.total = fileHandler.size
    this.fileUploader = new FileUploader({
      file: fileHandler,
      algo: this.hashAlgo,
      initUrl: this.initUpload,
      emsListener: this,
      onHashAvailable: (hash, type, name) => this._hash(hash, type, name),
      onProgress: (status, progress, remaining) => this._progress(status, progress, remaining),
      onUploaded: (assetUrl, previewUrl) => this._uploaded(assetUrl, previewUrl),
      onError: (message, code) => this._error(message, code)
    })
  }

  _hash() {
    this.loader.uploadTotal = this.total
    this.loader.uploaded = 0
  }

  _progress(status, progress, remaining) {
    this.loader.uploadTotal = this.total
    this.loader.uploaded = this.total - remaining
  }

  _uploaded(assetUrl) {
    this.resolve({
      default: assetUrl
    })
  }

  _error(message, code) {
    console.log(`Couldn't upload file: ${this.loader.file.name} with return code ${code}.`)
    console.log(message)
    this.reject(message)
  }
}
