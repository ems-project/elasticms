import { Extension, Mark, Node } from '@tiptap/core'
import { ModeModule } from './module/ModeModule.ts'
import { HistoryModule } from './module/HistoryModule.ts'
import { BasicStylesModule } from './module/BasicStylesModule.ts'
import { CleanupModule } from './module/CleanupModule.ts'
import { ListModule } from './module/ListModule.ts'
import { IndentModule } from './module/IndentModule.ts'
import { AlignModule } from './module/AlignModule.ts'
import { BlockModule } from './module/BlockModule.ts'
import { TiptapEditor } from './editor.ts'

export interface TiptapModule {
    name: string
    extensions: (Extension | Mark | Node)[]
    groups: Record<string, string[]>
    actions: Record<string, ToolbarAction>
}

export interface ToolbarAction {
    icon: string
    tooltip?: string
    command?: (editor: TiptapEditor) => void
    isActive: (editor: TiptapEditor) => boolean
}

export const DefaultModules: TiptapModule[] = [
    ModeModule,
    HistoryModule,
    BasicStylesModule,
    CleanupModule,
    ListModule,
    IndentModule,
    AlignModule,
    BlockModule
]
