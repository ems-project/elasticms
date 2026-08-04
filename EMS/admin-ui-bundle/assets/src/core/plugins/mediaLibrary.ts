import MediaLibraryComponent from '../components/MediaLibrary/MediaLibrary.ts'

export default class MediaLibrary {
    components: MediaLibraryComponent[] = []

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
                    hashAlgo: body.dataset.hashAlgo ?? ''
                })
            )
        }
    }
}
