# Login to the Admin API

## Get an authentication token

In order to use the admin API you must have an authentication token. To get one you have two
options.

The first option is to go to the user admin interface (`/user/`), locate your user and click on
`API key` > `Generate API key for this user?`. The page will reload and a notice message will shows
the authentication token.

The other option is to login via a curl call:

```shell
curl -X POST http://localhost:8881/auth-token -d '{
  "username": "demo",
  "password": "demo"
}'
```

In case of success, you retrieve the key in the `authToken` field of the JSON response.

## Test an authentication token

The endpoint `/api/test` allows you to test an authentication token:

```shell
curl -X GET -H 'X-Auth-Token: EDcTszIHnaaDCpvpi+dJeakj6uOsDqtvSY6rqJyDR3baPpnFA+6u4UAaPcMuJIAfwTs='  http://localhost:8881/api/test
```
