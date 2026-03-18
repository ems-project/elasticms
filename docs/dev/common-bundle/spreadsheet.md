# Spreadsheets

In Twig you can set the spreadsheet options by generating a JSON

Two writer are supported:

- `xlsx`: Generate a Microsoft Excel file
- `csv`: Generate a CSV file

```twig
{% set config = {
    "filename": "custom-filename",
    "disposition": "attachment",
    "writer": "xlsx",
    "value_binder": "string",
    "sheets": [
        {
            "name": "Sheet 1",
            "rows": [
                ["A1", "A2"],
                ["B1", "B2"],
            ]
        },
        {
            "name": "Sheet 2",
            "rows": [
                ["A1", "A2"],
                ["B1", "B2"],
            ]
        },
    ]
} %}

{{- config|json_encode|raw -}}
```

## Value binders

The `value_binder` config allows following values:

- `null`: use DefaultValueBinder
- `string`: use StringValueBinder
- `advanced`: use AdvancedValueBinder

More info
about [value binders](https://phpspreadsheet.readthedocs.io/en/latest/topics/accessing-cells/#using-value-binders-to-facilitate-data-entry).

## Style cells

Different config for definition of Cell are available (config may be mixed up)

- `without style`: directly string value or an array { "data" : "stringValue" }

```twig
    "rows": [
        [
            "A1", 
            { "data" : "A2" }
        ],
        [
            { "data" : "B1" }, 
            "B2"
        ],
    ]
```

- `with style`: need an array { "data" : "stringValue", "style" : [] }

```twig
    "rows": [
        [   "A1", 
            { 
                "data" : "A2",
                "style": {
                  "fill": {
                    "fillType": "solid",
                    "color": {
                      "rgb": "F9D73F"
                    }
                  }
                }
            }
        ],
        [
            { 
                "data" : "B1", 
                "style": {
                  "fill": {
                    "fillType": "solid",
                    "color": {
                      "rgb": "F9D73F"
                    }
                  }
                } 
            }, 
            { 
                "data" : "B2", 
                "style" : {} 
            }
        ],
    ]

```

- More information of style (styleArray
  only): [refers to Phpspreadsheet Documentation](https://phpspreadsheet.readthedocs.io/en/latest/topics/recipes/#styles)

## Date cells

When dates are exported as strings, all cells in the column will initially
contain string values. However, if a user edits one of these cells in Excel,
that specific cell is automatically converted into a real date value, while the
others remain strings.
As a result, the column ends up containing mixed data types.
When reading the file back, the edited cell is interpreted as a numeric date (
Excel stores dates internally as integers) and will therefore be returned in the
m/d/Y format.

To avoid mixed data types, any value that represents a date should be stored as
a proper date type instead of a string.

Define:

- Type: date
- Format input: use by php for converting the data into a \DateTime object
- Format display: use by formatting the object in excel

```json
{
  "rows": [
    [
      {
        "data": "23/08/2025",
        "type": "date",
        "format_input": "d/m/Y",
        "format_display": "dd/mm/yyyy"
      }
    ]
  ]
}
```
## String cells

Force text mode on a cell that contains only numbers

Define:

- Type: s

```json
{
    "rows": [
        [
            {
                "data": "12345",
                "type": "s"
            }
        ]
    ]
}
```