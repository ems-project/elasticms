import { WysiwygProfile, WysiwygRevisionOptions } from '../components/wysiwyg/types.ts'

export default class WYSIWYG {
    editors: any[] = []
    profile: WysiwygProfile | null = null

    constructor() {
        this.profile = this.getProfile()
    }

    async load(target: HTMLElement, editorOptions?: Record<string, unknown>) {
        if (!this.profile) return

        await Promise.all([
            this.loadInAdminUI(target, this.profile, editorOptions),
            this.loadInRevision(target, this.profile, editorOptions)
        ])
    }

    private getProfile(): WysiwygProfile | null {
        const info = document.body.dataset.wysiwygInfo
        if (!info) {
            console.error('WysiwygInfo is missing from body dataset')
            return null
        }

        try {
            return JSON.parse(info) as WysiwygProfile
        } catch (e) {
            console.error('Invalid WysiwygInfo JSON format', e)
            return null
        }
    }

    async loadInAdminUI(
        target: HTMLElement,
        profile: WysiwygProfile,
        editorOptions?: Record<string, unknown>
    ) {
        const elements = target.querySelectorAll<HTMLTextAreaElement>('textarea.ems-wysiwyg')
        for (const element of elements) {
            await this.createEditor(element, null, profile, editorOptions)
        }
    }

    async loadInRevision(
        target: HTMLElement,
        profile: WysiwygProfile,
        editorOptions?: Record<string, unknown>
    ) {
        const elements = target.querySelectorAll<HTMLTextAreaElement>(
            'textarea.ems-wysiwyg-revision'
        )
        for (const element of elements) {
            const height = element.getAttribute('data-height')
            const options: WysiwygRevisionOptions = {
                onChangeEvent: 'keyup',
                styleSet: element.getAttribute('data-styles-set'),
                formatTags: element.getAttribute('data-format-tags'),
                contentCss: element.getAttribute('data-content-css'),
                height: height ? Number.parseInt(height, 10) : null,
                referrerEmsId: element.getAttribute('data-referrer-ems-id'),
                tableDefaultCss: element.getAttribute('data-table-default-css'),
                lang: element.getAttribute('data-lang')
            }

            await this.createEditor(element, options, profile, editorOptions)
        }
    }

    async createEditor(
        element: HTMLTextAreaElement,
        options: WysiwygRevisionOptions | null = null,
        profile: WysiwygProfile,
        editorOptions?: Record<string, unknown>
    ) {
        const Editor = await import(`../components/wysiwyg/${profile.editor}.ts`)

        this.editors.push(new Editor.default(element, options, profile, editorOptions))
    }
}
