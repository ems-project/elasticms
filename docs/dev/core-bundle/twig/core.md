# Core

## emsco_get
Get the [document](https://github.com/ems-project/elasticms/blob/HEAD/EMS/common-bundle/src/Elasticsearch/Document/DocumentInterface.php) from an ems link
Optional you can pass an search environment.

```twig
{% set document = 'page:4930260c-3d40-4db3-ad94-f577c8d9c45e'|emsco_get %}
{{ document.getValue('nl.label') }}

{% set docInLive = 'page:17090dbe-5a61-4277-b691-08a867ad740e'|emsco_get('live'|emsco_get_environment)  %}
```


## emsco_log_[error | warning | notice]
Print flash message, usefull in post processing
```twig
{{ 'Example print error flash'|emsco_log_error }}
{{ 'Example print warning flash'|emsco_log_warning }}
{{ 'Example print notice flash'|emsco_log_notice }}
```
## emsco_generate_email
Generate an email and use [emsco_send_email](#emsco_send_email) for sending the email.
```twig
{% set mailBody %}
  <h1>Example email</h1>
  <p>example ...</p>
{% endset %}

{% set email = emsco_generate_email('test title') %}
{% do email.to('test@example.com').html(mailBody|format) %}
{% do emsco_send_email(email) %}
```

## emsco_send_email
Send an email generated with [emsco_generate_email](#emsco_generate_email).
Default value for from is `ems_core.from_email` and `%ems_core.name%` parameter.

```twig
{% set email = emsco_generate_email('example send') %}
{% do email.to('test@example.com').text('Body text') %}
{% do emsco_send_email(email) %}
```

## emsco_skip_notification
Can be used in notification in order to not send the notification and display a warning message.

```twig
{{ emsco_skip_notification() }}
```
The warning message can be defined:

```twig
{{ emsco_skip_notification('The title field is not provided, the request for publication can not be send.') }}
```

## emsco_form

Handle the current request with the form identified by its name. It allows to generate form in view, action or dashboard:

```twig
{% set form = emsco_form('user') %}
{% set formView = form.createView %}

{{ form_start(formView) }}
    {{ form_row(attribute(formView, 'user')) }}
    <div>
        <button type="submit" class="btm btn-primary">Filter</submit>
    </div>
{{ form(formView) }}

{% if form.valid %}
    {{ form.data.user|json_encode }}
{% endif %}
```

## emsco_display

Returns a string representation for a elasticSearch document, revision or EMS link.

Pass a symfony [expression](https://symfony.com/doc/current/components/expression_language.html) or define a default in the contentType field `display`.

This filter replaces the filter `|data_label`.

Context for the expression: 
- rawData: array containing the rawData from document or revision
- userLocale: the preferred locale of the user, fallback environment variable `EMSCO_FALLBACK_LOCALE`

```twig
{# Print the label in the users preferred locale, fallback to label_fr #}
{% set document = 'page:e6f73dd73a5a3f5336bd3fe52d0304b26e437f34'|emsco_get %}
{{ document|emsco_display("(rawData['label_'~userLocale] ?? rawData['label_fr'])")

{# display from emsLink and using contentTypes defined display value #}
{{ 'page:e6f73dd73a5a3f5336bd3fe52d0304b26e437f34'|emsco_display }}
```

## emsco_save_contents

Allow to save a contents into storage services as an asset. Examples bellow.

In this example the contents of a forged URL is saved as an asset in storage services:
```twig
{% if finalize and _source.default_image is not defined %}
    {% set response = ems_http("https://domain.tld/fr/base_url/#{_source.uuid}/original.jpg") %}
    {% if response.getStatusCode() == 200 %}
        {{ emsco_save_contents(response.getContent(), "#{_source.name}.jpg", response.getHeaders()['content-type'][0])|json_encode|raw }}
    {% endif %}
{% endif %}
```


In this example an array of identifiers (strings) will be converted into a multiple FileFieldType:
```twig
{% if finalize and (_source.images is not defined or _source.images|length == 0) %}
    {% set images = [] %}
    {% for hash in _source.Stack|default([]) %}
        {% set response = ems_http("https://domain.tld/base-url/#{hash}/original.jpg") %}
        {% if response.getStatusCode() == 200 %}
            {% set images = images|merge([emsco_save_contents(response.getContent(), "#{_source.name}-#{loop.index}.jpg", response.getHeaders()['content-type'][0])]) %}
        {% endif %}
    {% endfor %}
    {{ images|json_encode|raw }}
{% endif %}
```

A forth optional argument allow to defined the file's type:
 * 0: Cache
 * 1: Config
 * 2: Asset (default value)
 * 3: Backup
