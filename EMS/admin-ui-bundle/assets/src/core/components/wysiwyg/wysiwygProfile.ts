type ToolbarGroup = { name: string; groups?: string[] } | '/'
interface CkeditorConfig {
    extraPlugins?: string
    removeButtons?: string
    language: string
    toolbarGroups: ToolbarGroup[]
}

const DEFAULT_CK_VALUES: CkeditorConfig = {
    language: 'en',
    toolbarGroups: [
        { name: 'undo' },
        { name: 'insert' },
        { name: 'links' },
        { name: 'tools' },
        { name: 'document', groups: ['mode'] },
        '/',
        { name: 'basicstyles', groups: ['basicstyles', 'cleanup'] },
        { name: 'paragraph', groups: ['list', 'indent', 'blocks', 'align'] },
        { name: 'styles' },
        { name: 'colors' }
    ]
}

export class WysiwygProfile {
    editor: string = 'ckeditor4'
    styles: {
        name: string
        config: object
    }[] = []
    config: {
        emsBrowsers?: {
            browser_object?: { url: string; label: string }
            browser_file?: { url: string; label: string }
            browser_image?: { url: string; label: string }
        }
    } & CkeditorConfig = {
        emsBrowsers: undefined,
        ...DEFAULT_CK_VALUES
    }
}

let cached: WysiwygProfile | undefined = undefined

export function getWysiwygProfile(): WysiwygProfile {
    if (cached) return cached

    const defaultProfile = new WysiwygProfile()
    const info = document.body.dataset.wysiwygInfo

    console.debug(info)

    if (!info) {
        cached = defaultProfile
        return cached
    }

    try {
        const parsed = JSON.parse(info)
        cached = Object.assign(new WysiwygProfile(), parsed, {
            config: {
                ...defaultProfile.config,
                ...(parsed.config || {})
            }
        })
    } catch (e) {
        console.error('Invalid WysiwygInfo JSON format', e)
        cached = defaultProfile
    }

    return cached!
}
