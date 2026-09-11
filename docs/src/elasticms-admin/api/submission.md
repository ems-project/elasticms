# Submission API

The Submission API stores form submissions, retrieves stored submission data and files, and manages
verification codes used by form integrations.

Every request must be authenticated with an API token. See the [Login API](./login.md) documentation
for token generation and validation.

In the examples below, the token is available in the `AUTH_TOKEN` environment variable:

```shell
export AUTH_TOKEN='EDcTszIHnaaDCpvpi+dJeakj6uOsDqtvSY6rqJyDR3baPpnFA+6u4UAaPcMuJIAfwTs='
```

## Endpoints

| Action                     | Endpoint                                                   |
| -------------------------- | ---------------------------------------------------------- |
| Create a submission        | `POST /api/forms/submissions`                              |
| Get a submission           | `GET /api/forms/submissions/{submissionId}`                |
| Get a submitted file       | `GET /api/forms/submissions/{submissionId}/files/{fileId}` |
| Create a verification code | `POST /api/forms/verifications`                            |
| Get a verification code    | `GET /api/forms/verifications?value={value}`               |

## Create a submission

Use `POST /api/forms/submissions` to persist a form submission.

Request body:

```json
{
    "form_name": "contact",
    "instance": "website",
    "locale": "en",
    "label": "Contact request from Jane Doe",
    "expire_date": "2026-12-31",
    "data": {
        "firstName": "Jane",
        "lastName": "Doe",
        "email": "jane.doe@example.test",
        "message": "Please contact me."
    },
    "files": [
        {
            "filename": "attachment.txt",
            "mimeType": "text/plain",
            "base64": "SGVsbG8gd29ybGQ=",
            "size": "11",
            "form_field": "attachment"
        }
    ]
}
```

Required fields are `form_name`, `instance`, `locale` and `data`. The optional `files` array stores
base64-encoded files with the submission. The optional `expire_date` defines when the submission can
be cleaned by the expiration process.

Example:

```shell
curl -X POST \
     -H "X-Auth-Token: ${AUTH_TOKEN}" \
     -H 'Content-Type: application/json' \
     -H 'Accept: application/json' \
     http://localhost:8881/api/forms/submissions -d \
'{
  "form_name": "contact",
  "instance": "website",
  "locale": "en",
  "label": "Contact request from Jane Doe",
  "data": {
    "firstName": "Jane",
    "lastName": "Doe",
    "email": "jane.doe@example.test",
    "message": "Please contact me."
  }
}' -w '\n'
```

Successful response:

```json
{
    "submission_id": "4a154f68-2386-4f80-a2c8-657a4af25b61",
    "submission": {
        "id": "4a154f68-2386-4f80-a2c8-657a4af25b61",
        "name": "contact",
        "instance": "website",
        "locale": "en",
        "data": {
            "firstName": "Jane",
            "lastName": "Doe",
            "email": "jane.doe@example.test",
            "message": "Please contact me."
        },
        "label": "Contact request from Jane Doe",
        "processTryCounter": 0,
        "processId": null,
        "processBy": null,
        "created": "2026-09-09T14:28:57+02:00",
        "modified": "2026-09-09T14:28:57+02:00"
    }
}
```

When files are submitted, the `submission.files` array contains file metadata:

```json
{
    "id": "4e2f1232-d389-42db-b8a9-5f4f92cd2e67",
    "filename": "attachment.txt",
    "mimeType": "text/plain",
    "size": "11",
    "formField": "attachment"
}
```

## Get a submission

Use `GET /api/forms/submissions/{submissionId}` to retrieve a stored submission.

```shell
curl -X GET \
     -H "X-Auth-Token: ${AUTH_TOKEN}" \
     -H 'Accept: application/json' \
     http://localhost:8881/api/forms/submissions/4a154f68-2386-4f80-a2c8-657a4af25b61 -w '\n'
```

Successful response:

```json
{
    "id": "4a154f68-2386-4f80-a2c8-657a4af25b61",
    "name": "contact",
    "instance": "website",
    "locale": "en",
    "data": {
        "email": "jane.doe@example.test",
        "message": "Please contact me."
    },
    "label": "Contact request from Jane Doe",
    "processTryCounter": 0,
    "processId": null,
    "processBy": null,
    "created": "2026-09-09T14:28:57+02:00",
    "modified": "2026-09-09T14:28:57+02:00"
}
```

Add `property` to return only one property. Nested values use Symfony property paths:

```shell
curl -X GET \
     -H "X-Auth-Token: ${AUTH_TOKEN}" \
     -H 'Accept: application/json' \
     'http://localhost:8881/api/forms/submissions/4a154f68-2386-4f80-a2c8-657a4af25b61?property=data[email]' -w '\n'
```

Response:

```json
{
    "data[email]": "jane.doe@example.test"
}
```

If the submission contains files, add `fileUrl` to generate URLs for those files. The value can
contain `{SUBMISSION_ID}` and `{FILE_ID}` placeholders.

```shell
curl -X GET \
     -H "X-Auth-Token: ${AUTH_TOKEN}" \
     -H 'Accept: application/json' \
     'http://localhost:8881/api/forms/submissions/4a154f68-2386-4f80-a2c8-657a4af25b61?fileUrl=/api/forms/submissions/{SUBMISSION_ID}/files/{FILE_ID}' -w '\n'
```

The response contains an additional `file_urls` array:

```json
{
    "file_urls": [
        "/api/forms/submissions/4a154f68-2386-4f80-a2c8-657a4af25b61/files/4e2f1232-d389-42db-b8a9-5f4f92cd2e67"
    ]
}
```

## Get a submitted file

Use `GET /api/forms/submissions/{submissionId}/files/{fileId}` to stream a file submitted with the
form.

```shell
curl -X GET \
     -H "X-Auth-Token: ${AUTH_TOKEN}" \
     http://localhost:8881/api/forms/submissions/4a154f68-2386-4f80-a2c8-657a4af25b61/files/4e2f1232-d389-42db-b8a9-5f4f92cd2e67 \
     --output attachment.txt
```

The response is streamed inline with the submitted file `Content-Type`, `Content-Length` and
`Content-Disposition` headers.

## Create a verification code

Use `POST /api/forms/verifications` to create a verification code for a value. If a non-expired code
already exists for the value, the existing code is returned and its expiration is extended.

Request body:

```json
{
    "value": "jane.doe@example.test"
}
```

Example:

```shell
curl -X POST \
     -H "X-Auth-Token: ${AUTH_TOKEN}" \
     -H 'Content-Type: application/json' \
     -H 'Accept: application/json' \
     http://localhost:8881/api/forms/verifications -d \
'{
  "value": "jane.doe@example.test"
}' -w '\n'
```

Successful response:

```json
{
    "code": "123456"
}
```

The `value` field is required. Verification codes expire after three hours.

## Get a verification code

Use `GET /api/forms/verifications?value={value}` to retrieve the verification code associated with a
value. Reading an existing code extends its expiration.

```shell
curl -X GET \
     -H "X-Auth-Token: ${AUTH_TOKEN}" \
     -H 'Accept: application/json' \
     'http://localhost:8881/api/forms/verifications?value=jane.doe@example.test' -w '\n'
```

Successful response:

```json
{
    "code": "123456"
}
```

## Error handling

| Symptom                        | Check                                                                           |
| ------------------------------ | ------------------------------------------------------------------------------- |
| `401 Unauthorized`             | The `X-Auth-Token` header is missing or invalid.                                |
| `400 Bad Request` on submit    | The JSON body is invalid or misses `form_name`, `instance`, `locale` or `data`. |
| `400 Bad Request` verification | The JSON body is invalid or the `value` field/query parameter is missing.       |
| `404 Not Found` submission     | The submission identifier does not exist.                                       |
| `404 Not Found` file           | The submission or file identifier does not exist.                               |
| `404 Not Found` verification   | No verification code exists for the requested `value`.                          |
