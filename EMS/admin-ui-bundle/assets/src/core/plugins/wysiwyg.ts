import { WysiwygRevisionOptions } from '../components/wysiwyg/types.ts'
import { getWysiwygProfile } from '../components/wysiwyg/wysiwygProfile.ts'

export default class WYSIWYG {
    editors: any[] = []
    private readonly selector = 'textarea.ems-wysiwyg, textarea.ems-wysiwyg-revision'

    async load(target: HTMLElement) {
        const isElement = target instanceof Element

        const elements =
            isElement && target.matches(this.selector)
                ? [target as HTMLTextAreaElement]
                : Array.from(target.querySelectorAll<HTMLTextAreaElement>(this.selector))

        for (const element of elements) {
            await this.initElement(element)
        }
    }

    private async initElement(element: HTMLTextAreaElement) {
        if (element.hasAttribute('data-wysiwyg-initialized')) return
        element.setAttribute('data-wysiwyg-initialized', 'true')

        const isRevision = element.classList.contains('ems-wysiwyg-revision')
        const options = isRevision ? this.getRevisionOptions(element) : null

        const editorName = getWysiwygProfile().editor
        const Editor = await import(`../components/wysiwyg/${editorName}.ts`)
        this.editors.push(new Editor.default(element, options))
    }

    private getRevisionOptions(element: HTMLTextAreaElement): WysiwygRevisionOptions {
        const height = element.getAttribute('data-height')
        return {
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
}
