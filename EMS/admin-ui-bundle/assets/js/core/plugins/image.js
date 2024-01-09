import 'jquery-lazyload'

class Image {
  load (target) {
    const query = $(target)
    query.find('img.lazy').show().lazyload({
      effect: 'fadeIn',
      threshold: 200
    })
  }
}

export default Image
