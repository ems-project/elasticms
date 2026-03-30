import { WysiwygRevisionOptions } from '../components/wysiwyg/types.ts'
import { getWysiwygProfile } from '../components/wysiwyg/WysiwygProfile.ts'

export default class WYSIWYG {
    editors: any[] = []

    async load(target: HTMLElement) {
        await Promise.all([this.loadInAdminUI(target), this.loadInRevision(target)])
    }

    async loadInAdminUI(target: HTMLElement) {
        const elements = target.querySelectorAll<HTMLTextAreaElement>('textarea.ems-wysiwyg')
        for (const element of elements) {
            await this.createEditor(element, null)
        }
    }

    async loadInRevision(target: HTMLElement) {
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

            await this.createEditor(element, options)
        }
    }

    async createEditor(
        element: HTMLTextAreaElement,
        options: WysiwygRevisionOptions | null = null
    ) {
        const editorName = getWysiwygProfile().editor
        const Editor = await import(`../components/wysiwyg/${editorName}.ts`)

        this.editors.push(new Editor.default(element, options))
    }
}
