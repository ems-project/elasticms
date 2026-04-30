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

export type ContextType = 'table'

export const Modules: TiptapModule[] = [
    historyModule,
    ...basicStyleModule,
    cleanupModule,
    listModule,
    indentModule,
    justifyModule,
    ...insertModule,
    tableModule
]

export interface HtmlTransform {
    name: string
    toEditor?: (doc: Document) => void
    toOutput?: (doc: Document) => void
}

export interface MenuItem {
    context: string[]
    label: string
    icon?: string
    parent?: string
    order?: number
    command: (editor: TiptapEditor) => void
}

export interface ToolbarItem {
    name: string
    icon: string
    tooltip?: string
    extensions?: (ExtensionType)[]
    command: (editor: TiptapEditor) => void
    isActive?: (editor: TiptapEditor) => boolean
}

export interface TiptapModule {
    group?: string
    extensions?: (ExtensionType)[]
    htmlTransforms?: HtmlTransform[]
    isEnabled?: (profile: WysiwygProfile) => boolean
    toolbar?: ToolbarItem[]
    menu?: MenuItem[]
}
