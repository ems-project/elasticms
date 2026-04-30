import '../../../../css/core/components/_tiptap.scss'

import { Editor } from '@tiptap/core'
import { DEFAULT_EXTENSIONS, ExtensionType } from './extensions.ts'
import { Toolbar } from './toolbar.ts'
import { ContextMenu } from './contextMenu.ts'
import { Modules, HtmlTransform, TiptapModule } from './types.ts'
import { WysiwygProfile } from '../wysiwyg/wysiwyg.ts'

interface TiptapEditorOptions {
    content?: string
    element: HTMLElement
    customModules?: TiptapModule[]
    toolbarElement?: HTMLElement | null
    wysiwygProfile?: WysiwygProfile | null
}

export class TiptapEditor {
    tiptap: Editor
    toolbar: Toolbar
    menu: ContextMenu
    element: HTMLElement
    readonly modules: TiptapModule[]
    private readonly htmlTransforms: HtmlTransform[]

    constructor(options: TiptapEditorOptions) {
        this.element = options.element
        this.toolbar = new Toolbar(this)

        const profile = options.wysiwygProfile ?? new WysiwygProfile()
        const { modules, extensions } = this.resolveModules(
            [...Modules, ...(options.customModules ?? [])],
            profile
        )

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
        this.element.innerHTML = ''
    }

    private resolveModules(allModules: TiptapModule[], profile: WysiwygProfile) {
        const removed = new Set(profile.config.removeButtons?.split(',') ?? [])
        const enabledModules = allModules.filter((m) => !m.isEnabled || m.isEnabled(profile))

        const activeModules = new Set<TiptapModule>()
        const extensionMap = new Map<string, ExtensionType>()

        const registerModule = (mod: TiptapModule) => {
            if (activeModules.has(mod)) return
            activeModules.add(mod)

            mod.extensions?.forEach((ext) => extensionMap.set(ext.name, ext))
        }

        profile.config.toolbarGroups.forEach((entry) => {
            if (entry === '/') return

            const groups = entry.groups ?? [entry.name]
            groups.forEach((groupName) => {
                enabledModules.forEach((mod) => {
                    const validItems = (mod.toolbar ?? []).filter(
                        (item) => item.group === groupName && !removed.has(item.name)
                    )

                    validItems.forEach((item) => {
                        registerModule(mod)
                        this.toolbar.addItem(item)
                        item.extensions?.forEach((ext) => extensionMap.set(ext.name, ext))
                    })
                })
            })
        })

        enabledModules.filter((m) => m.toolbar?.length === 0).forEach(registerModule)

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
