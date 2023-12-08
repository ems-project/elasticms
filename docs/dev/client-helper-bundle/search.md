# Search

Routes can use the search controller, which using a predefined search config.
The search config can be defined in: 
- Defaults option of the route (preferred)
- `seach_config` option in `EMSCH_ENVS`
- Environment variable `EMSCH_SEARCH_CONFIG`

## Config

- types: _contenttype field in the result
- fields: free text search in these fields
- query_search: define the query for search
- synonyms: can be used for translating emsLinks
- sizes: define possible search sizes, default is the first one, use request param **'l'**.
- sorts: key is the value of the request param **'s'**
- minimum_should_match: the bool query's minimum should match parameter, default `1`. Check the [minimum_should_match elasticsearch documentation](https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-minimum-should-match.html).

```yaml
search_example:
    config:
        path: '/search'
        controller: 'emsch.controller.search::handle'
        defaults: {
            types: [ "page", "block" ],
            fields: ["all_url_%locale%", "url"],
            synonyms: ["keyword"],
            sizes: [10,25,50],
            sorts: {
                recent: {field: "search_date", order: "desc", unmapped_type: "date", missing:  "_last"},
                title: "title_%locale%.keyword"
            },
            filters: {
                ctype: {type: "terms", field: "search_type", aggs_size: 10},
                fdate: {type: "date_range", field: "search_dates"}
            }
   
        }
    template_static: template/page/search_page.html.twig
```

## Query search

Define the search query, cannot be used in combination with the fields option.

!> Important the query_search option needs to be a bool query, since 5.12

Example search config with query_search defined.

```json
{
    "query_search": {
        "bool": {
            "minimum_should_match": 1,
            "should": [
                { "multi_match": { "query": "%query%", "operator": "and", "type":"bool_prefix", "boost":10, "fields": ["title_%_locale%","all_%_locale%"] } },
                { "query_string": { "query": "%query%", "default_operator": "AND", "boost": 5, "default_field": "title_%_locale%" } },
                { "query_string": { "query": "%query%", "default_operator": "AND", "default_field": "all_%_locale%" } }
            ]
        }
    }
}
```


## Filters

- filterName: the named of the request query parameter.
- type: term, terms, date_range
- public: default true, only public filters will accept request values
- active: default true, deactivated filter can become active by passing the filterName
- field: the search field in the elasticsearch document
- aggs_size: for adding the field in aggregations
- post_filter: filter after making aggregations (see Post Filtering)
- optional: if not all docs contain this filter, default false
- clause: add as must, must_not or should clause the filter to the bool query, default must
````json
{
   "filterName": {"type":  "type", "field":  "field", "aggs_size": 10, "post_filter":  true, "optional":  true}
}
````

### Private filter

By setting the option **public** to false the filter will not get his value from the request query.
You pass the private value with the **value** option.
````json
{
   "filterName": {"type":  "terms", "field":  "_contenttype", "public":  false, "value":  ["page"]}
}
````

### Post Filtering

By default post filtering is enable for public **terms** filters. This way the aggregations are computed before filtering,
we still known the counts of other choices.

[elasticsearch doc](https://www.elastic.co/guide/en/elasticsearch/reference/current/search-request-post-filter.html)

### DateRange 

Example uri for filtering all documents in november 2018.

/search?**fdate[start_date]**=1-11-2018&**fdate[end_date]**=30-11-2018

### DateTimeRrange

Show all document bigger then now on the field new_until.
Important we need to set the date_format to false, because we are passing 'now'.
If we let the user select the date from a datepicker we can use the date_format and make the filter public.

URI: /search?onlyNew=1

```json
{
  "filters": {
    "onlyNew": {
      "type": "datetime_range",
      "field": "new_until_date",
      "public": false,
      "active": false,
      "date_format": false,
      "post_filter": true,
      "value": {
        "start": "now"
      }
    }
  }
}
```

### Date Version

Elasticms supports versioning on documents. Documents share a version uuid and have a from and to date. 
The document without a to date is the current version.

This means we can search for date and get the matching version document. 
We will never have multiple results for a certain date because elasticms does not create gabs between versions.

Example:

````json
{
   "search_version": { "type": "date_version", "date_format": "d/m/Y", "value": "now"}
}
````
Default config options:
- **field**: version_from_date
- **secondary_field**: version_to_date

| Document X        | version_from_date             | version_to_date               |
|-------------------|-------------------------------|-------------------------------|
| Original document | 2019-01-01T14:54:09+02:00     | **2019-06-06T16:30:08+02:00** |
| Major version     | **2019-06-06T16:30:08+02:00** | 2019-08-08T19:22:05+02:00     |
| Minor version     | 2019-08-08T19:22:05+02:00     | 2020-01-01T10:15:17+02:00     |
| Current version   | 2020-01-01T10:15:17+02:00     |                               |

| Search value | result                                     | 
|--------------|--------------------------------------------|
| empty        | Current version (because value = 'now')    |
| 19/02/2020   | Current version                            |
| 01/01/2020   | Current version                            |
| 31/12/2019   | Minor version                              |
| 07/07/2018   | No result (document created on 01/01/2019) |
| 06/06/2019   | Major version                              |
| 19/02/2019   | Original document                          |
| 01/08/2019   | Minor version                              |

## Nested queries

If facets depends on facets, we can create a nested collection for filtering.

### Example document source's
````json
[
    {
        "name": "person1",
        "tags": [
            {"type": "tag1", "values": [1, 2, 3, 4]},
            {"type": "tag2", "values": [5, 7]},
            {"type": "tag3", "values": [5, 7]}
        ]
    },
    {
        "name": "person2",
        "tags": [
            {"type": "tag2", "values": [1, 2]},
            {"type": "tag4", "values": [5, 7]}
        ]
    }
]
````
### Configuration 2 nested filters
````json
{
  "filters": {
    "personTags": {
      "type": "terms",
      "nested_path": "tags",
      "field": "type",
      "aggs_size": 50,
      "sort_field": "_term",
      "sort_order": "desc"
    },
    "personValues": {
      "type": "terms",
      "nested_path": "tags",
      "field": "values",
      "aggs_size": 50,
      "sort_field": "_term",
      "sort_order": "desc"
    }
  }
}
````

## Synonyms

Translate emsLinks inside a search result.

### Simple, 

will search and match with the **_all** field.
````json
{
  "synonyms": ["keyword"]
}
````

### Advanced

- field: search result field
- types: will match on _contenttype
- search: search field for synonym
- filter: apply extra filter for searching synonyms
````json
{
  "synonyms": [
      {
        "field": "search_keywords",
        "types": [
          "keyword"
        ],
        "search": "title_%locale%",
        "filter": {
          "exists": {
            "field": "code"
          }
        }
      }
    ]
}
````

## Highlight

Get highlighted snippets from one or more fields in your search 
````json
{
  "highlight": {
    "pre_tags": [
      "<em>"
    ],
    "post_tags": [
      "</em>"
    ],
    "fields": {
      "all_%locale%": {
        "fragment_size": 2000,
        "number_of_fragments": 50
      }
    }
  }
}
````