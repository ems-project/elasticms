# User API

The User API exposes authenticated user profile information and user impersonation helpers for
elasticMS Admin integrations.

Every request must be authenticated with an API token. See the [Login API](./login.md) documentation
for token generation and validation.

In the examples below, the token is available in the `AUTH_TOKEN` environment variable:

```shell
export AUTH_TOKEN='nlpUnMR/W8bgSSclYXI2G0dP5REdp5yhvaXfMDV/he+XgQgI7pIRqkuNqsJRJzoYvYM='
```

## Endpoints

| Action                             | Endpoint                            | Required role                                            |
| ---------------------------------- | ----------------------------------- | -------------------------------------------------------- |
| Get the authenticated user profile | `GET /api/user-profile`             | authenticated user                                       |
| Get enabled user profiles          | `GET /api/user-profiles`            | `ROLE_USER_READ`, `ROLE_USER_MANAGEMENT` or `ROLE_ADMIN` |
| Generate a token for another user  | `POST /api/user/proxy-authenticate` | `ROLE_USER_MANAGEMENT`                                   |

## Profile fields

User profile responses use this structure:

```json
{
    "id": 1,
    "username": "demo",
    "displayName": "Demo User",
    "roles": [
        "ROLE_USER"
    ],
    "email": "demo@example.org",
    "circles": [
        "cercle_type:AWNJBOh85f9MtpLT8n-a"
    ],
    "lastLogin": "2026-05-09T10:15:00+02:00",
    "expirationDate": null,
    "language": "en",
    "locale": "en",
    "localePreferred": "en",
    "userOptions": {
        "simplified_ui": false,
        "allowed_configure_wysiwyg": false,
        "custom_options": []
    }
}
```

Date fields are returned in ISO 8601 format when available. Nullable fields may be returned as
`null`.

## Get the authenticated user profile

Use `GET /api/user-profile` to retrieve the profile of the user associated with the current API
token.

```shell
curl -X GET \
     -H "X-Auth-Token: ${AUTH_TOKEN}" \
     -H 'Accept: application/json' \
     http://localhost:8881/api/user-profile -w '\n'
```

Successful response:

```json
{
    "id": 1,
    "username": "demo",
    "displayName": "Demo",
    "roles": [
        "ROLE_SUPER_ADMIN",
        "ROLE_COPY_PASTE",
        "ROLE_ALLOW_ALIGN",
        "ROLE_DEFAULT_SEARCH",
        "ROLE_API",
        "ROLE_PUBLISHER",
        "ROLE_USER",
        "ROLE_FORM_CRM",
        "ROLE_ADMIN"
    ],
    "email": "demo@example.org",
    "circles": [],
    "lastLogin": "2026-05-07T21:28:08+02:00",
    "expirationDate": null,
    "language": "fr",
    "locale": "en",
    "localePreferred": "fr",
    "userOptions": {
        "simplified_ui": false,
        "allowed_configure_wysiwyg": false,
        "custom_options": []
    }
}
```

The endpoint fails if the authenticated user is disabled.

## Get enabled user profiles

Use `GET /api/user-profiles` to retrieve all enabled users.

The authenticated user must have one of these roles:

- `ROLE_USER_READ`
- `ROLE_USER_MANAGEMENT`
- `ROLE_ADMIN`

```shell
curl -X GET \
     -H "X-Auth-Token: ${AUTH_TOKEN}" \
     -H 'Accept: application/json' \
     http://localhost:8881/api/user-profiles -w '\n'
```

Successful response:

```json
[
    {
        "id": 1,
        "username": "demo",
        "displayName": "Demo User",
        "roles": ["ROLE_USER"],
        "email": "demo@example.org",
        "circles": ["publisher"],
        "lastLogin": "2026-05-09T10:15:00+02:00",
        "expirationDate": null,
        "language": "en",
        "locale": "en",
        "localePreferred": "en",
        "userOptions": {}
    }
]
```

Disabled users are not included in the response.

## Generate a token for another user

Use `POST /api/user/proxy-authenticate` to generate an authentication token for another enabled
user. This is intended for trusted back-office integrations that need to act as a specific user.

The authenticated user must have `ROLE_USER_MANAGEMENT`.

Request body:

```json
{
    "username": "demo",
    "email": "demo@example.org"
}
```

`username` and `email` are both required by the request validator. When `email` is provided, the API
first tries to find the user by email, then falls back to the username.

Example:

```shell
curl -X POST \
     -H "X-Auth-Token: ${AUTH_TOKEN}" \
     -H 'Content-Type: application/json' \
     -H 'Accept: application/json' \
     http://localhost:8881/api/user/proxy-authenticate -d \
'{
  "username": "demo",
  "email": "demo@example.org"
}' -w '\n'
```

Successful response:

```json
{
    "success": true,
    "token": "EDcTszIHnaaDCpvpi+dJeakj6uOsDqtvSY6rqJyDR3baPpnFA+6u4UAaPcMuJIAfwTs="
}
```

The returned token can be used as an `X-Auth-Token` value for subsequent API calls.

This endpoint fails when:

- the authenticated user does not have `ROLE_USER_MANAGEMENT`;
- `username` or `email` is missing from the JSON body;
- the target user does not exist;
- the target user is disabled or expired.

## Error handling

| Symptom                                   | Check                                                                                |
| ----------------------------------------- | ------------------------------------------------------------------------------------ |
| `401 Unauthorized`                        | The `X-Auth-Token` header is missing or invalid.                                     |
| `403 Forbidden`                           | The authenticated user does not have the role required by the endpoint.              |
| `400 Bad Request` on proxy authentication | The JSON body must contain both `username` and `email`.                              |
| Target user not found                     | Verify the username, email and whether the user exists in Admin.                     |
| Disabled or expired account               | Re-enable the target user or update the expiration date before proxy authentication. |
