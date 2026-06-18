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
import { anchorModule } from './module/anchor.ts'
import { linkModule } from './module/link.ts'
import { specialCharModule } from './module/specialChar.ts'
import { formatModule } from './module/format.ts'
import { showBlocksModule } from './module/showBlocks.ts'
import { divModule } from './module/div.ts'
import { TranslationKey } from './translations.ts'
import { findReplaceModule } from './module/find.ts'
import { colorModule } from './module/color.ts'

export const Modules: TiptapModule[] = [
    anchorModule,
    ...basicStyleModule,
    cleanupModule,
    colorModule,
    divModule,
    findReplaceModule,
    formatModule,
    historyModule,
    indentModule,
    ...insertModule,
    justifyModule,
    linkModule,
    listModule,
    showBlocksModule,
    specialCharModule,
    stylesModule,
    tableModule
]

export interface HtmlTransform {
    name: string
    toEditor?: (doc: Document) => void
    toOutput?: (doc: Document) => void
}

export interface ContextMenuItem {
    label: TranslationKey
    icon?: string
    parent?: TranslationKey
    parentIcon?: string
    order?: number
    command: (e: TiptapEditor, ctx?: { target?: Element | null }) => void
    disabled?: (editor: TiptapEditor) => boolean
}

export interface ContextMenu {
    node?: string
    selector?: string
    order?: number
    items: ContextMenuItem[]
}

export interface ToolbarItem {
    name: string
    icon: string
    tooltip: TranslationKey
    order?: number
    extensions?: ExtensionType[]
    command: (editor: TiptapEditor) => void
    isActive?: (editor: TiptapEditor) => boolean
    isDisabled?: (editor: TiptapEditor) => boolean
}

export interface ToolbarItemCustom {
    name: string
    create: (editor: TiptapEditor) => HTMLElement
    destroy?: (editor: TiptapEditor) => void
}

export interface Toolbar {
    group?: string
    items: (ToolbarItem | ToolbarItemCustom)[]
}

export interface TiptapModule {
    extensions?: ExtensionType[] | ((editor: TiptapEditor) => ExtensionType[])
    toolbar?: Toolbar
    contextMenu?: ContextMenu
    htmlTransforms?: HtmlTransform[]
    isEnabled?: (profile: WysiwygProfile) => boolean
}
