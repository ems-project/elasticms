# Multipart handler

Send a http request from submission data in a multipart format.

## Endpoint

The endpoint needs to be a valid JSON and the only required property is URL.

The following example will do a POST request to http://example.test/api/form?q=test
```json 
{
  "method": "POST",
  "url": "http://example.test/api/form",
  "timeout": 10,
}
```

## Message

This should return a json that will be converted into a multipart object. 

File fields can be serialized as is it. See the `contact_attachment`field in the following example.

Example:
```twig 
{% set subject = config.elements.contact_subject.choices.getLabel(data.contact_subject) %}

{{ {
    lastName: data.contact_last_name,
    firstName: data.contact_first_name,
    file: data.contact_attachment,
    fromAddress: data.contact_email,
    formIdentifier: 'contact_form',
    subject: subject,
    text: data.contact_message
}|json_encode|raw }}
```