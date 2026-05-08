import { Node, mergeAttributes } from '@tiptap/core'
import { Table, TableCell, TableRow } from '@tiptap/extension-table'
import IconTable from '@tabler/icons/outline/table.svg?raw'
import { TiptapModule } from '../types.ts'
import { tableCleanHtmlTransform } from '../table/cleanTransformer.ts'
import { Caption, tableCaptionHtmlTransform, TableFigure } from '../table/caption.ts'
import { contextMenu } from '../table/contextMenu.ts'
import { openTableDialog } from '../table/dialogTable.ts'
import { CustomTableHeader, tableTheadHtmlTransform } from '../table/header.ts'

export const tableModule: TiptapModule = {
    extensions: getExtensions(),
    htmlTransforms: [tableCaptionHtmlTransform, tableCleanHtmlTransform, tableTheadHtmlTransform],
    toolbarGroup: 'insert',
    toolbar: [
        {
            name: 'Table',
            icon: IconTable,
            tooltip: 'Insert Table',
            command: (e) => openTableDialog(e, 'insert'),
            isActive: (e) => e.tiptap.isActive('table') || e.tiptap.isActive('tableFigure')
        }
    ],
    contextMenuNode: 'table',
    contextMenu: contextMenu
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
