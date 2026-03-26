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
    icon: IconKey
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

export type IconKey =
    | 'bold'
    | 'italic'
    | 'strike'
    | 'undo'
    | 'redo'
    | 'clear'
    | 'bulletList'
    | 'orderedList'
    | 'indent'
    | 'outdent'
    | 'alignLeft'
    | 'alignCenter'
    | 'alignRight'
    | 'alignJustify'
    | 'blockquote'
    | 'horizontalRule'
    | 'source'
    | 'maximize'
    | 'minimize'

export type IconSet = Record<IconKey, string>

export const fa4Icons: IconSet = {
    bold: '<i class="fa fa-bold"></i>',
    italic: '<i class="fa fa-italic"></i>',
    strike: '<i class="fa fa-strikethrough"></i>',
    undo: '<i class="fa fa-undo"></i>',
    redo: '<i class="fa fa-repeat"></i>',
    clear: '<i class="fa fa-eraser"></i>',
    bulletList: '<i class="fa fa-list-ul"></i>',
    orderedList: '<i class="fa fa-list-ol"></i>',
    indent: '<i class="fa fa-indent"></i>',
    outdent: '<i class="fa fa-outdent"></i>',
    alignLeft: '<i class="fa fa-align-left"></i>',
    alignCenter: '<i class="fa fa-align-center"></i>',
    alignRight: '<i class="fa fa-align-right"></i>',
    alignJustify: '<i class="fa fa-align-justify"></i>',
    blockquote: '<i class="fa fa-quote-right"></i>',
    horizontalRule: '<i class="fa fa-minus"></i>',
    source: '<i class="fa fa-code"></i>',
    maximize: '<i class="fa fa-expand"></i>',
    minimize: '<i class="fa fa-compress"></i>'
}

export const fa5Icons: IconSet = {
    bold: '<i class="fa-solid fa-bold"></i>',
    italic: '<i class="fa-solid fa-italic"></i>',
    strike: '<i class="fa-solid fa-strikethrough"></i>',
    undo: '<i class="fa-solid fa-rotate-left"></i>',
    redo: '<i class="fa-solid fa-rotate-right"></i>',
    clear: '<i class="fa-solid fa-remove-format"></i>',
    bulletList: '<i class="fa-solid fa-list-ul"></i>',
    orderedList: '<i class="fa-solid fa-list-ol"></i>',
    indent: '<i class="fa-solid fa-indent"></i>',
    outdent: '<i class="fa-solid fa-outdent"></i>',
    alignLeft: '<i class="fa-solid fa-align-left"></i>',
    alignCenter: '<i class="fa-solid fa-align-center"></i>',
    alignRight: '<i class="fa-solid fa-align-right"></i>',
    alignJustify: '<i class="fa-solid fa-align-justify"></i>',
    blockquote: '<i class="fa-solid fa-quote-right"></i>',
    horizontalRule: '<i class="fa-solid fa-grip-lines"></i>',
    source: '<i class="fa-solid fa-code"></i>',
    maximize: '<i class="fa-solid fa-expand"></i>',
    minimize: '<i class="fa-solid fa-compress"></i>'
}
