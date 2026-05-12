import { Editor } from '@tiptap/core'
import { DEFAULT_EXTENSIONS, ExtensionType } from './extensions.ts'
import { Toolbar } from './ui/toolbar.ts'
import { ContextMenu } from './ui/contextMenu.ts'
import { Modules, HtmlTransform, TiptapModule } from './types.ts'
import { WysiwygOptions, WysiwygProfile } from '../wysiwyg/wysiwyg.ts'
import { CkeditorStyle } from '../wysiwyg/ckeditorConfig.ts'

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

    constructor(options: TiptapEditorOptions) {
        this.options = options
        this.docEditor = options.element.ownerDocument
        this.docParent = this.options.parent ?? document
        this.profile = options.wysiwygProfile ?? new WysiwygProfile()

        this.toolbar = new Toolbar(this)

        const { modules, extensions } = this.resolveModules([
            ...Modules,
            ...(options.customModules ?? [])
        ])

        this.modules = modules
        this.htmlTransforms = modules.flatMap((m) => m.htmlTransforms ?? [])

        this.tiptap = new Editor({
            element: { mount: options.element },
            extensions: [...DEFAULT_EXTENSIONS, ...extensions],
            content: this.transformHtml(options.content ?? '', 'toEditor'),
            onUpdate: () => this.toolbar.update(),
            onSelectionUpdate: () => this.toolbar.update(),
            onTransaction: () => this.toolbar.update()
        })

        this.menu = new ContextMenu(this)

        if (options.toolbarElement) this.attachToolbar(options.toolbarElement)
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
        this.tiptap.destroy()
        this.toolbar.destroy()
        this.menu.destroy()
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

            mod.extensions?.forEach((ext) => extensionMap.set(ext.name, ext))
        }

        this.profile.config.toolbarGroups.forEach((entry) => {
            if (entry === '/') {
                this.toolbar.addRowBreak()
                return
            }

            const groups = entry.groups ?? [entry.name]
            groups.forEach((groupName) => {
                enabledModules.forEach((mod) => {
                    if (mod.toolbarGroup !== groupName) return

                    const validItems = (mod.toolbar ?? []).filter((item) => {
                        return !('name' in item) || !removed.has(item.name)
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

        enabledModules.filter((m) => !m.toolbar?.length).forEach(registerModule)

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
