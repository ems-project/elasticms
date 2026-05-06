# Data API

The Data API lets you read, create and update CMS documents from the Admin application.

Every request must be authenticated with an API token. See the [Login API](./login.md) documentation
for token generation and validation.

In the examples below, the token is available in the `AUTH_TOKEN` environment variable:

```shell
export AUTH_TOKEN='nlpUnMR/W8bgSSclYXI2G0dP5REdp5yhvaXfMDV/he+XgQgI7pIRqkuNqsJRJzoYvYM='
```

The request body depends on the target content type configuration. The following examples use a
`simple_page` content type with:

- a required `title` field;
- an optional `body` field.

Example JSON body:

```json
{
    "title": "Title",
    "body": "Page content"
}
```

## Get a document

Use `GET /api/data/{contentTypeName}/{ouuid}` to retrieve the newest revision of an existing
document.

The `ouuid` is the document Object Universal Unique Identifier.

Example:

```shell
curl -X GET \
     -H "X-Auth-Token: ${AUTH_TOKEN}" \
     -H 'Accept: application/json' \
     http://localhost:8881/api/data/simple_page/97591e4d-c71a-48ae-8504-67d09df595c2 -w '\n'
```

Successful response:

```json
{
    "acknowledged": true,
    "success": true,
    "revision": {
        "_contenttype": "simple_page",
        "_finalization_datetime": "2026-05-06T12:15:34+02:00",
        "_finalized_by": "demo",
        "body": "Page content",
        "title": "Title",
        "_sha1": "5eaddbc63a989e99df658569cf6b9db829a581b1"
    },
    "ouuid": "97591e4d-c71a-48ae-8504-67d09df595c2",
    "id": 263
}
```

## Create or replace a document

The `index` endpoint creates a document when no existing document is found for the given `ouuid`.
When the document already exists, the endpoint replaces the document data with the JSON body.

Use `POST /api/data/{contentTypeName}/index` to create a document and let ElasticMS generate the
`ouuid`.

Example:

```shell
curl -X POST \
     -H "X-Auth-Token: ${AUTH_TOKEN}" \
     -H 'Content-Type: application/json' \
     -H 'Accept: application/json' \
     http://localhost:8881/api/data/simple_page/index -d \
'{
  "title": "Title",
  "body": "Page content"
}' -w '\n'
```

Use `POST /api/data/{contentTypeName}/index/{ouuid}` when you already have an identifier, or when
you want future calls to update the same document.

Example:

```shell
curl -X POST \
     -H "X-Auth-Token: ${AUTH_TOKEN}" \
     -H 'Content-Type: application/json' \
     -H 'Accept: application/json' \
     http://localhost:8881/api/data/simple_page/index/97591e4d-c71a-48ae-8504-67d09df595c2 -d \
'{
  "title": "Updated title",
  "body": "Updated page content"
}' -w '\n'
```

Successful response:

```json
{
    "success": true,
    "ouuid": "97591e4d-c71a-48ae-8504-67d09df595c2",
    "type": "simple_page",
    "revision_id": 124
}
```

The response may also contain `notice`, `warning` or `error` arrays when messages are raised during
the request.

## Partially update a document

Use `POST /api/data/{contentTypeName}/update/{ouuid}` to merge the JSON body with the current
document data. Fields omitted from the request body keep their current value.

Example:

```shell
curl -X POST \
     -H "X-Auth-Token: ${AUTH_TOKEN}" \
     -H 'Content-Type: application/json' \
     -H 'Accept: application/json' \
     http://localhost:8881/api/data/simple_page/update/97591e4d-c71a-48ae-8504-67d09df595c2 -d \
'{
  "title": "Updated title"
}' -w '\n'
```

Successful response:

```json
{
    "notice": [
        "The document \"97591e4d-c71a-48ae-8504-67d09df595c2\" has been published in preview"
    ],
    "acknowledged": true,
    "success": true,
    "ouuid": "97591e4d-c71a-48ae-8504-67d09df595c2",
    "type": "simple_page",
    "revision_id": 264
}
```

## Refresh the default environment

By default, the document is finalized but the default environment is not explicitly refreshed by the
request. Add `?refresh=true` when the document must be searchable immediately after the API call:

```shell
curl -X POST \
     -H "X-Auth-Token: ${AUTH_TOKEN}" \
     -H 'Content-Type: application/json' \
     -H 'Accept: application/json' \
     'http://localhost:8881/api/data/simple_page/index/97591e4d-c71a-48ae-8504-67d09df595c2?refresh=true' -d \
'{
  "title": "Updated title",
  "body": "Updated page content"
}' -w '\n'
```
