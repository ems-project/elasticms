import { CKEditorConfig, CkeditorStyle } from './CKEditorConfig.ts'
import { DEFAULT_CK_VALUES } from './CKEditorConfig.ts'

export const URL_TYPES = ['url', 'fileLink', 'anchor', 'email', 'phone', 'localPage'] as const
export type UrlType = (typeof URL_TYPES)[number]

export class WysiwygProfile {
    editor: string = 'ckeditor4'
    styles: {
        name: string
        config: CkeditorStyle[]
    }[] = []
    linkTypes: [string, string][] = []
    config: {
        searchUrl: string | null
        emsAjaxPaste?: string
        imageBrowser_listUrl?: string
        ems?: {
            urlTypes?: string[]
            urlTargetDefaultBlank?: string[]
            urlAllContentTypes?: boolean
        }
        emsBrowsers: {
            browser_object?: { url: string; label: string; urlModal: string }
            browser_file?: { url: string; label: string; urlModal: string }
            browser_image?: { url: string; label: string; urlModal: string }
        }
        translations?: Record<string, Record<string, string>>
        url: {
            browseUploadedFiles: string
        }
    } & CKEditorConfig = {
        ...DEFAULT_CK_VALUES
    } as WysiwygProfile['config']

    get urlTypes(): UrlType[] {
        const configured = this.config.ems?.urlTypes as string[] | undefined
        if (!configured) return [...URL_TYPES]
        return configured.filter((t): t is UrlType => (URL_TYPES as readonly string[]).includes(t))
    }

    isUrlTargetDefaultBlank(type: string): boolean {
        return this.config.ems?.urlTargetDefaultBlank?.includes(type) ?? false
    }

    hasPlugin(name: string): boolean {
        const { plugins, extraPlugins } = this.config
        if (!plugins && !extraPlugins) return true
        return [plugins, extraPlugins].some((list) =>
            list
                ?.split(',')
                .map((p) => p.trim())
                .includes(name)
        )
    }
}

let cached: WysiwygProfile | undefined = undefined

export function getWysiwygProfile(doc: Document = document): WysiwygProfile {
    if (cached) return cached

    const defaultProfile = new WysiwygProfile()
    const info = doc.body.dataset.wysiwygInfo

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

export class WysiwygOptions {
    inRevision: boolean = false
    styleSet: null | string = null
    onChangeEvent: null | string = null
    height: null | number = null
    formatTags: null | string = null
    contentCss: null | string = null
    referrerEmsId: null | string = null
    tableDefaultCss: null | string = null
    lang: null | string = null
}

export function getWysiwygOptions(element: HTMLElement): WysiwygOptions {
    const height = element.getAttribute('data-height')
    return {
        inRevision: element.classList.contains('ems-wysiwyg-revision'),
        onChangeEvent: 'keyup',
        styleSet: element.getAttribute('data-styles-set'),
        formatTags: element.getAttribute('data-format-tags'),
        contentCss: element.getAttribute('data-content-css'),
        height: height ? Number.parseInt(height, 10) : null,
        referrerEmsId: element.getAttribute('data-referrer-ems-id'),
        tableDefaultCss: element.getAttribute('data-table-default-css'),
        lang: element.getAttribute('data-lang')
    }
}
