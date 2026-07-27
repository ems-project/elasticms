import type { TiptapEditor } from './Editor.ts'

import { HistoryModule } from './Module/History.ts'
import { BasicStyleModule } from './Module/BasicStyle.ts'
import { CleanupModule } from './Module/Cleanup.ts'
import { ListModule } from './Module/List.ts'
import { IndentModule } from './Module/Indent.ts'
import { JustifyModule } from './Module/Justify.ts'
import { InsertModule } from './Module/Insert.ts'
import { WysiwygProfile } from '../wysiwyg/wysiwyg.ts'
import { TableModule } from './Module/Table.ts'
import { ExtensionType } from './Extensions.ts'
import { StylesModule } from './Module/Styles.ts'
import { AnchorModule } from './Module/Anchor.ts'
import { LinkModule } from './Module/Link.ts'
import { SpecialCharModule } from './Module/SpecialChar.ts'
import { FormatModule } from './Module/Format.ts'
import { ShowBlocksModule } from './Module/ShowBlocks.ts'
import { DivModule } from './Module/Div.ts'
import { TranslationKey } from './Translations.ts'
import { FindReplaceModule } from './Module/Find.ts'
import { ColorModule } from './Module/Color.ts'
import { IframeModule } from './Module/Iframe.ts'
import { ImageModule } from './Module/Image.ts'

export const Modules: TiptapModule[] = [
    AnchorModule,
    ...BasicStyleModule,
    CleanupModule,
    ColorModule,
    DivModule,
    IframeModule,
    FindReplaceModule,
    FormatModule,
    HistoryModule,
    ImageModule,
    IndentModule,
    ...InsertModule,
    JustifyModule,
    LinkModule,
    ...ListModule,
    ShowBlocksModule,
    SpecialCharModule,
    StylesModule,
    TableModule
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
