# Service Now handler

Sends data to a Service Now REST endpoint. 

## Endpoint

The endpoint field contains the host, table, user, and password to connect to the REST endpoint. 
Connection parameters are fetched using the [connection configuration](../index.md#connections)
```twig 
//endpoint field
{
    "host": "https://example.service-now.com",
    "table": "table_name",
    "username": "{{'connection-name%.%user'|emss_connection}}",
    "password": "{{'connection-name%.%password'|emss_connection}}"
}
```

If you don't use default endpoints, you can specify them :
```twig 
//endpoint field
{
    "host": "https://example.service-now.com",
    "table": "table_name",
    "bodyEndpoint": "/api/now/v1/table",
    "attachmentEndpoint": "/api/now/v1/attachment/file",
    "username": "{{'connection-name%.%user'|emss_connection}}",
    "password": "{{'connection-name%.%password'|emss_connection}}"
}
```

## Message

The message field contains the data to be send to the REST endpoint, for example:
```twig 
//message field
{
    "body": {
        "title": "Unknown",
        "name": "{{ data.name }}",
        "firstname": "{{ data.firstname }}",
        "email1": "{{ data.email }}"
    }
}
```

To include one or multiple attachments to your email, declare them as shown below.
```twig 
//message field
{
    "body": {
        "title": "Unknown",
        "name": "{{ data.name }}",
        "firstname": "{{ data.firstname }}",
        "email1": "{{ data.email }}"
    },
    "attachments": {
        "file_1": {
            "pathname": "{{ data.file_1.getPathname()|json_encode }}",
            "originalName": "{{ data.file_1.getClientOriginalName() }}",
            "mimeType": "{{ data.file_1.getClientMimeType() }}"
        }
    }
}
```