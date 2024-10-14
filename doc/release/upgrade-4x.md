# Upgrade 4.x

## version 4.2.x

Content type roles in twig

```twig
{% if is_granted(contentType.createRole) %} // [!code --]
{% if is_granted(contentType.roles.create) %} // [!code ++]
```

## version 4.x

Deprecated twig functions

```twig
{% spaceless %} // [!code --]
{% apply spaceless %} // [!code ++]

{% endspaceless %} // [!code --]
{% endapply %} // [!code ++]

{% for key, item in array if test %} // [!code --]
{% for key, item in array|filter(key, item => test) %} // [!code ++]

{{ 'search.results'|transchoice(response.total) -}} // [!code --]
{{ 'search.results'|trans({'%count%': response.total }) -}} // [!code ++]

{% set totalHits = results.hits.total %} // [!code --]
{% set totalHits = results.hits.total.value %} // [!code ++]

{% do emsch_assets(assets) %}// [!code --]
{% do emsch_assets_version(assets) %} // [!code ++]

"{{ assets('js/app.js') ? assets_hash }}" // [!code --]
"{{ assets('js/app.js', 'emsch) }}" // [!code ++]
```

```twig
{% set email = emsco_generate_email(subjectMail) -%}
{% set email = email.setTo(toMail) %} // [!code --]
{% set email = email.setBody(bodyMail, 'text/html') %} // [!code --]
{% set email = email.setFrom(fromMail) %} // [!code --]
{% set email = email.to(toMail) %} // [!code ++]
{% set email = email.html(bodyMail) %} // [!code ++]
{% set email = email.from(fromMail) %} // [!code ++]
{% do emsco_send_email(email) %}
```
