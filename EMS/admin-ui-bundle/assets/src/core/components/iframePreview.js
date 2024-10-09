import '../../../css/core/components/iframe_preview.scss'

export class IframePreview {
  constructor(iframe) {
    this.iframe = iframe
    const self = this
    window.addEventListener('message', function (event) {
      self.onMessage(event)
    })
  }

  onMessage(event) {
    if (event.source !== this.iframe.contentWindow) {
      return
    }

    if (event.data === 'ready') {
      this.loadBody()
    } else if (event.data === 'resize') {
      this.adjustHeight()
    } else {
      console.log('Unknown event type: ' + event.data)
    }
  }

  loadBody() {
    let body = this.iframe.getAttribute('data-iframe-body')
    body = this.#changeSelfTargetLinksToParent(body)
    const window = this.iframe.contentWindow || this.iframe.contentDocument.defaultView
    window.postMessage(body, '*')
  }

  adjustHeight() {
    const window = this.iframe.contentWindow || this.iframe.contentDocument.defaultView

    let height = window.document.documentElement.scrollHeight

    ;['border-top-width', 'border-bottom-width', 'padding-top', 'padding-bottom'].forEach((v) => {
      height += parseInt(
        window.getComputedStyle(this.iframe, null).getPropertyValue(v).replace('px', ''),
        10
      )
    })

    this.iframe.height = height
  }

  #changeSelfTargetLinksToParent(body) {
    const parser = new DOMParser()
    const dom = parser.parseFromString(body, 'text/html')
    ;[...dom.getElementsByTagName('a')].forEach((link) => {
      if (!link.getAttribute('target')) {
        link.setAttribute('target', '_parent')
      }
    })

    return dom.documentElement.outerHTML
  }
}

export default IframePreview
