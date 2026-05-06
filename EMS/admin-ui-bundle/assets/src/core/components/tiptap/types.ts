import type { TiptapEditor } from './editor.ts'

import { historyModule } from './module/history.ts'
import { basicStyleModule } from './module/basicStyle.ts'
import { cleanupModule } from './module/cleanup.ts'
import { listModule } from './module/list.ts'
import { indentModule } from './module/indent.ts'
import { justifyModule } from './module/justify.ts'
import { insertModule } from './module/insert.ts'
import { WysiwygProfile } from '../wysiwyg/wysiwyg.ts'
import { tableModule } from './module/table.ts'
import { ExtensionType } from './extensions.ts'
import { stylesModule } from './module/styles.ts'

export type ContextType = 'table'

export const Modules: TiptapModule[] = [
    historyModule,
    ...basicStyleModule,
    cleanupModule,
    listModule,
    indentModule,
    justifyModule,
    ...insertModule,
    tableModule,
    stylesModule
]

export interface HtmlTransform {
    name: string
    toEditor?: (doc: Document) => void
    toOutput?: (doc: Document) => void
}

export interface ContextMenuItem {
    label: string
    icon?: string
    parent?: string
    parentIcon?: string
    order?: number
    command: (editor: TiptapEditor) => void
    disabled?: (editor: TiptapEditor) => boolean
}

export interface ToolbarItem {
    name: string
    icon: string
    tooltip?: string
    extensions?: ExtensionType[]
    command: (editor: TiptapEditor) => void
    isActive?: (editor: TiptapEditor) => boolean
}

export interface ToolbarItemCustom {
    create: (editor: TiptapEditor) => HTMLElement
    destroy?: (editor: TiptapEditor) => void
}

export interface TiptapModule {
    extensions?: ExtensionType[]
    toolbar?: (ToolbarItem | ToolbarItemCustom)[]
    toolbarGroup?: string
    contextMenu?: ContextMenuItem[]
    contextMenuNode?: string
    htmlTransforms?: HtmlTransform[]
    isEnabled?: (profile: WysiwygProfile) => boolean
}
