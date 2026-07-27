import { Node, mergeAttributes } from '@tiptap/core'
import { Table, TableCell, TableRow } from '@tiptap/extension-table'
import IconTable from '@tabler/icons/outline/table.svg?raw'
import { TiptapModule } from '../Types.ts'
import { TableCleanHtmlTransform } from './Table/CleanTransformer.ts'
import { Caption, tableCaptionHtmlTransform, TableFigure } from './Table/Caption.ts'
import { contextMenu } from './Table/ContextMenu.ts'
import { openTableDialog } from './Table/DialogTable.ts'
import { CustomTableHeader, tableTheadHtmlTransform } from './Table/Header.ts'

export const TableModule: TiptapModule = {
    isEnabled: (wysiwygProfile) => wysiwygProfile.hasPlugin('table'),
    extensions: getExtensions(),
    htmlTransforms: [tableCaptionHtmlTransform, TableCleanHtmlTransform, tableTheadHtmlTransform],
    toolbar: {
        group: 'insert',
        items: [
            {
                name: 'Table',
                icon: IconTable,
                tooltip: 'table_insert',
                order: 2,
                command: (e) => openTableDialog(e, 'insert'),
                isActive: (e) => e.tiptap.isActive('table') || e.tiptap.isActive('tableFigure')
            }
        ]
    },
    contextMenu: {
        node: 'table',
        items: contextMenu,
        order: 0
    }
}

function getExtensions(): Node[] {
    const CustomTable = Table.extend({
        addAttributes() {
            return {
                ...this.parent?.(),
                class: { default: null },
                id: { default: null },
                summary: { default: null },
                align: { default: null },
                border: { default: null },
                cellpadding: { default: null },
                cellspacing: { default: null },
                width: { default: null },
                height: { default: null },
                dataUserStyle: {
                    default: null,
                    parseHTML: (el) => el.getAttribute('data-user-style'),
                    renderHTML: (attrs) =>
                        attrs.dataUserStyle
                            ? { 'data-user-style': attrs.dataUserStyle, style: attrs.dataUserStyle }
                            : {}
                }
            }
        },
        renderHTML({ HTMLAttributes }) {
            return [
                'table',
                mergeAttributes(this.options.HTMLAttributes, HTMLAttributes),
                ['tbody', 0]
            ]
        }
    })

    const CustomTableCell = TableCell.extend({
        addAttributes() {
            return {
                ...this.parent?.(),
                class: {
                    default: null,
                    parseHTML: (el) => el.getAttribute('class') || null,
                    renderHTML: (attrs) => (attrs.class ? { class: attrs.class } : {})
                },
                dataUserStyle: {
                    default: null,
                    parseHTML: (el) => el.getAttribute('data-user-style'),
                    renderHTML: (attrs) =>
                        attrs.dataUserStyle
                            ? { 'data-user-style': attrs.dataUserStyle, style: attrs.dataUserStyle }
                            : {}
                }
            }
        }
    })

    return [
        CustomTable.configure({ resizable: false, allowTableNodeSelection: true }),
        TableRow,
        CustomTableCell,
        CustomTableHeader,
        TableFigure,
        Caption
    ]
}
