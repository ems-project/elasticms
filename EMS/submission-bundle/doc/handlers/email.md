# Email handler

Sends an email to the email address defined in the endpoint field. 

## Endpoint

```twig 
incoming-email@example.com
```

Or dynamically:
```twig
{%- spaceless -%}
{%- set destination = "incoming-email@example1.com" -%}

{%- if data.my_destination_field is same as("my_destination_value") -%}
    {%- set destination = "incoming-email@example2.com" -%}
{%- endif -%}

{%- endspaceless -%}
{{- destination -}}
```

## Message
Email sender, subject, and body are defined in the message field using the "from", "subject", and "body" keys respectively. The output should be a json object.
```twig 
{% autoescape %}
{% set email = {
    "from": "noreply@example.com",
    "subject": "Form submission from website",
    "body": "foobar"
} %}
{% endautoescape %}
{{ email|json_encode|raw }}
```

The message can access the filled in data of the form, for example submitted fields "email", "name", "firstname". Use the following approach if you want to include newlines in your email body.
```twig
{% autoescape %}
{% set body %}
    Email {{ data.email }}
    Name {{ data.name }}
    Firstname {{ data.firstname }}
{% endset %}

{% set email = {
    "from": data.email,
    "subject": "Email Form subject",
    "body": body
} %}
{% endautoescape %}
{{ email|json_encode|raw }}
```

If your body contains HTML structured text, you have to pass the content-type option text/html in the email object. By default text/plain is used.
```twig
{% autoescape %}
{% set body %}
    Email {{ data.email }}
    Name {{ data.name }}
    Firstname {{ data.firstname }}
{% endset %}

{% set email = {
    "from": data.email,
    "subject": "Email Form subject",
    "body": body,
    "content-type": "text/html"
} %}
{% endautoescape %}
{{ email|json_encode|raw }}
```

### Attachments

Use the **formData** helper object to retreive all files that are attached to the form submission.
You can override the default values for each file using the `map` filter as shown below.

```twig 
{% autoescape %}
{% set body %}
    Email {{ data.email }}
    Name {{ data.name }}
    Firstname {{ data.firstname }}
{% endset %}

{% set email = {
    "from": data.email,
    "subject": "Email Form subject",
    "body": body
} %}

{%- set files = {} -%}
{% for file in formData.allFiles|map(v => v.toArray) %}
    {%- set files = files|merge({(file.form_field):{
        filename: file.filename|ems_webalize,
        originalName: file.filename|ems_webalize,
        mimeType: file.mimeType,
        pathname: file.pathname,
    } }) -%}
{% endfor %}

{% if files|length > 0 %}
    {% set email = email|merge({"attachments": files}) %}
{% endif %}

{% endautoescape %}
{{ email|json_encode|raw }}
```
