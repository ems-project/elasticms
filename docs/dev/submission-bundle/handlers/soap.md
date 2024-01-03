# Soap handler

Send a soap request from submission data.

## Endpoint

The endpoint needs to be a valid JSON. The only required option is the **operation**.
For the options no validation is executed, see [php soapClient](https://www.php.net/manual/en/soapclient.soapclient.php) documentation for more information

Default property values:
```twig 
{
    "wsdl": null,
    "options": []
}
```

## Message

- Add a block named **arguments** for defining the arguments passed to the operation call. 
- Add a block named **handleResponseExtra** for changing the handler response.

Example:
```twig 
{%- block arguments -%}
{{- {'name': 'test'}|json_encode|raw -}}
{%- endblock -%}

{%- block handleResponseExtra -%}
    {# add the soap response to the response #}
    {%- set extra = {
        'soap_response': (response.getSoapResponse)
    } -%}
    {{- extra|json_encode|raw -}}
{%- endblock -%}
```