# PropertyAccessor

The `PropertyAccessor` is a singleton utility for reading, writing, and iterating over deeply nested
array structures using a bracket-notation path syntax. It supports encoding/decoding operators,
wildcard iteration, and recursive traversal by ID.

## Path Syntax

Paths use bracket notation to navigate nested arrays:

```
[field1][field2][field3]
```

Each bracket segment maps to a key in the array. For example, `[settings][theme][color]` accesses
`$array['settings']['theme']['color']`.

### Operators

Operators are prefixed to the field name, separated by colons. Multiple operators can be chained and
are applied in order during decoding and in reverse order during encoding.

| Operator | Description                                                |
| -------- | ---------------------------------------------------------- |
| `json`   | JSON encodes/decodes the value                             |
| `base64` | Base64 encodes/decodes the value                           |
| `id_key` | Re-indexes an array using each item's `id` property as key |

Example: `[json:id_key:structure]` first JSON-decodes the `structure` field, then re-indexes the
resulting array by `id`.

### Wildcards

| Wildcard | Description                         |
| -------- | ----------------------------------- |
| `*`      | Iterates over all direct children   |
| `**`     | Recursively traverses nested arrays |

### Recursive Traversal (`**`)

The `**` wildcard performs a depth-first search through all nested arrays. When combined with the
`id_key` operator, it generates "beautiful paths" that include the matched item's `id` instead of
numeric indices.

#### In `getValue` and `setValue`

When used in `getValue` or `setValue`, the segment immediately after `**` is treated as a target ID.
The accessor searches the tree for an item whose `id` property matches, then continues resolving the
remaining path from that item.

```php
$accessor = PropertyAccessor::createPropertyAccessor();

$data = [
    'structure' => Json::encode([
        ['id' => 'root', 'children' => [
            ['id' => 'node-1', 'config' => ['enabled' => true]]
        ]]
    ])
];

// Read a deeply nested value by ID
$value = $accessor->getValue($data, '[json:id_key:structure][**][node-1][config][enabled]');
// Returns: true

// Write a deeply nested value by ID
$accessor->setValue($data, '[json:id_key:structure][**][node-1][config][enabled]', false);
```

If no item with the given ID is found, `getValue` returns `null` and `setValue` is a no-op.

#### In `iterator`

When iterating with `**`, all nested arrays are yielded. Combined with `id_key`, the generated paths
use the item's `id` instead of numeric keys, producing stable, human-readable paths.

```php
$fieldPath = '[json:id_key:structure][**][title_%locale%]';
$replacers = ['%locale%' => 'nl'];

foreach ($accessor->iterator($fieldPath, $data, $replacers) as $path => $value) {
    // $path: [json:id_key:structure][**][node-1][content][title_%locale%]
    // $value: the matched value
}
```

### Replacers

The `iterator` method accepts a `$replacers` array that performs string substitution on field names.
This is useful for locale-aware paths:

```php
$replacers = ['%locale%' => 'fr'];
// Path [title_%locale%] resolves to field title_fr in the data
// but the yielded key retains the placeholder [title_%locale%]
```

### Pipe-separated Fields

A single path segment can match multiple fields using the pipe character:

```
[title_nl|title_fr|title_de]
```

This iterates over all matching fields in a single pass.
