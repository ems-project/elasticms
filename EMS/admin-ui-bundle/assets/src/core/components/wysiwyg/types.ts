export class WysiwygProfile {
    editor: string = ''
    styles: {
        name: string
        config: object
    }[] = []
    config: {
        emsBrowsers:
            | undefined
            | {
                  browser_object:
                      | undefined
                      | {
                            url: string
                            label: string
                        }
                  browser_file:
                      | undefined
                      | {
                            url: string
                            label: string
                        }
                  browser_image:
                      | undefined
                      | {
                            url: string
                            label: string
                        }
              }
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
