# Inline Editor

Since version 7.2, ElasticMS includes an inline editor that lets authenticated users edit content
directly inside channels. It must be enabled on the channel.

When enabled, an edit button is injected for authenticated users visiting the channel. Clicking it
loads the inline editor, which wraps an inline topbar and sidebar around the channel.

Inside the inline editor, users can click directly on the content they want to edit, then save or
discard their changes.

## Implementation

Enable inline editing with the `emsch_inline_edit` function, passing a config object.

- On the public frontend, this function simply outputs the content.
- Inside a channel with the inline editor enabled, it wraps the content in a `div` containing the
  attributes needed for inline editing to work.

### Required options

| Option     | Description                                                              |
| ---------- | ------------------------------------------------------------------------ |
| `document` | Instance of `DocumentInterface` (via `emsco_get`) or the route document. |
| `path`     | String path to the property being printed.                               |

### Optional options

| Option       | Description                                                                                                    |
| ------------ | -------------------------------------------------------------------------------------------------------------- |
| `attributes` | Array of additional HTML attributes.                                                                           |
| `element`    | Wrapping element tag. Defaults to `div`.                                                                       |
| `content`    | Allows passing pre-processed content, e.g. for applying `emsch_routing` or other replacements before wrapping. |

### Examples

Inline editable `h1`:

```twig
{{ emsch_inline_edit({
    element: 'h1',
    document: target,
    path: "[#{locale}][title]",
}) }}
```

Inline editable `div` with custom attributes:

```twig
{{ emsch_inline_edit({
    document: target,
    path: "[#{locale}][body]",
    content: target.source.(locale).body|default('')|emsch_routing,
    attributes: {
        class: 'col-12 col-lg-10 offset-lg-1 col-xl-8 offset-xl-2',
        'data-field-type': 'wysiwyg'
    }
}) }}
```
