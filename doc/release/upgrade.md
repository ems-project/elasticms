# Upgrade

## Admin

### Migrate your content to elasticsearch 7

- Remove the standard filter from all analyzers
- Check external content types have a _contenttype field
- Recompute all your content types, this will hightlight all post-processes and computed that you have to update.

### Corresponding revision action

In earlier versions of ElasticMS, there was an action called "Corresponding
Revision." If this feature is still required, we now have the ability to
configure it ourselves.

To implement this, create an action for the desired content types using the
following parameters:

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

## Web

### Support old assets path

To ensure backward compatibility, you may want to continue supporting the old
asset path. A new route can be set up to redirect to the updated asset URL. The
route is as follows:

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
