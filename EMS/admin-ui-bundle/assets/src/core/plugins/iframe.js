import IframePreview from '../components/iframePreview'
class Iframe {
  #iframes = []

  load(target) {
    const iframes = target.querySelectorAll('iframe[data-iframe-body]')
    for (let i = 0; i < iframes.length; i++) {
      this.#iframes.push(new IframePreview(iframes[i]))
    }
  }
}

export default Iframe
