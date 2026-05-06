# Data API

The data endpoints allows you to update CMS contents just like the regular UI.

The body (JSON format) of those endpoints depend the content type you want to work with. 
And, by extension, it depends on how the content type is configured in the elasticMS.

In the following documentation we'll consider a simple content type `simple_page`:

 * a mandatory `title` field
 * an optional `body` field


Example of body for this content type:

```json
{
  "title": "Titre",
  "body": "Contenu de la page"
}
```

First, you need an `auth-token`. Checks the [Login API](./login.md) documentation.
In the following documentation we'll consider that an API Auth Token is available iin the `AUTH_TOKEN` environment variable:

```shell
export AUTH_TOKEN=nlpUnMR/W8bgSSclYXI2G0dP5REdp5yhvaXfMDV/he+XgQgI7pIRqkuNqsJRJzoYvYM=

curl -X GET -H "X-Auth-Token: ${AUTH_TOKEN}"  http://localhost:8881/api/test
```
If the response returns a `200 OK` and a `success` equal to `true` in the JSON body, you're all good.

## Data get API

This endpoint retrieves you the content of an existing document based on its Object Universal Unique Identifier (aka: `ouuid`).

Get it to the endpoint `/api/data/{name of the content type}/index`.

Example:

```shell
curl -X GET \
     -H "X-Auth-Token: ${AUTH_TOKEN}" \
     -H "Accept: application/json" \
     http://localhost:8881/api/data/simple_page/97591e4d-c71a-48ae-8504-67d09df595c2 -w '\n'
```

If it's just to ensure that the document exists you can do a `HEAD`:

```shell
curl --head \
     -H "X-Auth-Token: ${AUTH_TOKEN}" \
     -H "Accept: application/json" \
     http://localhost:8881/api/data/simple_page/97591e4d-c71a-48ae-8504-67d09df595c2 -w '\n'
```

## Data index API

This endpoint is inspired by the elasticsearch `index` endpoint. It's the easiest way to create or update a document in the CMS.

### Create a document with the index API

Send the JSON body to the endpoint `/api/data/{name of the content type}/index`.

Example:

```shell
curl -X POST \
     -H "X-Auth-Token: ${AUTH_TOKEN}" \
     -H "Accept: application/json" \
     http://localhost:8881/api/data/simple_page/index -d \
'{
  "title": "Titre",
  "body": "Contenu de la page"
}' -w '\n'
```

The JSON response contains:
 * `success`: A boolean that is `true` if a document ha been created
 * `ouuid`: the object unique identifier of the document in the CMS (string)
 * `revision_id`: the identifier of the first revision of this document
 * `notice`: an optional array of strings, containing the notices raised by the call
 * `warning`: an optional array of strings, containing the warnings raised by the call
 * `error`: an optional array of strings, containing the errors raised by the call

If you plan to update the document in the futur you have to save the `ouuid` of the response.
Another option is to generate it on your side, for example a [`UUID`](https://en.wikipedia.org/wiki/Universally_unique_identifier) and call the [index endpoint with a `ouuid`](#create-or-update-a-document-for-a-ouuid-with-the-index-api).

### Create, or update, a document for a ouuid with the index API

Send the JSON body to the endpoint `/api/data/{name of the content type}/index/{ouuid}`.

The ouuid can have been retrieved by another call for an existing document. Or generated up front.

Example: 

```shell
curl -X POST \
     -H "X-Auth-Token: ${AUTH_TOKEN}" \
     -H "Accept: application/json" \
     http://localhost:8881/api/data/simple_page/index/97591e4d-c71a-48ae-8504-67d09df595c2 -d \
'{
  "title": "Titre 2",
  "body": "Nouveau contenu de la page"
}' -w '\n'
```