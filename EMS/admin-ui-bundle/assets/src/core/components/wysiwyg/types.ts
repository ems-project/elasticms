export class WysiwygProfile {
    editor: string = ''
    styles: {
        name: string
        config: object
    }[] = []
    config: {
        emsBrowsers?: {
            browser_object?: {
                url: string
                label: string
            }
            browser_file?: {
                url: string
                label: string
            }
            browser_image?: {
                url: string
                label: string
            }
        }
        removeButtons?: string
        language?: string
        toolbarGroups?: {
            name: string
            groups?: string[]
        }[]
    } = {
        emsBrowsers: undefined
    }
}

export class WysiwygRevisionOptions {
    styleSet: null | string = null
    onChangeEvent: null | string = null
    height: null | number = null
    formatTags: null | string = null
    contentCss: null | string = null
    referrerEmsId: null | string = null
    tableDefaultCss: null | string = null
    lang: null | string = null
}
