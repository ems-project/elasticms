# Search API

Those endpoints bassicly give you access to the elasticsearch content via the API of the CMS. Just like a HTTP proxy.

## Search endpoint

Do an elasticsearch query in the cluster via the CMS.
The JSON body should contain a `search` member that contains a serialized JSON encoded [`EMS\CommonBundle\Search`](../../../../EMS/common-bundle/src/Search/Search.php) object.

Minimal example:

```shell
curl -X POST \
     -H "X-Auth-Token: ${AUTH_TOKEN}" \
     -H "Accept: application/json" \
     http://localhost:8881/api/search/search -d \
'{
  "search": "{\"indices\": [\"ems_demo_preview\"]}"
}' -w '\n'
```

This will returns you a regular elasticsearch response with the 10 first documents in the `ems_demo_preview`index.

Structure of a `Search` object:

 * `indices`: The elasticsearch indices to search in (array of string)
 * `sourceIncludes`: The document fields to include in the response (array of string)
 * `sourceExcludes`:  The document fields to exclude in the response (array of string)
 * `contentTypes`:  The content types to search in (array of string)
 * `aggregations`: A [elasticsearch aggregation query](https://www.elastic.co/docs/explore-analyze/query-filter/aggregations) (array)
 * `size`: The number of documents to return (integer)
 * `from`: The first document to return, useful for pagination (integer)
 * `sort`: An [elasticsearch sort search result](https://www.elastic.co/docs/reference/elasticsearch/rest-apis/sort-search-results)
 * `postFilter`: An [elasticsearch filter search results](https://www.elastic.co/docs/reference/elasticsearch/rest-apis/filter-search-results)
 * `suggest`: An [elasticsearch suggest query](https://www.elastic.co/docs/reference/elasticsearch/rest-apis/search-suggesters)
 * `highlight`: An [elasticsearch highlight query](https://www.elastic.co/docs/reference/elasticsearch/rest-apis/highlighting)
 * `regex`: A regex to filter the `indices`
 * `query`: An [elasticsearch search query](https://www.elastic.co/docs/explore-analyze/query-filter/languages/querydsl)

Example of Search object to get the 3 last finalized simple_pages published in preview:

```json
{
  "indices": [
    "ems_demo_preview"
  ],
  "sourceIncludes": [
    "title",
    "body"
  ],
  "contentTypes": [
    "simple_page"
  ],
  "size": 3,
  "from": 0,
  "sort": {
    "_finalization_datetime": {
      "order": "desc",
      "missing": "_last",
      "unmapped_type": "long"
    }
  }
}
```
And the call to the CMS.

Notice: The Search is currently twiced encoded.

```shell
curl -X POST \
     -H "X-Auth-Token: ${AUTH_TOKEN}" \
     -H "Accept: application/json" \
     http://localhost:8881/api/search/search -d \
'{
  "search": "{\"indices\":[\"ems_demo_preview\"],\"sourceIncludes\":[\"title\",\"body\"],\"contentTypes\":[\"simple_page\"],\"size\":3,\"from\":0,\"sort\":{\"_finalization_datetime\":{\"order\":\"desc\",\"missing\":\"_last\",\"unmapped_type\":\"long\"}}}"
}' -w '\n'
```
