# Tiptap Rich Text Editor

A custom Tiptap-based WYSIWYG editor with a modular architecture. Each feature is a self-contained module that can
provide toolbar buttons, context menu items, and HTML transforms. The editor loads modules based on the active wysiwyg
profile.

The editor UI is fully translated into English, Dutch, French, and German.

## Modules

### Basic Styles

Bold, italic, and strikethrough are always available. Subscript and superscript are opt-in via the
`basicstyles` extra plugin.

### Cleanup

A "Remove Format" button that strips all formatting from the selection. Resets styled blocks back to paragraphs.
Preserves anchors and links. Disabled when the selection has no removable formatting.

### Colors

Two toolbar buttons: **Text Color** and **Background Color**.

Each button opens a dropdown with:

- An **Automatic** option that removes the active color.
- A grid of **predefined colors**. Configurable via `colorButton_colors` in the wysiwyg profile — either a
  comma-separated string of hex values (with or without `#`) or an array of hex strings. Falls back to a built-in
  palette of 24 colors when not configured.
- A grid of **active colors** — colors currently used in the document combined with colors previously selected in this
  session. Hidden when empty.
- A **More colors** button that opens a color picker dialog with a web-safe palette and a native color input.

Color names are fully translated into English, Dutch, French and German for all predefined colors.

### Div Container

A toolbar button and context menu for wrapping content in `<div>` elements. Opt-in via the `div`
extra plugin.

The toolbar button always creates a new div around the current block, allowing divs to be nested.

Right-clicking inside a div provides two context menu actions:

- **Edit Div** — opens a dialog to modify the div's attributes.
- **Remove Div** — unwraps the div while preserving its content.

The dialog covers a style preset dropdown (populated from wysiwyg styles targeting `div`), CSS classes, ID, language
code, inline style, and advisory title. Selecting a preset fills the classes field; the field remains manually editable.

### Find & Replace

Two toolbar buttons — Find and Replace — that open the same dialog on a different tab.

**Find tab** — a search field with a Find button. Results are highlighted in the editor as the user navigates through
them. Each press of the button advances to the next match. When the last match is reached, the search wraps back to the
first (if wrap around is enabled). The status line below the options shows the current position within the total number
of matches (e.g. `2 / 5`). Pressing Enter in the search field is equivalent to clicking Find.

**Replace tab** — two fields: one for the search term and one for the replacement text. The Replace button replaces the
current match and automatically advances to the next one. If no match is active yet, the first press acts as a find.
Replace All replaces every occurrence in one operation and reports how many substitutions were made.

Both tabs share the same three search options:

- **Case-sensitive** — when enabled, `Hello` and `hello` are treated as different words. Disabled by default.
- **Whole word** — when enabled, only matches where the search term appears as a complete word are found. Searching for
  `cat` will not match `concatenate`. Disabled by default.
- **Wrap around** — when enabled, reaching the last match continues from the top of the document. When disabled, the
  search stops at the end and shows a notice. Enabled by default.

Switching between the Find and Replace tabs carries the current search term over so the user does not have to retype it.
The two tabs are otherwise independent — each has its own match state.

All highlights are cleared when the dialog is closed. Editing the document while the dialog is open also clears the
highlights, since positions in the document may have shifted.

### Format

A toolbar dropdown for switching block-level formats, equivalent to CKEditor's Format plugin. Works at the block level —
no text selection needed. The dropdown button shows the name of the active format.

Available formats are configured via `formatTags` (default: `p;h1;h2;h3;pre`). Each tag has a label shown in the
dropdown: Normal, Heading 1–6, Formatted, Address, Normal (DIV).

The dropdown renders inside an iframe so format previews use the editor's content CSS.

### History

Undo and redo buttons. Greyed out when there is nothing to undo or redo.

### Indentation

Indent and outdent for paragraphs, headings, and div containers. Inside a list, the buttons sink or lift list items
instead. When content is nested (e.g. a paragraph inside a div), indentation is applied from the outermost indentable
element inward — indent the div first, then the paragraph inside it.

### Insert

- **Horizontal Rule** — inserts an `<hr>`.
- **Blockquote** — toggles a block quote.
- **Special Characters** — opens a character picker dialog. Hovering shows a preview, clicking inserts the character.

### Iframe

A toolbar button that lets the user embed external content (e.g. a YouTube video) by pasting an
`<iframe>` embed code into a dialog. Opt-in via the `iframe` extra plugin.

The dialog contains a textarea where the user pastes the embed snippet. On confirm, the pasted HTML is parsed and the
`src`, `width`, `height`, `allow`, `title`, `frameborder`, `allowfullscreen`, and
`referrerpolicy` attributes are extracted and stored on the node. Invalid input (no `<iframe>` with a
`src`) shows an inline error instead of closing the dialog.

The iframe is an inline node, so it can sit inside a paragraph and inherits the paragraph's text alignment (left,
center, right). In the editor it is rendered as a non-interactive placeholder — sized to match the configured
width/height — instead of a live embed, both to avoid loading external content while editing and to keep the node
properly selectable. The real `<iframe>` is only output in the saved HTML.

Right-clicking an existing iframe placeholder reopens the same dialog, pre-filled, to edit it.

The placeholder participates in the editor's normal node selection (click to select) and is also highlighted when
included in a wider text selection (e.g. select-all), and is labeled when Show Blocks is active.

### Images

Insert images either by dragging and dropping files onto the editor, or through the toolbar's Image button.

**Drag & drop** — drop one or more image files anywhere in the editor to upload and insert them. Each image shows a
loading placeholder while it uploads, so you always know an upload is in progress, and images only appear once they're
fully uploaded. If an upload fails, the placeholder disappears and a brief notice explains that the upload didn't
succeed.

**Toolbar button** — click "Image" to insert a new one. This opens a dialog where you can type or paste an image URL
directly, or click "Browse server" to pick an image you've already uploaded before, browsable by folder with thumbnails.
Either way, as soon as the image is set, its actual width and height are filled in automatically, so you always start
from its real size. You can also set alternative text (for accessibility), adjust the width and height, choose an
alignment, and choose whether text should wrap around the image.

A lock icon next to the dimensions keeps width and height proportional when you change one of them — click it to unlock
and resize freely. A reset icon next to it restores the original width and height at any time. You don't have to open
the dialog to resize, either: click an image to select it and a small handle appears in each bottom corner — drag either
one to resize on the fly, right in the page, while keeping its proportions.

**Alignment and text wrap** — an image can be aligned left, center, or right on its own line, or set to wrap left or
right so surrounding paragraph text flows naturally around it. Both are set from the same dialog, right next to each
other.

To edit an existing image, right-click or double-click it. This reopens the same dialog, pre-filled with its current
settings — including its current alignment or wrap choice — and includes a "Remove image" option. Right-clicking also
offers a "Remove image" option directly in the menu, without needing to open the dialog. Selecting an image and pressing
Delete or Backspace removes it the same way.

Selected images are highlighted the same way as other special content blocks (like embeds), so it's always clear what
you're about to edit or delete.

**Captions** — the same dialog lets you add a caption, a short line of text shown right under the image. The caption
field accepts multiple lines, so longer captions can wrap onto a second line exactly where you want them to. Just fill
in the caption field when inserting or editing an image. Leave it empty and no caption is shown; fill it in later and
one appears automatically. Clear it again and the image goes back to being a plain image with no extra space around it,
and removing the image removes its caption along with it.

### Links

Three buttons: Link, Unlink, and Anchor.

**Link** opens a dialog supporting six link types: URL (with optional target), Anchor (link to a named anchor in the
document), E-mail (with optional subject/body), Phone, Internal (link to another content item within the system), and
File (link to an uploaded file). The dialog detects the current link type and pre-fills accordingly. Available types can
be restricted via
`ems.urlTypes`. Default `_blank` target for new links can be set per type via
`ems.urlTargetDefaultBlank`.

**Anchor** creates a named bookmark (`<a id="…">`) that can be linked to. Works on selected text or inserts an invisible
bookmark when nothing is selected. Right-click provides edit and remove actions.

**Internal** links point to another content item rather than a URL. A search field looks up content by title as the user
types, showing a paginated list of matches. When multiple content types are linkable, a type dropdown filters the search
and narrows results accordingly. Selecting a result shows it as a compact chip in place of the search field, with an
option to clear the selection and search again. Internal links store a stable reference to the target content rather
than a plain URL.

**File** links point to an uploaded file (PDF, document, etc.) rather than a typed URL. You can create one two ways:

- **Drag & drop** — drop any non-image file anywhere in the editor. It uploads automatically and a link to the file,
  labeled with its filename, is inserted at the drop position. A loading placeholder shows while the upload is in
  progress.
- **Toolbar dialog** — choose the File type in the Link dialog and click "Browse..." to pick a file from your device.
  Once uploaded, the link text field is filled in with the file's name, which you can edit freely before applying — this
  text becomes the visible content of the link.

Both routes show a brief notice on success or failure of the upload.

**Unlink** removes the link from the selection. Disabled when the cursor is not inside a link.

Anchors and links are preserved by the Remove Format action.

**Auto-linking** — URLs are automatically converted into links, whether typed (followed by a space or Enter) or pasted
as plain text. Recognizes `http://`, `https://`, and bare `www.` addresses. Pasting a URL over a text selection turns
that selection into a link instead of replacing it.

### Lists

Ordered (numbered) and unordered (bulleted) lists.

Right-clicking a bulleted list opens a properties dialog to configure the list marker type: circle, disc, or square.

Right-clicking a numbered list opens a properties dialog to configure the start number and list type: numbers (1, 2, 3),
lowercase letters (a, b, c), uppercase letters (A, B, C), lowercase Roman numerals (i, ii, iii), or uppercase Roman
numerals (I, II, III).

### Maximize

A toggle button that expands the editor to fill the entire viewport. The button icon and tooltip switch between
"Maximize" and "Minimize" depending on the current state. Only available in the WYSIWYG editor, not in the inline
editor.

### Show Blocks

A toggle button that visualizes block-level elements in the editor with dotted borders and tag name labels (p, h1, div,
pre, etc.). Also labels the iframe placeholder. Purely visual — does not affect the HTML output. Useful for inspecting
document structure. The button icon and tooltip switch between "Show Blocks" and "Hide Blocks" depending on the current
state. Opt-in via the `showblocks`
extra plugin.

### Source

A toggle button that switches between the visual editor and a raw HTML source view. When entering source mode, the
current HTML is formatted and shown in an Ace-based code editor (reusing the shared `CodeEditor` component), and all
toolbar buttons are disabled except Source and Maximize. When leaving source mode, the edited HTML is loaded back into
the editor. Only available in the WYSIWYG editor, not in the inline editor.

### Styles

A toolbar dropdown for applying styles defined in the wysiwyg configuration. Styles are split into three categories:

- **Block styles** — change the block type and optionally add a CSS class or inline style. Target elements: `p`, `h1`–
  `h6`, `div`, `pre`, `address`, `blockquote`.
- **Inline styles** — wrap selected text in an element with optional class/style. Target elements:
  `span`, `small`, `code`, `kbd`, `del`, `ins`, and others.
- **Object styles** — apply a class or style to the nearest matching ancestor. Target elements:
  `table`, `ul`, `ol`, `td`, `th`, `a`.

The dropdown groups styles by category and only shows object styles when the cursor is inside a matching element (e.g.
table styles only appear when editing a table). Clicking an active style removes it.

The dropdown button label reflects the currently active styles and updates on every selection change.

When pressing Enter on a styled block, the new paragraph starts clean — block styles and inline style marks are
automatically cleared.

### Tables

Full table editing: insert and edit tables via a properties dialog, edit cell properties, and a right-click context menu
with cell, row, column, and table-level operations.

The table dialog covers headers, dimensions, alignment, border, padding, spacing, class, ID, inline style, caption, and
summary. The cell dialog covers cell type, spans, dimensions, word wrap, and alignment.

### Text Alignment

Left, center, right, and justify. Left works by unsetting alignment since it is the default. Images share the same
left/center/right alignment options, available directly from the image dialog rather than the main toolbar.

## HTML Transforms

Tiptap's internal document model does not natively support `<caption>`, `<thead>`/`<tbody>`, or
`<figure>` wrappers around tables. We solve this with **HTML transforms** — functions that run on the HTML string both
when content enters the editor and when it leaves.

Each transform has a `toEditor` and a `toOutput` method. They operate on a parsed DOM so we can restructure elements
before Tiptap sees them (and restore the original structure on output).

### Table Caption Transform

**Problem:** Tiptap has no concept of a `<caption>` element inside a `<table>`. Any `<caption>` in the source HTML would
be dropped.

**Solution:** On input (`toEditor`), we find every `<table>` that contains a `<caption>`, wrap the table in a
`<figure data-type="table">`, convert the `<caption>` into a `<figcaption>`, and append it after the table inside the
figure. Two custom Tiptap nodes — `TableFigure` and `TableCaption` — handle this structure in the editor.

On output (`toOutput`), the transform reverses the process: it unwraps the figure, converts the
`<figcaption>` back into a `<caption>`, and places it inside the table. The result is clean, standard HTML.

### Table Header / Thead Transform

**Problem:** Tiptap flattens all table rows into a single level. It does not distinguish between
`<thead>`, `<tbody>`, and `<tfoot>` sections. If the source HTML contains these wrappers, rows may be duplicated or
lost.

**Solution:** On input, the transform moves all rows from `<thead>`, `<tbody>`, and `<tfoot>`
directly under the `<table>` element, then removes the now-empty section wrappers. Tiptap sees a flat list of `<tr>`
elements and parses them normally.

On output, the transform inspects the first row: if all its cells are `<th>` elements, that row goes into a new
`<thead>` and the rest go into a `<tbody>`. If there are no header cells, everything goes into a single `<tbody>`. This
restores semantically correct HTML.

### Table Cleanup Transform

**Problem:** Various table attributes and styles need special handling to survive the round-trip through Tiptap without
loss or duplication.

**Solution:** On input, `colgroup` elements are removed (Tiptap doesn't use them), redundant
`colspan="1"` and `rowspan="1"` attributes are stripped, and inline `style` attributes on tables and cells are moved to
a `data-user-style` attribute so Tiptap doesn't interfere with them.

On output, the `data-user-style` values are merged back into the `style` attribute, `width` and
`height` table attributes are folded into the style string, `colgroup` leftovers are cleaned up again, and `min-width`
values injected by the table editing UI are stripped from cell styles.

### Trailing Paragraph Transform

The styles module registers a lightweight output transform that removes empty trailing `<p>`
elements from the output HTML. This cleans up the extra paragraph that the editor inserts to keep the document editable.

### Transform Pipeline

All transforms run in sequence through a shared pipeline. The HTML is parsed into a DOM, every registered transform runs
in order, and the result is serialized back to an HTML string. Modules simply declare their transforms and the editor
handles the rest.

## Profile Configuration

The editor adapts to different use cases through a `WysiwygProfile`. The profile controls:

- **Toolbar groups** — which groups of buttons appear, and in what order.
- **Removed buttons** — individual buttons that should be hidden.
- **Extra plugins** — opt-in features like subscript/superscript and show blocks.
- **Format tags** — via `formatTags`, configures which block formats are available in the Format dropdown (default:
  `p;h1;h2;h3;pre`).
- **Default table class** — a CSS class automatically applied to new tables.
- **Allowed link types** — via `ems.urlTypes`, restricts which link types (url, anchor, email, phone) the link dialog
  offers.
- **Default `_blank` target** — via `ems.urlTargetDefaultBlank`, pre-selects `_blank` as the target for new links of the
  listed types.
- **Ajax paste endpoint** — via `ems.paste`, intercepts pasted HTML and sends it to the configured URL for sanitization
  and/or formatting before inserting it into the editor.
- **Translation overrides** — via `translations`, overrides specific UI translation keys per locale (e.g.
  `{ nl: { button_apply: 'Toepassen' } }`). Unknown keys are silently ignored; falls back to the built-in translation
  when no override is set.
- **Custom CSS** — content styling is applied by targeting the `.wysiwyg-content` class. This works consistently across
  the preview, the Tiptap iframe editor, and the Tiptap inline editor.
- **Media contentType** — via `ems.mediaContentType`, we can define the contentType that is used for media files,
  default is `media_file`. The contentType will not be available for internal links; you can create a link, but only
  with the file option, if a file browser dashboard is defined.

Modules can implement an `isEnabled` check that reads the profile to decide whether they should be loaded at all.

### Ajax Paste

When `ems.paste` is configured in the wysiwyg profile, pasted HTML is intercepted and sent to that endpoint before being
inserted into the editor. The endpoint sanitizes and/or pretty-prints the HTML content.

On paste, the raw clipboard HTML is posted as JSON (`{ content: string }`) to the configured URL. The endpoint is
expected to return JSON with a `content` field containing the cleaned HTML. The response is then parsed by ProseMirror
and inserted at the current selection.

If `ems.paste` is not set, paste behaviour is unchanged.

Auto-linking of URLs also applies to content returned by the paste endpoint, so pasted HTML that contains plain-text
URLs still gets converted into links after sanitization.