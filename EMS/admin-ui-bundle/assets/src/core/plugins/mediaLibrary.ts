export default class MediaLibrary {
    components: any[] = []

    async load(target: HTMLElement | Document) {
        const elements = target.getElementsByClassName('media-lib')
        if (elements.length === 0) return

        const { default: MediaLibraryComponent } =
            await import('../components/MediaLibrary/MediaLibrary.ts')
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
