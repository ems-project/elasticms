# File API

The File API lets you upload, check, retrieve and download binary files stored by ElasticMS Admin.
Uploaded files are identified by their content hash.

Every request must be authenticated with an API token. See the [Login API](./login.md) documentation
for token generation and validation.

In the examples below, the token is available in the `AUTH_TOKEN` environment variable:

```shell
export AUTH_TOKEN='EDcTszIHnaaDCpvpi+dJeakj6uOsDqtvSY6rqJyDR3baPpnFA+6u4UAaPcMuJIAfwTs='
```

## Main concepts

A file is stored under a hash computed from its content. The hash algorithm is provided by
`GET /api/file/hash-algo` and is usually `sha1`.

The file metadata used in document fields follows this structure:

```json
{
    "sha1": "cc805347b91457ce0ca10166b0ba73ea182f0dd4",
    "_hash": "cc805347b91457ce0ca10166b0ba73ea182f0dd4",
    "filesize": 12345,
    "_size": 12345,
    "filename": "document.pdf",
    "_name": "document.pdf",
    "mimetype": "application/pdf",
    "_type": "application/pdf",
    "_algo": "sha1"
}
```

Fields without the leading `_` are deprecated.

## Endpoints

| Action                          | Endpoint                      |
| ------------------------------- | ----------------------------- |
| Get the hash algorithm          | `GET /api/file/hash-algo`     |
| Check several hashes            | `POST /api/file/heads`        |
| Get file information            | `GET /api/file/info/{hash}`   |
| View a file inline              | `GET /api/file/view/{hash}`   |
| Download a file                 | `GET /api/file/{hash}`        |
| Initialize a chunked upload     | `POST /api/file/init-upload`  |
| Upload one chunk                | `POST /api/file/chunk/{hash}` |
| Upload with multipart form data | `POST /api/file/upload`       |

## Get the hash algorithm

Use `GET /api/file/hash-algo` before computing a file hash client-side.

```shell
curl -X GET \
     -H "X-Auth-Token: ${AUTH_TOKEN}" \
     -H 'Accept: application/json' \
     http://localhost:8881/api/file/hash-algo -w '\n'
```

Successful response:

```json
{
    "hash_algo": "sha1"
}
```

## Check if files already exist

Use `POST /api/file/heads` to check several hashes in one request. The body is a JSON array of file
hashes.

```shell
curl -X POST \
     -H "X-Auth-Token: ${AUTH_TOKEN}" \
     -H 'Content-Type: application/json' \
     -H 'Accept: application/json' \
     http://localhost:8881/api/file/heads -d \
'[
  "cc805347b91457ce0ca10166b0ba73ea182f0dd4",
  "37fff25eb13e97b2fcc315a02482d41a67f2c787"
]' -w '\n'
```

Successful response:

```json
["cc805347b91457ce0ca10166b0ba73ea182f0dd4", true]
```

Hashes **not** found in storage are returned as strings. Found hashes are returned as `true`.

For a single file, a `HEAD` request can also be used:

```shell
curl --head \
     -H "X-Auth-Token: ${AUTH_TOKEN}" \
     http://localhost:8881/api/file/cc805347b91457ce0ca10166b0ba73ea182f0dd4
```

## Get file information

Use `GET /api/file/info/{hash}` to retrieve metadata known by ElasticMS for a file.

```shell
curl -X GET \
     -H "X-Auth-Token: ${AUTH_TOKEN}" \
     -H 'Accept: application/json' \
     http://localhost:8881/api/file/info/cc805347b91457ce0ca10166b0ba73ea182f0dd4 -w '\n'
```

Successful response:

```json
{
    "hash": "cc805347b91457ce0ca10166b0ba73ea182f0dd4",
    "algo": "sha1",
    "name": "favicon.ico",
    "type": "image/png",
    "file-object": {
        "sha1": "cc805347b91457ce0ca10166b0ba73ea182f0dd4",
        "_hash": "cc805347b91457ce0ca10166b0ba73ea182f0dd4",
        "filesize": 12345,
        "_size": 12345,
        "filename": "favicon.ico",
        "_name": "favicon.ico",
        "mimetype": "image/png",
        "_type": "image/png",
        "_algo": "sha1"
    },
    "first-seen": {
        "date": "2026-05-05 20:22:36.000000",
        "timezone_type": 3,
        "timezone": "Europe\/Brussels"
    },
    "last-seen": {
        "date": "2026-05-05 20:22:36.000000",
        "timezone_type": 3,
        "timezone": "Europe\/Brussels"
    },
    "uploaded-by": "demo",
    "hidden": false,
    "size": 12345,
    "uploads": 1,
    "head-counter": 1
}
```

Add `?first-seen=false` when the first-seen lookup is not needed.

## View or download a file

Use `GET /api/file/view/{hash}` to render the file inline when the browser supports its MIME type:

```shell
curl -X GET \
     -H "X-Auth-Token: ${AUTH_TOKEN}" \
     http://localhost:8881/api/file/view/cc805347b91457ce0ca10166b0ba73ea182f0dd4 \
     --output favicon.ico
```

Use `GET /api/file/{hash}` to download the file as an attachment:

```shell
curl -X GET \
     -H "X-Auth-Token: ${AUTH_TOKEN}" \
     http://localhost:8881/api/file/cc805347b91457ce0ca10166b0ba73ea182f0dd4 \
     --output favicon.ico
```

Both endpoints also support `HEAD` requests.

## Upload a file with multipart form data

For simple integrations, use `POST /api/file/upload` with a multipart field named `upload`.

```shell
curl -X POST \
     -H "X-Auth-Token: ${AUTH_TOKEN}" \
     -H 'Accept: application/json' \
     http://localhost:8881/api/file/upload \
     -F 'upload=@./favicon.ico;type=image/png' -w '\n'
```

Successful response:

```json
{
    "success": true,
    "uploaded": 1,
    "fileName": "favicon.ico",
    "_name": "favicon.ico",
    "_size": 12345,
    "_type": "image/png",
    "_hash": "cc805347b91457ce0ca10166b0ba73ea182f0dd4",
    "_algo": "sha1",
    "url": "/data/file/view/cc805347b91457ce0ca10166b0ba73ea182f0dd4"
}
```

`uploaded` is `1` when the file is available in storage.

## Upload a file by chunks

The chunked upload flow is useful for large files and resumable uploads.

1. Compute the file hash with the algorithm returned by `/api/file/hash-algo`.
2. Initialize the upload with the hash, file size, filename and MIME type.
3. Send the binary content to `/api/file/chunk/{hash}` in one or more chunks.
4. Stop when the returned `uploaded` value equals the file size and `available` is `true`.

Example hash and size calculation on Linux:

```shell
FILE='./favicon.ico'
FILE_HASH=$(sha1sum "${FILE}" | cut -d ' ' -f 1)
FILE_SIZE=$(stat -c '%s' "${FILE}")
```

Initialize the upload:

```shell
curl -X POST \
     -H "X-Auth-Token: ${AUTH_TOKEN}" \
     -H 'Content-Type: application/json' \
     -H 'Accept: application/json' \
     http://localhost:8881/api/file/init-upload -d \
'{
  "name": "favicon.ico",
  "type": "image/png",
  "hash": "cc805347b91457ce0ca10166b0ba73ea182f0dd4",
  "size": 12345,
  "algo": "sha1"
}' -w '\n'
```

Successful response:

```json
{
    "acknowledged": true,
    "success": true,
    "sha1": "cc805347b91457ce0ca10166b0ba73ea182f0dd4",
    "hash": "cc805347b91457ce0ca10166b0ba73ea182f0dd4",
    "type": "image/png",
    "available": false,
    "name": "favicon.ico",
    "size": 12345,
    "status": "created",
    "uploaded": 0,
    "user": "demo",
    "fileName": "favicon.ico",
    "previewUrl": "/asset/preview/path",
    "chunkUrl": "/api/file/chunk/cc805347b91457ce0ca10166b0ba73ea182f0dd4",
    "url": "/asset/path"
}
```

Upload the binary content. The request body is the raw chunk bytes, not JSON:

```shell
curl -X POST \
     -H "X-Auth-Token: ${AUTH_TOKEN}" \
     -H 'Content-Type: application/octet-stream' \
     -H 'Accept: application/json' \
     http://localhost:8881/api/file/chunk/cc805347b91457ce0ca10166b0ba73ea182f0dd4 \
     --data-binary '@./favicon.ico' -w '\n'
```

Successful final response:

```json
{
    "acknowledged": true,
    "success": true,
    "sha1": "cc805347b91457ce0ca10166b0ba73ea182f0dd4",
    "hash": "cc805347b91457ce0ca10166b0ba73ea182f0dd4",
    "type": "image/png",
    "available": true,
    "name": "favicon.ico",
    "size": 12345,
    "status": "created",
    "uploaded": 12345,
    "user": "demo",
    "fileName": "favicon.ico",
    "previewUrl": "/asset/preview/path",
    "chunkUrl": "/api/file/chunk/cc805347b91457ce0ca10166b0ba73ea182f0dd4",
    "url": "/asset/path"
}
```

If an upload is interrupted, call `/api/file/init-upload` again with the same hash and size. The
response contains the current `uploaded` offset. Resume by sending the remaining bytes from that
offset.

## Use an uploaded file in document data

Once the file is available, store its file object in a document field configured as an asset field.
Use the values returned by the upload response:

```json
{
    "attachment": {
        "sha1": "cc805347b91457ce0ca10166b0ba73ea182f0dd4",
        "_hash": "cc805347b91457ce0ca10166b0ba73ea182f0dd4",
        "filesize": 12345,
        "_size": 12345,
        "filename": "favicon.ico",
        "_name": "favicon.ico",
        "mimetype": "image/png",
        "_type": "image/png",
        "_algo": "sha1"
    }
}
```

Then create or update the document with the [Data API](./data.md).

## Error handling

| Symptom                                | Check                                                                                 |
| -------------------------------------- | ------------------------------------------------------------------------------------- |
| `401 Unauthorized`                     | The `X-Auth-Token` header is missing or invalid.                                      |
| `400 Bad Request` on `init-upload`     | The JSON body must contain `hash` and `size`.                                         |
| `success: false` on upload             | Check the logs and verify the hash, size, MIME type and current user.                 |
| `Upload job not found`                 | Call `/api/file/init-upload` before sending chunks.                                   |
| Uploaded size does not match file size | Resume from the returned `uploaded` offset or restart with the correct hash and size. |
| `404 Not Found` on view/download/info  | The hash is unknown or the file is not available in storage.                          |
