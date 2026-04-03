import { Extension, Mark, Node } from '@tiptap/core'
import type { TiptapEditor } from './editor.ts'

import { historyActions } from './action/history.ts'
import { basicStyleActions } from './action/basicstyles.ts'
import { cleanupActions } from './action/cleanup.ts'
import { listActions } from './action/list.ts'
import { indentActions } from './action/indent.ts'
import { justifyActions } from './action/justify.ts'
import { insertActions } from './action/insert.ts'
import { WysiwygProfile } from '../wysiwyg/wysiwygProfile.ts'

export const Actions: ToolbarAction[] = [
    ...historyActions,
    ...basicStyleActions,
    ...cleanupActions,
    ...listActions,
    ...indentActions,
    ...justifyActions,
    ...insertActions
]

export interface ToolbarAction {
    name: string
    group: string
    icon: string
    tooltip?: string
    extensions?: (Extension | Mark | Node)[]
    command?: (editor: TiptapEditor) => void
    isActive: (editor: TiptapEditor) => boolean
    isEnabled?: (profile: WysiwygProfile) => boolean
}
