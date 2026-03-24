import { WysiwygRevisionOptions } from '../components/wysiwyg/types.ts'

export default class WYSIWYG {
    editors: any[] = []
    profile: any = null

    async load(target: HTMLElement) {
        if (!this.setProfile()) return

        await this.loadInAdminUI(target)
        await this.loadInRevision(target)
    }

    private setProfile(): boolean {
        if (this.profile) return true

        const info = document.body.dataset.wysiwygInfo
        if (!info) {
            console.error('WysiwygInfo is missing')
            return false
        }

        try {
            this.profile = JSON.parse(info)
            return typeof this.profile.editor === 'string'
        } catch (e) {
            console.error('Invalid WysiwygInfo JSON', e)
            return false
        }
    }

    async loadInAdminUI(target: HTMLElement) {
        const elements = target.querySelectorAll<HTMLTextAreaElement>('textarea.ems-wysiwyg')
        for (const element of elements) {
            await this.createEditor(element)
        }
    }

    async loadInRevision(target: HTMLElement) {
        const elements = target.querySelectorAll<HTMLTextAreaElement>('textarea.ems-wysiwyg-revision')
        for (const element of elements) {
            const height = element.getAttribute('data-height')
            await this.createEditor(element, {
                onChangeEvent: 'keyup',
                styleSet: element.getAttribute('data-styles-set'),
                formatTags: element.getAttribute('data-format-tags'),
                contentCss: element.getAttribute('data-content-css'),
                height: height ? Number.parseInt(height) : null,
                referrerEmsId: element.getAttribute('data-referrer-ems-id'),
                tableDefaultCss: element.getAttribute('data-table-default-css'),
                lang: element.getAttribute('data-lang')
            })
        }
    }

    async createEditor(element: HTMLTextAreaElement, options: WysiwygRevisionOptions | null = null) {
        const name = this.profile.editor
        const Editor = await import(`../components/wysiwyg/${name}.ts`)

        this.editors.push(new Editor.default(element, options, this.profile))
    }
}