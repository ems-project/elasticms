import { ensureSafeUrl } from './ckeditor5-link/src/utils'

export default class Link {
  constructor (href) {
    this.href = href
    this.linkType = null
    this.contentType = null
    this.uid = null
  }

  isEmsLink () {
    if (!this.href || !this.href.startsWith('ems://')) {
      this.linkType = null
      this.contentType = null
      this.uid = null
      return false
    }
    const regex = /ems:\/\/(.*?):(([a-zA-Z0-9-_.]+):)?([a-zA-Z0-9-_.]+)/
    const match = this.href.match(regex)
    this.linkType = match[1]
    this.contentType = match[3]
    this.uid = match[4]

    return true
  }

  getUrl () {
    if (this.isEmsLink()) {
      switch (this.linkType) {
        case 'object':
          return document.body.dataset.revisionUrl
            .replaceAll('__type__', this.contentType)
            .replaceAll('__ouuid__', this.uid)
        default:
          console.error(`Link type ${this.linkType} not supported`)
      }
    }
    return this.href && ensureSafeUrl(this.href)
  }
}
