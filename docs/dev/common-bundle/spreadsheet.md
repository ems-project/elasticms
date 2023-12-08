In Twig you can set the spreadsheet options by generating a JSON

Two writer are supported:
- `xlsx`: Generate a Microsoft Excel file
- `csv`: Generate a CSV file

```twig
{% set config = {
    "filename": "custom-filename",
    "disposition": "attachment",
    "writer": "xlsx",
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
- More information of style (styleArray only): [refers to Phpspreadsheet Documentation](https://phpspreadsheet.readthedocs.io/en/latest/topics/recipes/#styles)
