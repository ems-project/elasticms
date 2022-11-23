# Documents

## Update

```bash
php bin\console emscli:documents:update --help
Description:
  Update documents form excel or csv with custom configuration

Usage:
  emscli:documents:update [options] [--] <config-file-path> <data-file-path>

Arguments:
  config-file                        Config file
  data-file                          Data file

Options:
      --data-offset=DATA-OFFSET            Offset data [default: 0]
      --data-length=DATA-LENGTH            Length data to parse
      --data-skip-first[=DATA-SKIP-FIRST]  Skip data header [default: true]
      --dry-run                            Skip updating documents [default: false]
```

### Arguments

| Name        | Description                               |
|-------------|-------------------------------------------|
| config-file | path to the [config](#update-config-file) |
| data-file   | path to the data file (excel or csv)      |

### Options

| Name            | Description                                                              |
|-----------------|--------------------------------------------------------------------------|
| data-offset     | After the data is loading we can provide a offset (is uses \array_slice) |
| data-length     | After the data is loading we can provide a length (is uses \array_slice) |   
| data-skip-first | If the data file contains a header row, also needed when using offset.   |

### Update Config file

| Option (path)                     | Description                                                            |
|-----------------------------------|------------------------------------------------------------------------|
| update.contentType **[required]** | The target contentType for updating                                    |
| update.indexEmsId **[required]**  | The column index that contains the ouuid for updating. (first = **0**) |
| update.collectionField            | Data, for a same ouuid, are saved as collection for the given field    |
| update.mapping[]                  | Provide a mapping array between document and data file                 |
| update.mapping[].field            | The document field name                                                |
| update.mapping[].indexDataColumn  | The data value column index (first = **0**)                            |
| dataColumns[]                     | [see data columns](#update-config-data-columns)                        |

#### Example update page contentType.
- The first column (0) contains a valid emsId (page:ouuid)
- The second column (1) equals the update value for the page title field.
- The third column (2) equals the update value for the page description field.

```json
{
  "update": {
    "contentType": "page",
    "indexEmsId": 0,
    "mapping": [
      { "field": "title", "indexDataColumn": 1 },
      { "field": "description", "indexDataColumn": 2 }
    ]
  }
}
```

### Update config data columns

Provide dataColumns for transforming the provided data by row.

#### BusinessId dataColumn

DataColumn of the type **businessId** are used for replacing business data by valid emsId.
Necessary if the data file does not contain emsIds for **update.indexEmsId** or **update.mapping[].indexDataColumn**

| Option                                       | Description                                                             |
|----------------------------------------------|-------------------------------------------------------------------------|
| dataColumns[].index **[required]**           | the column index in the data file (starts a **0**)                      |
| dataColumns[].type **[required]**            | **businessId**                                                          |
| dataColumns[].contentType **[required]**     | the data value comes from this contentType                              |
| dataColumns[].field **[required]**           | the data value comes from this field inside the contentType             |
| dataColumns[].scrollSize [default=1000]      | for performance we scroll overall documents and build an internal array |
| dataColumns[].scrollMust [default=null]      | add a must query for scrolling                                          |
| dataColumns[].removeNotFound [default=false] | after transforming remove all not valid emsIds                          |


##### Example

Data file contains products and new category codes.
The first column (0) contains the product code and the 4th column contains a category code.

For updating the products we need an emsId for the products and category (dataLink).
The data can contain not existing category codes, do not update them (removeNotFound=true).
Also the example will only update active products by the scrollMust option.

```json
{
  "update": {
    "contentType": "product",
    "indexEmsId": 0,
    "mapping": [
      { "field": "category", "indexDataColumn": 3 }
    ]
  },
  "dataColumns": [
    {
      "index": 0,
      "type": "businessId",
      "field": "code",
      "contentType": "product",
      "removeNotFound": true,
      "scrollSize": 2000,
      "scrollMust": [
        { "term": { "active": { "value": true } } }
      ]
    },
    {
      "index": 3,
      "type": "businessId",
      "field": "code",
      "contentType": "product_category",
      "scrollSize": 2000,
      "removeNotFound": true
    }
  ]
}
```




