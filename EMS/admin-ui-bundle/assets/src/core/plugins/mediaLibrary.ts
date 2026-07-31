import MediaLibraryComponent, { AjaxModalLike } from '../components/MediaLibrary/MediaLibrary.ts'

export default class MediaLibrary {
    components: MediaLibraryComponent[] = []
    #ajaxModal?: AjaxModalLike

    /**
     * `ajaxModal` can be provided to reuse the ajaxModal singleton matching the current
     * theme (e.g. the legacy bootstrap3 core-bundle one). When omitted, the component falls
     * back to the admin-ui-bundle bootstrap5 ajaxModal.
     */
    constructor(ajaxModal?: AjaxModalLike) {
        this.#ajaxModal = ajaxModal
    }

    load(target: HTMLElement | Document) {
        const elements = target.getElementsByClassName('media-lib')
        const body = document.querySelector('body') as HTMLElement

        for (const el of elements) {
            const element = el as HTMLElement
            if (element.dataset.mediaLibInitialized) continue
            element.dataset.mediaLibInitialized = 'true'

            this.components.push(
                new MediaLibraryComponent(element, {
                    urlMediaLib: '/component/media-lib',
                    urlInitUpload: body.dataset.initUpload ?? '',
                    hashAlgo: body.dataset.hashAlgo ?? '',
                    ajaxModal: this.#ajaxModal
                })
            )
        }
    }
}
