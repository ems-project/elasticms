export class WysiwygProfile {
    editor: string = 'ckeditor4'
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

let cached: WysiwygProfile | undefined = undefined

export function getWysiwygProfile(): WysiwygProfile {
    if (cached !== undefined) return cached

    const info = document.body.dataset.wysiwygInfo
    if (!info) {
        cached = new WysiwygProfile()
        return cached
    }

    try {
        cached = JSON.parse(info) as WysiwygProfile
    } catch (e) {
        console.error('Invalid WysiwygInfo JSON format', e)
        cached = new WysiwygProfile()
    }

    return cached
}
