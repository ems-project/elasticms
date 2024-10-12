---
editLink: true
lang: en-US
---

# Upgrade 4.x

## version 4.2.x

### Content type roles in twig

```twig
{% if is_granted(contentType.createRole) %} // [!code --]
{% if is_granted(contentType.roles.create) %} // [!code ++]
```

## version 4.x

### Deprecated twig functions

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

## Tips and tricks

### Backward compatibility route to old school assets path

New route to redirect to the new asset's url. Route:

```yaml
redirect_asset:
  config:
    path: 'bundles/emsch_assets/{slug}'
    requirements: { slug: '^.+$' }
    controller: 'emsch.controller.router::redirect'
  template_static: template/redirects/asset.json.twig
```

Template (template/redirects/asset.json.twig):

```twig
{% extends '@EMSCH/template/variables.twig' %}

{% block request -%}
{% apply spaceless %}
    {{ { url: asset(app.request.get('slug'), 'emsch') }|json_encode|raw }}
{% endapply %}
{% endblock -%}
```

### Create an old school "Corresponding revision" in the action menu

Create an action for the content types you want with those parameters:

* Name: `corresponding-revision`
* Label: `Corresponding revision`
* Icon: `Archive`
* Public: unchecked
* Environment: empty
* EDit with WYSIWYG: unchecked
* Role: `User`
* Render option: `Raw HTML`
* Body:

```twig
<a href="{{ path('emsco_data_revision_in_environment', {
    environment: environment.name,
    type: contentType.name,
    ouuid: object._id,
}) }}">
	<i class="fa fa-archive"></i> Corresponding revision
</a>
```
