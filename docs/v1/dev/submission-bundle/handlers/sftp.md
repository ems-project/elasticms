# Sftp handler

Transport files over sftp. 
The files can be from previous handlers (Pdf, ..) or form files.

## Endpoint

The endpoint needs to be a valid JSON, only the property **host** is required
The privateKey must be a base64 decode ppk key file.

Default property values:
```twig 
{
    "host": "127.0.0.1",
    "port": 22,
    "username": "",
    "password": "",
    "privateKey": "",
    "root": "/",
    "timeout": 10
}
```

## Message

- Add a block named **files** for defining the files to send. 
- Add a block named **handleResponseExtra** for changing the handler response.

Example:
```twig 
{%- block files -%}
    {# first 2 entries are form attachments files and the first one is place in a folder called test #}
    {# response 0 is a pdf response and we can also forward the base64 #}
    {%- set files = [
        {
            'path': 'test/' ~ data.attachments.0.getClientOriginalName(),
            'content_path': data.attachments.0.getPathname(),
        },
        {
            'path': data.attachments.1.getClientOriginalName(),
            'content_path': data.attachments.1.getPathname(),
        },
        {
            'path': responses.0.filename,
            'content_base64': responses.0.content,
        }
    ] -%}
    {{-  files|json_encode|raw -}}
{%- endblock -%}

{%- block handleResponseExtra -%}
    {# add the transported files to the response #}
    {%- set extra = {
        'pdf': (response.getTransportedFiles)
    } -%}
    {{- extra|json_encode|raw -}}
{%- endblock -%}
```