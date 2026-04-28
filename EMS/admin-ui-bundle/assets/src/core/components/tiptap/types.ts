import { Extension, Mark, Node } from '@tiptap/core'
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

export const Modules: TiptapModule[] = [
    ...historyModule,
    ...basicStyleModule,
    ...cleanupModule,
    ...listModule,
    ...indentModule,
    ...justifyModule,
    ...insertModule,
    ...tableModule
]

export interface HtmlTransform {
    name: string
    toEditor?: (doc: Document) => void
    toOutput?: (doc: Document) => void
}

export interface TiptapModule {
    name: string
    extensions?: (Extension | Mark | Node)[]
    htmlTransforms?: HtmlTransform[]
    command?: (editor: TiptapEditor) => void
    isActive: (editor: TiptapEditor) => boolean
    isEnabled?: (profile: WysiwygProfile) => boolean
    toolbar?: {
        group: string
        icon: string
        tooltip?: string
    }
}
