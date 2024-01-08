import 'jquery-lazyload'

class Image {
    load(target) {
        $(target).find("img.lazy").show().lazyload({
            effect : "fadeIn",
            threshold : 200
        });
    }
}

export default Image
