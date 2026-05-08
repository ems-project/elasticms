# Search API

The Search API gives authenticated access to Elasticsearch data through the Admin application.

Every request must be authenticated with an API token. See the [Login API](./login.md) documentation
for token generation and validation.

In the examples below, the token is available in the `AUTH_TOKEN` environment variable:

```shell
export AUTH_TOKEN='nlpUnMR/W8bgSSclYXI2G0dP5REdp5yhvaXfMDV/he+XgQgI7pIRqkuNqsJRJzoYvYM='
```

## Search documents

Use `POST /api/search/search` to run an Elasticsearch query through ElasticMS.

The request body must contain a `search` field. Its value is a JSON string containing a serialized
[`EMS\CommonBundle\Search`](../../../../EMS/common-bundle/src/Search/Search.php) object. This means
that the search object is encoded once as JSON, then sent as a string inside the request JSON body.

Minimal example:

```shell
curl -X POST \
     -H "X-Auth-Token: ${AUTH_TOKEN}" \
     -H 'Content-Type: application/json' \
     -H 'Accept: application/json' \
     http://localhost:8881/api/search/search -d \
'{
  "search": "{\"indices\":[\"ems_demo_preview\"],\"query\":{\"match_all\":{}},\"size\":10}"
}' -w '\n'
```

The response is the raw Elasticsearch search response returned by the cluster.

Common `Search` properties:

- `indices`: Elasticsearch indices or aliases to search in.
- `query`: Elasticsearch query DSL. The native serializer may also output this field as
  `queryArray`.
- `sources`: Fields to include in the response. It can be either an array of field names, or an
  object with `includes` and `excludes`.
- `contentTypes`: Content types to filter on.
- `aggs`: Elasticsearch aggregations.
- `size`: Number of documents to return. Default: `10`.
- `from`: First document offset, useful for pagination. Default: `0`.
- `sort`: Elasticsearch sort definition.
- `highlight`: Elasticsearch highlight definition.
- `regex`: Regular expression used to filter the configured `indices`.

Search object example for the last three finalized `simple_page` documents published in preview:

```json
{
    "indices": ["ems_demo_preview"],
    "sources": ["title", "body"],
    "contentTypes": ["simple_page"],
    "query": {
        "match_all": {}
    },
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

The corresponding API call:

```shell
curl -X POST \
     -H "X-Auth-Token: ${AUTH_TOKEN}" \
     -H 'Content-Type: application/json' \
     -H 'Accept: application/json' \
     http://localhost:8881/api/search/search -d \
'{
  "search": "{\"indices\":[\"ems_demo_preview\"],\"sources\":[\"title\",\"body\"],\"contentTypes\":[\"simple_page\"],\"query\":{\"match_all\":{}},\"size\":3,\"from\":0,\"sort\":{\"_finalization_datetime\":{\"order\":\"desc\",\"missing\":\"_last\",\"unmapped_type\":\"long\"}}}"
}' -w '\n'
```

## Count documents

Use `POST /api/search/count` with the same `search` payload to return only the number of matching
documents.

```shell
curl -X POST \
     -H "X-Auth-Token: ${AUTH_TOKEN}" \
     -H 'Content-Type: application/json' \
     -H 'Accept: application/json' \
     http://localhost:8881/api/search/count -d \
'{
  "search": "{\"indices\":[\"ems_demo_preview\"],\"contentTypes\":[\"simple_page\"]}"
}' -w '\n'
```

Successful response:

```json
{
    "count": 42
}
```

## Get a document from an index

Use `POST /api/search/document` to retrieve one document by index and `ouuid`.

```shell
curl -X POST \
     -H "X-Auth-Token: ${AUTH_TOKEN}" \
     -H 'Content-Type: application/json' \
     -H 'Accept: application/json' \
     http://localhost:8881/api/search/document -d \
'{
  "index": "ems_demo_preview",
  "content-type": "simple_page",
  "ouuid": "97591e4d-c71a-48ae-8504-67d09df595c2",
  "source-includes": ["title", "body"],
  "source-excludes": []
}' -w '\n'
```

Successful response:

```json
{
    "_source": {
        "_contenttype": "simple_page",
        "body": "Page content",
        "title": "Updated title",
        "_sha1": "d31ebd05a566ea5095ce849c4f82ff87b7c59c93"
    },
    "_id": "97591e4d-c71a-48ae-8504-67d09df595c2",
    "_index": "ems_demo_preview_20260210_132214"
}
```

## Cluster status

Use `GET /api/search/version` to return the Elasticsearch version:

```shell
curl -X GET \
     -H "X-Auth-Token: ${AUTH_TOKEN}" \
     -H 'Accept: application/json' \
     http://localhost:8881/api/search/version -w '\n'
```

Successful response:

```json
{
    "version": "8.19.4"
}
```

Use `GET /api/search/health-status` to return the cluster health status:

```shell
curl -X GET \
     -H "X-Auth-Token: ${AUTH_TOKEN}" \
     -H 'Accept: application/json' \
     http://localhost:8881/api/search/health-status -w '\n'
```

Successful response:

```json
{
    "status": "green"
}
```

## Refresh an index

Use `POST /api/search/refresh` to refresh one index or alias:

```shell
curl -X POST \
     -H "X-Auth-Token: ${AUTH_TOKEN}" \
     -H 'Content-Type: application/json' \
     -H 'Accept: application/json' \
     http://localhost:8881/api/search/refresh -d \
'{
  "index": "ems_demo_preview"
}' -w '\n'
```

Successful response:

```json
{
    "success": true
}
```
