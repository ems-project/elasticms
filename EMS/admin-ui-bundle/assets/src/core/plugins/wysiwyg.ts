import { getWysiwygProfile } from '../components/Wysiwyg/Wysiwyg.ts'

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

        const editorName = getWysiwygProfile().editor
        const Editor =
            editorName === 'tiptap'
                ? await import(`../components/Wysiwyg/Tiptap.ts`)
                : await import(`../components/Wysiwyg/CKEditor4.ts`)

        this.editors.push(new Editor.default(element))
    }
}
