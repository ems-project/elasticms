# Tiptap Rich Text Editor

This document describes our custom Tiptap-based WYSIWYG editor, its modules, toolbar system, and the
HTML transforms we built to work around Tiptap's limitations.

## Architecture Overview

The editor is built around three core pieces:

- **TiptapEditor** — the main class that wires everything together: initializes the Tiptap instance,
  resolves which modules to load based on the wysiwyg profile, manages the toolbar and context menu,
  and handles HTML transforms on input/output.
- **Toolbar** — renders toolbar buttons grouped by category and keeps their active state in sync
  with the editor selection.
- **ContextMenu** — a right-click menu that shows context-sensitive actions (e.g. table operations
  when clicking inside a table).

Every feature is encapsulated in a **module**. A module can provide extensions, toolbar buttons,
context menu items, and HTML transforms. The editor collects all modules, filters them based on the
active profile, and registers only the relevant extensions and UI elements.

## Modules & Features

### Basic Styles

Bold, italic, and strikethrough are always available. Subscript and superscript are opt-in and only
load when the profile includes the `basicstyles` extra plugin.

### History (Undo / Redo)

Provides undo and redo buttons backed by Tiptap's built-in `UndoRedo` extension.

### Lists

Supports ordered (numbered) and unordered (bulleted) lists. Each list type registers its own
extension and toolbar button.

### Indentation

Handles indentation for paragraphs and headings via a custom `indent` global attribute that renders
as `margin-left`. When the cursor is inside a list, the indent/outdent buttons sink or lift list
items instead.

### Text Alignment

Left, center, right, and justify alignment using Tiptap's `TextAlign` extension configured for
headings and paragraphs. The "left" button works by unsetting alignment (since left is the default).

### Styles

The styles module provides a dropdown for applying block, inline, and object styles defined in the
wysiwyg configuration. It bridges CKEditor-style definitions to Tiptap by mapping them onto headings,
paragraphs, divs, inline marks, and object nodes like tables and lists.

#### Element Classification

Styles are categorized into three groups based on their target element:

- **Block styles** — target `p`, `h1`–`h6`, `div`, `pre`, `address`, or `blockquote`. Applied by
  switching the node type and/or setting `htmlClass` and `htmlStyle` attributes.
- **Inline styles** — target elements like `span`, `small`, `code`, `kbd`, `del`, `ins`, etc.
  Each inline element is registered as a separate Tiptap mark (`inlineStyle_span`,
  `inlineStyle_code`, …) with `style` and `class` attributes. Applied via `toggleMark`.
- **Object styles** — target `table`, `ul`, `ol`, `td`, `th`, or `a`. These operate on the
  nearest matching ancestor node or link mark, toggling a `dataUserStyle` attribute or CSS class.

#### Extensions

The module registers several extensions:

- **Heading** — Tiptap's built-in heading extension.
- **Div** — a custom block node for `<div>` elements with inline content.
- **Inline style marks** — one mark per inline element, supporting `style` and `class` attributes.
- **styleAttributes** — a global attribute extension that adds `htmlStyle` and `htmlClass` to
  headings, paragraphs, and divs. Also includes two ProseMirror plugins:
  - *trailingParagraph* — ensures the document always ends with a paragraph node.
  - *clearStyleOnSplit* — resets block attributes and inline style marks when splitting a block
    with Enter, so new paragraphs start clean.

#### Dropdown Behavior

The toolbar renders a "Styles" dropdown button. The dropdown panel is lazily initialized on first
open and rendered inside an iframe to isolate the content CSS from the toolbar. The panel groups
styles by category and only shows object styles when the cursor is inside a matching element (e.g.
table styles only appear when editing a table).

The button label updates on every selection change and transaction to reflect the currently active
styles. When a style is already active, clicking it again removes it (toggling behavior).

#### Style Resolution

Block style detection uses two lookup maps — `NODE_TO_ELEMENT` and `ELEMENT_TO_APPLIED` — to
translate between Tiptap node names and HTML elements. This avoids deeply nested conditionals and
makes it easy to add new block types. Object style detection walks up the node tree to find the
nearest matching ancestor using `OBJECT_NODE_MAP`.

### Insert

Two separate sub-modules:

- **Horizontal Rule** — inserts an `<hr>` element.
- **Blockquote** — toggles a block quote with active state tracking.

### Cleanup

A single "Remove Format" button that strips all marks from the selection.

### Tables

The most complex module. It provides a full table editing experience with insert/edit dialogs, cell
property dialogs, a context menu with row/column/cell operations, and several HTML transforms. See
the dedicated section below for details.

## Table Support in Detail

### Custom Extensions

We extend several default Tiptap table nodes to support extra attributes:

- **CustomTable** — adds `class`, `id`, `summary`, `align`, `border`, `cellpadding`, `cellspacing`,
  `width`, `height`, and a `dataUserStyle` attribute that preserves inline styles through editing.
- **CustomTableCell** — adds the same `dataUserStyle` attribute to cells.
- **CustomTableHeader** — extends the default `TableHeader` with a `scope` attribute (`col`, `row`,
  or `null`) for accessibility.
- **TableFigure** and **TableCaption** — two custom nodes for wrapping tables with captions (see
  below).

### Table Dialog

The table properties dialog allows you to configure headers, dimensions, alignment, border, padding,
spacing, CSS class, ID, inline style, caption, and summary — both when inserting a new table and
when editing an existing one.

### Cell Dialog

The cell properties dialog lets you change cell type (data vs header), column/row span, width,
height, word wrap, and horizontal/vertical alignment.

### Context Menu

Right-clicking inside a table opens a context menu with grouped sub-menus:

- **Cell** — insert cell before/after, clear cells, merge, split, cell properties.
- **Row** — insert row before/after, delete rows.
- **Column** — insert column before/after, delete columns.
- **Table-level** — delete table, table properties.

All cell/row/column actions are disabled when the cursor is inside a caption.

## HTML Transforms

Tiptap's internal document model does not natively support `<caption>`, `<thead>`/`<tbody>`, or
`<figure>` wrappers around tables. We solve this with **HTML transforms** — functions that run on
the HTML string both when content enters the editor and when it leaves.

Each transform has a `toEditor` and a `toOutput` method. They operate on a parsed DOM so we can
restructure elements before Tiptap sees them (and restore the original structure on output).

### Table Caption Transform

**Problem:** Tiptap has no concept of a `<caption>` element inside a `<table>`. Any `<caption>` in
the source HTML would be dropped.

**Solution:** On input (`toEditor`), we find every `<table>` that contains a `<caption>`, wrap the
table in a `<figure data-type="table">`, convert the `<caption>` into a `<figcaption>`, and append
it after the table inside the figure. Two custom Tiptap nodes — `TableFigure` and `TableCaption` —
handle this structure in the editor.

On output (`toOutput`), the transform reverses the process: it unwraps the figure, converts the
`<figcaption>` back into a `<caption>`, and places it inside the table. The result is clean,
standard HTML.

### Table Header / Thead Transform

**Problem:** Tiptap flattens all table rows into a single level. It does not distinguish between
`<thead>`, `<tbody>`, and `<tfoot>` sections. If the source HTML contains these wrappers, rows may
be duplicated or lost.

**Solution:** On input, the transform moves all rows from `<thead>`, `<tbody>`, and `<tfoot>`
directly under the `<table>` element, then removes the now-empty section wrappers. Tiptap sees a
flat list of `<tr>` elements and parses them normally.

On output, the transform inspects the first row: if all its cells are `<th>` elements, that row goes
into a new `<thead>` and the rest go into a `<tbody>`. If there are no header cells, everything goes
into a single `<tbody>`. This restores semantically correct HTML.

### Table Cleanup Transform

**Problem:** Various table attributes and styles need special handling to survive the round-trip
through Tiptap without loss or duplication.

**Solution:** On input, `colgroup` elements are removed (Tiptap doesn't use them), redundant
`colspan="1"` and `rowspan="1"` attributes are stripped, and inline `style` attributes on tables and
cells are moved to a `data-user-style` attribute so Tiptap doesn't interfere with them.

On output, the `data-user-style` values are merged back into the `style` attribute, `width` and
`height` table attributes are folded into the style string, `colgroup` leftovers are cleaned up
again, and `min-width` values injected by the table editing UI are stripped from cell styles.

### Trailing Paragraph Transform

The styles module registers a lightweight output transform that removes empty trailing `<p>` elements
from the output HTML. This cleans up the extra paragraph that the `trailingParagraph` ProseMirror
plugin inserts to keep the editor usable.

### Transform Pipeline

All transforms run in sequence through a shared pipeline in `TiptapEditor.transformHtml()`. The
method parses the HTML into a DOM, calls every registered transform in order, and serializes the
result back to an HTML string. This keeps the transform logic decoupled from the editor core —
modules simply declare their transforms and the editor handles the rest.

## Profile-Based Configuration

The editor adapts to different use cases through a `WysiwygProfile`. The profile controls:

- **Toolbar groups** — which groups of buttons appear, and in what order.
- **Removed buttons** — individual buttons that should be hidden.
- **Extra plugins** — opt-in features like subscript/superscript.
- **Default table class** — a CSS class automatically applied to new tables.

Modules can implement an `isEnabled` check that reads the profile to decide whether they should be
loaded at all.