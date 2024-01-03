# Http handler

Send a http request from submission data.

## Endpoint

The endpoint needs to be a valid JSON and the only required property is URL.

The following example will do a POST request to http://example.test/api/form?q=test
```json 
{
  "method": "POST",
  "url": "http://example.test/api/form",
  "query": {
    "q": "test"
  },
  "headers": {
    "Content-Type": "application/json"
  },
  "timeout": 30,
}
```

Authentication

The endpoint JSON allow two authentication properties **auth_basic** and **auth_bearer**

```json 
{
  "auth_basic": "username:password",
  "auth_bearer": "a token enabling HTTP Bearer authorization"
}
```

The authentication parameters can also be fetched using the [connection configuration](../index.md#connections)

```json 
{
  "auth_basic": "{{'apiTest%.%user'|emss_connection}}:{{'apiTest%.%password'|emss_connection}}",
  "auth_bearer": "{{'apiTest%.%token'|emss_connection}}"
}
```

## Message

- Add a block named **requestBody** for defining the HTTP request body. 
- Add a block named **handleResponseExtra** for changing the handler response.

Example:
```twig 
{%- block requestBody -%}
    {%- set message = {'test': 'test'} -%}
    {{- message|json_encode|raw -}}
{%- endblock -%}

{%- block handleResponseExtra -%}
    {%- set extra = {
        'uid': (response.getHttpResponseContentJSON.uid)
    } -%}
    {{- extra|json_encode|raw -}}
{%- endblock -%}
```