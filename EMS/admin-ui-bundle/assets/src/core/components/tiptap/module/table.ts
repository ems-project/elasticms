import { Node, mergeAttributes } from '@tiptap/core'
import { Table, TableCell, TableHeader, TableRow } from '@tiptap/extension-table'
import IconTable from '@tabler/icons/outline/table.svg?raw'
import { HtmlTransform, MenuItem, TiptapModule } from '../types.ts'
import { Dialog } from '../../dialog.ts'

export const tableModule: TiptapModule = {
    extensions: getExtensions(),
    htmlTransforms: getHtmlTransforms(),
    group: 'insert',
    toolbar: [
        {
            name: 'Table',
            icon: IconTable,
            tooltip: 'Insert Table',
            command: (e) => openTableDialog(e, 'insert'),
            isActive: (e) => e.tiptap.isActive('table') || e.tiptap.isActive('tableFigure')
        }
    ],
    menu: getContextMenuItems()
}

function getExtensions(): Node[] {
    const CustomTable = Table.extend({
        addAttributes() {
            return {
                ...this.parent?.(),
                class: {
                    default: null,
                    parseHTML: (el) => el.getAttribute('class'),
                    renderHTML: (attrs) => (attrs.class ? { class: attrs.class } : {})
                },
                id: {
                    default: null,
                    parseHTML: (el) => el.getAttribute('id'),
                    renderHTML: (attrs) => (attrs.id ? { id: attrs.id } : {})
                },
                summary: {
                    default: null,
                    parseHTML: (el) => el.getAttribute('summary'),
                    renderHTML: (attrs) => (attrs.summary ? { summary: attrs.summary } : {})
                },
                dataUserStyle: {
                    default: null,
                    parseHTML: (el) => el.getAttribute('data-user-style'),
                    renderHTML: (attrs) =>
                        attrs.dataUserStyle
                            ? { 'data-user-style': attrs.dataUserStyle, style: attrs.dataUserStyle }
                            : {}
                },
                align: {
                    default: null,
                    parseHTML: (el) => el.getAttribute('align'),
                    renderHTML: (attrs) => (attrs.align ? { align: attrs.align } : {})
                }
            }
        }
    })
    const TableFigure = Node.create({
        name: 'tableFigure',
        group: 'block',
        content: 'tableCaption? table',
        isolating: true,

        parseHTML() {
            return [{ tag: 'figure[data-type="table"]' }]
        },

        renderHTML({ HTMLAttributes }) {
            return ['figure', mergeAttributes(HTMLAttributes, { 'data-type': 'table' }), 0]
        }
    })
    const TableCaption = Node.create({
        name: 'tableCaption',
        content: 'inline*',
        defining: true,

        parseHTML() {
            return [{ tag: 'figcaption' }]
        },

        renderHTML({ HTMLAttributes }) {
            return ['figcaption', mergeAttributes(HTMLAttributes), 0]
        },

        addKeyboardShortcuts() {
            return {
                Backspace: () => {
                    const { $from, empty } = this.editor.state.selection
                    if (!empty) return false
                    if ($from.parent.type.name !== this.name) return false
                    if ($from.parent.content.size > 0) return false

                    return this.editor
                        .chain()
                        .deleteRange({ from: $from.before(), to: $from.after() })
                        .focus()
                        .run()
                }
            }
        }
    })

    return [
        CustomTable.configure({ resizable: false, allowTableNodeSelection: true }),
        TableRow,
        TableCell,
        TableHeader,
        TableFigure,
        TableCaption
    ]
}

function getHtmlTransforms(): HtmlTransform[] {
    return [
        {
            name: 'tableCaption',
            toEditor(doc) {
                doc.querySelectorAll('table').forEach((table) => {
                    const caption = table.querySelector(':scope > caption')
                    if (!caption) return
                    const figure = doc.createElement('figure')
                    figure.setAttribute('data-type', 'table')
                    const figcaption = doc.createElement('figcaption')
                    figcaption.innerHTML = caption.innerHTML
                    caption.remove()
                    table.replaceWith(figure)
                    figure.appendChild(figcaption)
                    figure.appendChild(table)
                })
            },
            toOutput(doc) {
                doc.querySelectorAll('figure[data-type="table"]').forEach((fig) => {
                    const figcaption = fig.querySelector(':scope > figcaption')
                    const table = fig.querySelector(':scope > table')
                    if (!table) {
                        fig.remove()
                        return
                    }
                    if (figcaption) {
                        const caption = doc.createElement('caption')
                        caption.innerHTML = figcaption.innerHTML
                        table.insertBefore(caption, table.firstChild)
                    }
                    fig.replaceWith(table)
                })
            }
        },
        {
            name: 'tableCleanup',
            toEditor(doc) {
                doc.querySelectorAll('table').forEach((table) => {
                    const style = table.getAttribute('style')
                    if (style && !table.getAttribute('data-user-style')) {
                        table.setAttribute('data-user-style', style)
                        table.removeAttribute('style')
                    }
                })
            },
            toOutput(doc) {
                doc.querySelectorAll('table').forEach((table) => {
                    table.removeAttribute('style')
                    const userStyle = table.getAttribute('data-user-style')
                    if (userStyle) {
                        table.setAttribute('style', userStyle)
                        table.removeAttribute('data-user-style')
                    }
                    table.querySelector(':scope > colgroup')?.remove()
                    table.querySelectorAll('td, th').forEach((cell) => {
                        const style = cell.getAttribute('style')
                        if (style) {
                            const cleaned = style.replace(/min-width\s*:[^;]+;?/gi, '').trim()
                            if (cleaned) cell.setAttribute('style', cleaned)
                            else cell.removeAttribute('style')
                        }
                        if (cell.getAttribute('colwidth')) cell.removeAttribute('colwidth')
                        if (cell.getAttribute('colspan') === '1') cell.removeAttribute('colspan')
                        if (cell.getAttribute('rowspan') === '1') cell.removeAttribute('rowspan')
                    })
                })
            }
        }
    ]
}

function getContextMenuItems(): MenuItem[] {
    return [
        {
            context: ['table'],
            label: 'Insert row above',
            parent: 'Row',
            order: 0,
            command: (e) => e.tiptap.chain().focus().addRowBefore().run()
        },
        {
            context: ['table'],
            label: 'Insert row below',
            parent: 'Row',
            order: 1,
            command: (e) => e.tiptap.chain().focus().addRowAfter().run()
        },
        {
            context: ['table'],
            label: 'Delete row',
            parent: 'Row',
            order: 2,
            command: (e) => e.tiptap.chain().focus().deleteRow().run()
        },
        {
            context: ['table'],
            label: 'Table properties',
            order: 98,
            command: (e) => openTableDialog(e, 'edit')
        },
        {
            context: ['table'],
            label: 'Delete table',
            order: 99,
            command: (e) => commandDeleteTable(e)
        }
    ]
}

function commandDeleteTable(e: { tiptap: any }) {
    const { $from } = e.tiptap.state.selection
    for (let d = $from.depth; d > 0; d--) {
        if ($from.node(d).type.name === 'tableFigure') {
            e.tiptap
                .chain()
                .focus()
                .deleteRange({ from: $from.before(d), to: $from.after(d) })
                .run()
            return
        }
    }
    e.tiptap.chain().focus().deleteTable().run()
}

function getTableContext(tiptap: any): { attrs: Record<string, any>; caption: string } {
    const { $from } = tiptap.state.selection
    let attrs: Record<string, any> = {}
    let caption = ''

    for (let d = $from.depth; d > 0; d--) {
        const node = $from.node(d)
        if (node.type.name === 'table') {
            attrs = { ...node.attrs }
        }
        if (node.type.name === 'tableFigure') {
            const first = node.firstChild
            if (first?.type.name === 'tableCaption') {
                caption = first.textContent
            }
            break
        }
    }

    return { attrs, caption }
}

function updateCaption(tiptap: any, caption: string) {
    const { $from } = tiptap.state.selection

    let figurePos: number | null = null
    let figureNode = null
    let tablePos: number | null = null

    for (let d = $from.depth; d > 0; d--) {
        const node = $from.node(d)
        if (node.type.name === 'tableFigure') {
            figurePos = $from.before(d)
            figureNode = node
            break
        }
        if (node.type.name === 'table') {
            tablePos = $from.before(d)
        }
    }

    if (figureNode && figurePos !== null) {
        const firstChild = figureNode.firstChild
        if (caption) {
            if (firstChild?.type.name === 'tableCaption') {
                const from = figurePos + 1
                const to = from + firstChild.nodeSize
                tiptap
                    .chain()
                    .focus()
                    .deleteRange({ from, to })
                    .insertContentAt(from, {
                        type: 'tableCaption',
                        content: [{ type: 'text', text: caption }]
                    })
                    .run()
            } else {
                tiptap
                    .chain()
                    .focus()
                    .insertContentAt(figurePos + 1, {
                        type: 'tableCaption',
                        content: [{ type: 'text', text: caption }]
                    })
                    .run()
            }
        } else if (firstChild?.type.name === 'tableCaption') {
            const from = figurePos + 1
            const to = from + firstChild.nodeSize
            tiptap.chain().focus().deleteRange({ from, to }).run()
        }
    } else if (caption && tablePos !== null) {
        const tableNode = tiptap.state.doc.nodeAt(tablePos)
        if (!tableNode) return
        const tableEnd = tablePos + tableNode.nodeSize
        tiptap
            .chain()
            .focus()
            .deleteRange({ from: tablePos, to: tableEnd })
            .insertContentAt(tablePos, {
                type: 'tableFigure',
                content: [
                    { type: 'tableCaption', content: [{ type: 'text', text: caption }] },
                    tableNode.toJSON()
                ]
            })
            .run()
    }
}

function openTableDialog(e: { tiptap: any }, mode: 'insert' | 'edit') {
    const dialog = new Dialog('Table Properties', { draggable: true })

    const current = mode === 'edit' ? getTableContext(e.tiptap) : { attrs: {}, caption: '' }
    const a = current.attrs

    const esc = (v: any) => (v ?? '').toString().replace(/"/g, '&quot;')

    dialog.setContent(`
        ${
            mode === 'insert'
                ? `
        <div style="display: flex; gap: 15px;">
            <div style="flex: 1; margin-bottom: 15px;">
                <label for="table-cols">Columns</label>
                <input type="number" id="table-cols" value="2" min="1" max="10">
            </div>
            <div style="flex: 1; margin-bottom: 15px;">
                <label for="table-rows">Rows</label>
                <input type="number" id="table-rows" value="3" min="1" max="20">
            </div>
        </div>`
                : ''
        }
        <div style="margin-bottom: 15px;">
            <label for="table-caption">Caption</label>
            <input type="text" id="table-caption" value="${esc(current.caption)}" placeholder="Optional">
        </div>
        <div style="margin-bottom: 15px;">
            <label for="table-summary">Summary</label>
            <input type="text" id="table-summary" value="${esc(a.summary)}" placeholder="Optional">
        </div>
        <div style="margin-bottom: 15px;">
            <label for="table-style">Style</label>
            <input type="text" id="table-style" value="${esc(a.dataUserStyle)}" placeholder="Optional">
        </div>
        <div style="margin-bottom: 15px;">
            <label for="table-align">Align</label>
            <select id="table-align">
                <option value=""${!a.align ? ' selected' : ''}>Not defined</option>
                <option value="left"${a.align === 'left' ? ' selected' : ''}>Left</option>
                <option value="center"${a.align === 'center' ? ' selected' : ''}>Center</option>
                <option value="right"${a.align === 'right' ? ' selected' : ''}>Right</option>
            </select>
        </div>
        <div style="display: flex; gap: 15px;">
            <div style="flex: 1; margin-bottom: 15px;">
                <label for="table-id">ID</label>
                <input type="text" id="table-id" value="${esc(a.id)}" placeholder="Optional">
            </div>
            <div style="flex: 1; margin-bottom: 15px;">
                <label for="table-class">Class</label>
                <input type="text" id="table-class" value="${esc(a.class)}" placeholder="Optional">
            </div>
        </div>
    `)

    dialog.addButton({
        label: 'Apply',
        variant: 'primary',
        onClick: (d) => {
            const caption = (d.getFieldValue('table-caption') || '').trim()
            const tableId = (d.getFieldValue('table-id') || '').trim()
            const tableClass = (d.getFieldValue('table-class') || '').trim()
            const tableSummary = (d.getFieldValue('table-summary') || '').trim()
            const tableStyle = (d.getFieldValue('table-style') || '').trim()
            const tableAlign = (d.getFieldValue('table-align') || '').trim()

            const attrs: Record<string, string | null> = {
                id: tableId || null,
                class: tableClass || null,
                summary: tableSummary || null,
                dataUserStyle: tableStyle || null,
                align: tableAlign || null
            }

            if (mode === 'edit') {
                e.tiptap.chain().focus().updateAttributes('table', attrs).run()
                updateCaption(e.tiptap, caption)
            } else {
                const rows = parseInt(d.getFieldValue('table-rows')) || 3
                const cols = parseInt(d.getFieldValue('table-cols')) || 2

                const tableRows = Array.from({ length: rows }, () => ({
                    type: 'tableRow',
                    content: Array.from({ length: cols }, () => ({
                        type: 'tableCell',
                        content: [{ type: 'paragraph' }]
                    }))
                }))

                if (caption) {
                    e.tiptap
                        .chain()
                        .focus()
                        .insertContent({
                            type: 'tableFigure',
                            content: [
                                {
                                    type: 'tableCaption',
                                    content: [{ type: 'text', text: caption }]
                                },
                                { type: 'table', attrs, content: tableRows }
                            ]
                        })
                        .run()
                } else {
                    e.tiptap
                        .chain()
                        .focus()
                        .insertContent({ type: 'table', attrs, content: tableRows })
                        .run()
                }
            }
            d.close()
        }
    })

    dialog.addButton({
        label: 'Cancel',
        variant: 'secondary',
        onClick: (d) => d.close()
    })

    dialog.open()
}
