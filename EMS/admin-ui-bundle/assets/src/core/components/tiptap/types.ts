import { Extension, Mark, Node } from '@tiptap/core'
import type { TiptapEditor } from './editor.ts'

import { modeActions } from './action/mode.ts'
import { historyActions } from './action/history.ts'
import { basicStyleActions } from './action/basicstyles.ts'
import { cleanupActions } from './action/cleanup.ts'
import { listActions } from './action/list.ts'
import { indentActions } from './action/indent.ts'
import { justifyActions } from './action/justify.ts'
import { insertActions } from './action/insert.ts'

export const Actions: ToolbarAction[] = [
    ...modeActions,
    ...historyActions,
    ...basicStyleActions,
    ...cleanupActions,
    ...listActions,
    ...indentActions,
    ...justifyActions,
    ...insertActions
]

export const ActionMap = new Map(Actions.map((a) => [a.name, a]))

export function getActionsByGroup(groupName: string): ToolbarAction[] {
    return Actions.filter((action) => action.group === groupName)
}

export interface ToolbarAction {
    name: string
    group: string
    icon: string
    tooltip?: string
    extensions?: (Extension | Mark | Node)[]
    command?: (editor: TiptapEditor) => void
    isActive: (editor: TiptapEditor) => boolean
}

export type ToolbarConfigItem = { name: string; groups?: string[] } | '/'

export const DefaultToolbarConfig: ToolbarConfigItem[] = [
    { name: 'undo' },
    { name: 'insert' },
    { name: 'links' },
    { name: 'tools' },
    { name: 'document', groups: ['mode'] },
    '/',
    { name: 'basicstyles', groups: ['basicstyles', 'cleanup'] },
    { name: 'paragraph', groups: ['list', 'indent', 'blocks', 'align'] },
    { name: 'styles' },
    { name: 'colors' }
]
