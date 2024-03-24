import { ensureSafeUrl } from './ckeditor5-link/src/utils'
import queryString from './queryString'

export default class Link {
  constructor (href) {
    this.href = href
    this.linkType = null
    this.contentType = null
    this.uid = null
    this.hash = null
    this.name = null
    this.type = null
  }

  isEmsLink () {
    if (!this.href || !this.href.startsWith('ems://')) {
      this.linkType = null
      this.contentType = null
      this.uid = null
      return false
    }
    const regex = /ems:\/\/(.*?):(([a-zA-Z0-9-_.]+):)?([a-zA-Z0-9-_.]+)(\?(.*))?/
    const match = this.href.match(regex)
    this.linkType = match[1]
    switch (this.linkType) {
      case 'object': {
        this.contentType = match[3]
        this.uid = match[4]
        break
      }
      case 'asset': {
        const parameters = queryString(match[6])
        this.hash = match[4]
        this.name = parameters.name || 'file.bin'
        this.type = parameters.type || 'application.bin'
        break
      }
    }

    return true
  }

  getUrl () {
    if (this.isEmsLink()) {
      switch (this.linkType) {
        case 'object':
          return document.body.dataset.revisionUrl
            .replaceAll('__type__', this.contentType)
            .replaceAll('__ouuid__', this.uid)
        case 'asset':
          return document.body.dataset.fileView
            .replaceAll('__file_identifier__', this.hash)
            .replaceAll('__file_name__', this.name)
        default:
          console.error(`Link type ${this.linkType} not supported`)
      }
    }
    return this.href && ensureSafeUrl(this.href)
  }
}
