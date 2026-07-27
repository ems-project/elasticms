import { Editor } from '@tiptap/core'
import { AjaxPaste, DEFAULT_EXTENSIONS, ExtensionType } from './Extensions.ts'
import { Toolbar } from './UI/Toolbar.ts'
import { ContextMenu } from './UI/ContextMenu.ts'
import { Modules, HtmlTransform, TiptapModule } from './Types.ts'
import { WysiwygOptions, WysiwygProfile } from '../Wysiwyg/Wysiwyg.ts'
import { CkeditorStyle } from '../Wysiwyg/CKEditorConfig.ts'
import { isTransLocale, Locale, trans, TranslationKey } from './Translations.ts'
import { Dialog } from '../Dialog.ts'

interface TiptapEditorOptions {
    content?: string
    parent?: Document
    element: HTMLElement
    customModules?: TiptapModule[]
    toolbarElement?: HTMLElement | null
    wysiwygProfile?: WysiwygProfile | null
    wysiwygOptions?: WysiwygOptions | null
}

export class TiptapEditor {
    tiptap: Editor
    toolbar: Toolbar
    menu: ContextMenu
    readonly docParent: Document
    readonly docEditor: Document
    readonly profile: WysiwygProfile
    readonly modules: TiptapModule[]
    private readonly htmlTransforms: HtmlTransform[]
    private readonly options: TiptapEditorOptions
    readonly locale: Locale
    readonly mod: string = navigator.userAgent.includes('Mac') ? 'Cmd' : 'Ctrl'

    constructor(options: TiptapEditorOptions) {
        this.options = options
        this.docEditor = options.element.ownerDocument
        this.docParent = this.options.parent ?? document
        this.profile = options.wysiwygProfile ?? new WysiwygProfile()

        options.element.classList = 'wysiwyg-content'

        const lang = this.options.wysiwygOptions?.lang?.toUpperCase() ?? 'EN'
        this.locale = isTransLocale(lang) ? lang : 'EN'

        this.toolbar = new Toolbar(this)

        const { modules, extensions } = this.resolveModules([
            ...Modules,
            ...(options.customModules ?? [])
        ])

        this.modules = modules
        this.htmlTransforms = modules.flatMap((m) => m.htmlTransforms ?? [])

        this.tiptap = new Editor({
            element: { mount: options.element },
            extensions: [
                ...DEFAULT_EXTENSIONS,
                ...extensions,
                AjaxPaste.configure({ ajaxUrl: this.profile.config.emsAjaxPaste ?? null })
            ],
            content: this.transformHtml(options.content ?? '', 'toEditor'),
            onUpdate: () => this.toolbar.update(),
            onSelectionUpdate: () => this.toolbar.update(),
            onTransaction: () => this.toolbar.update()
        })

        this.menu = new ContextMenu(this)

        if (options.toolbarElement) this.attachToolbar(options.toolbarElement)
    }

    createDialog(
        title: TranslationKey,
        options: { bodyClass?: string; resizable?: boolean; minWidth?: number } = {}
    ): Dialog {
        return new Dialog(this.trans(title), {
            draggable: true,
            resizable: options.resizable,
            minWidth: options.minWidth,
            closeLabel: this.trans('modal_close'),
            bodyClass: options.bodyClass,
            doc: this.docParent
        })
    }

    trans(key: TranslationKey): string {
        return trans(this.locale, key, this.profile.config.translations).replace('{mod}', this.mod)
    }

    getWysiwygOptions(): null | WysiwygOptions {
        return this.options.wysiwygOptions ?? null
    }

    getWysiwygStyles(): CkeditorStyle[] {
        const styleSet = this.options.wysiwygOptions?.styleSet ?? null
        return (
            this.profile.styles.find((s) => s.name === styleSet)?.config ??
            this.profile.config.defaultStyles
        )
    }

    getHTML(): string {
        return this.transformHtml(this.tiptap.getHTML(), 'toOutput')
    }

    setContent(html: string) {
        this.tiptap.commands.setContent(this.transformHtml(html, 'toEditor'))
    }

    attachToolbar(target: HTMLElement) {
        target.innerHTML = ''
        this.toolbar.mount(target)
    }

    destroy() {
        this.menu.destroy()
        this.toolbar.destroy()
        this.tiptap.destroy()
        this.options.element.innerHTML = ''
    }

    private resolveModules(allModules: TiptapModule[]) {
        const removed = new Set(this.profile.config.removeButtons?.split(',') ?? [])
        const enabledModules = allModules.filter((m) => !m.isEnabled || m.isEnabled(this.profile))

        const activeModules = new Set<TiptapModule>()
        const extensionMap = new Map<string, ExtensionType>()

        const registerModule = (mod: TiptapModule) => {
            if (activeModules.has(mod)) return
            activeModules.add(mod)

            const extensions =
                typeof mod.extensions === 'function' ? mod.extensions(this) : (mod.extensions ?? [])
            extensions.forEach((ext) => extensionMap.set(ext.name, ext))
        }

        this.profile.config.toolbarGroups.forEach((entry) => {
            if (entry === '/') {
                this.toolbar.addRowBreak()
                return
            }

            const groups = [...new Set(entry.groups ? [...entry.groups, entry.name] : [entry.name])]
            groups.forEach((groupName) => {
                enabledModules.forEach((mod) => {
                    if (mod.toolbar?.group !== groupName) return

                    const validItems = (mod.toolbar.items ?? []).filter((item) => {
                        return !removed.has(item.name)
                    })

                    validItems.forEach((item) => {
                        registerModule(mod)
                        this.toolbar.addItem(groupName, item)
                        if ('extensions' in item) {
                            item.extensions?.forEach((ext) => extensionMap.set(ext.name, ext))
                        }
                    })
                })
            })
        })

        enabledModules.filter((m) => !m.toolbar?.items?.length).forEach(registerModule)

        return {
            modules: Array.from(activeModules),
            extensions: Array.from(extensionMap.values())
        }
    }

    private transformHtml(html: string, direction: 'toEditor' | 'toOutput'): string {
        if (!this.htmlTransforms.length) return html
        const doc = new DOMParser().parseFromString(`<div>${html}</div>`, 'text/html')
        const root = doc.body.firstChild as HTMLElement
        this.htmlTransforms.forEach((t) => t[direction]?.(doc))
        return root.innerHTML
    }
}
