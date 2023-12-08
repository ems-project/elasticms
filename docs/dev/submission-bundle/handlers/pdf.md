# Pdf handler

Create a pdf that can be used in chained handlers.

## Endpoint

The endpoint needs to be a valid JSON.

Default property values:
```twig 
{
    "filename": "handle.pdf",
    "orientation": "portrait",
    "size": "a4"
}
```

## Message

- Add a block named **pdfHtml** for defining the PDF content. 
- Add a block named **handleResponseExtra** for changing the handler response.

Example:
```twig 
{%- block pdfHtml -%}
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
    </head>
    <body>
        <h1>Content pdf</h1>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean euismod aliquam nisl, 
        ut varius purus vulputate quis. Nulla vehicula consequat ante a facilisis. 
        Nunc tincidunt mauris at tincidunt feugiat. Praesent lacinia lacinia gravida. 
        Lorem ipsum dolor sit amet, consectetur adipiscing elit. 
        Curabitur quis convallis eros. Curabitur scelerisque enim sapien, sed condimentum enim laoreet vel. 
        Ut ut semper urna. In interdum eros vel eros interdum rutrum.</p>   
    </body>
</html>
{%- endblock -%}

{%- block handleResponseExtra -%}
    {# add the base64 encoded pdf to the response #}
    {%- set extra = {
        'pdf': (response.getContent)
    } -%}
    {{- extra|json_encode|raw -}}
{%- endblock -%}
```

## Chaining to email

This example only works if the first submission (index 0) handler is a pdf handler.
Then we can access the response in the a second email handler.

```twig
{% autoescape %}
    {% set message = {
        'from': 'noreply@example.test',
        'subject': 'Test email with pdf',
        'attachments': [
            {
                'base64': request.responses.0.content|raw,
                'filename': request.responses.0.filename|raw,
                'mimeType': 'application/pdf'
            }
        ]
    } %}
{% endautoescape %}
{{ message|json_encode|raw }}
```
