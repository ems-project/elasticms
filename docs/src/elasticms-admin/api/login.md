# Admin API Login

The Admin API requires an authentication token. For authenticated API requests, prefer the standard
`Authorization: Bearer <token>` header.

The historical `X-Auth-Token` header is still supported for backward compatibility.

There are two ways to generate a token:

- from the Admin UI, for an existing user;
- from the `/auth-token` endpoint, with user credentials.

## Generate a token from the Admin UI

Open the user management page (`/user/`), select the user, then click `API key` >
`Generate API key for this user?`.

After the page reloads, the generated token is displayed in a notice message. The current user must
have either `ROLE_ADMIN` or `ROLE_USER_MANAGEMENT`.

## Generate a token from the API

Send a `POST` request to `/auth-token` with a JSON body containing the username and password:

```shell
curl -X POST \
     -H 'Content-Type: application/json' \
     -H 'Accept: application/json' \
     http://localhost:8881/auth-token -d \
'{
  "username": "demo",
  "password": "demo"
}' -w '\n'
```

On success, the response contains the token in the `authToken` field:

```json
{
    "acknowledged": true,
    "authToken": "EDcTszIHnaaDCpvpi+dJeakj6uOsDqtvSY6rqJyDR3baPpnFA+6u4UAaPcMuJIAfwTs=",
    "success": true
}
```

Store the token in an environment variable before calling the other API endpoints:

```shell
export AUTH_TOKEN='EDcTszIHnaaDCpvpi+dJeakj6uOsDqtvSY6rqJyDR3baPpnFA+6u4UAaPcMuJIAfwTs='
```

If authentication fails, the endpoint returns `401 Unauthorized` with `success` set to `false`.

## Test a token

Use `/api/test` to verify that a token is valid:

```shell
curl -X GET \
     -H "Authorization: Bearer ${AUTH_TOKEN}" \
     -H 'Accept: application/json' \
     http://localhost:8881/api/test -w '\n'
```

A valid token returns `200 OK` and a JSON body with `success` set to `true`.

Legacy clients can still send the same token with:

```shell
-H "X-Auth-Token: ${AUTH_TOKEN}"
```
