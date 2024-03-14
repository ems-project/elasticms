# Upgrade

  * [Switch to CK Editor 5](#switch-to-ck-editor-5)
  * [version 6.0.x](#version-514x)
  * [version 5.14.x](#)
  * [version 5.7.x](#version-57x)
  * [version 5.3.x](#version-53x)
  * [version 4.2.x](#version-42x)
  * [version 4.x](#version-4x)
  * [Tips and tricks](#tips-and-tricks)

## Switch to CK Editor 5

Require ElasticMS version >= 6.0.0

First activate Bootstrap 5 theme with the environment variable `EMSCO_TEMPLATE_NAMESPACE=EMSAdminUI/bootstrap5`

Then go to your `WYSIWYG` > `WYSIWYG styles sets` configs and change the `attributes.class` by a `classes` attribute

So

```json
[{
    "name": "Dekstop only",
    "element": "div",
    "attributes": {
      "class": "desktop-only row"
    }
}]
```

becomes

```json
[{
    "name": "Dekstop only",
    "element": "div",
    "classes": [
      "desktop-only",
      "row"
    ]
}]
```

Check then [CK Editor styles configuration](https://ckeditor.com/docs/ckeditor5/latest/features/style.html#configuration) for more details.
But defining other HTML attributes than the class attribute is not as easy as it was with CKE4.


And for the `WYSIWYG` > `WYSIWYG profiles` the config must be recreate from scratch.
But basically you can override every default [CK Editor config](https://github.com/ems-project/elasticms/blob/1ea0749ec813ac7bd3afd29a8ce9520654d9a97c/EMS/admin-ui-bundle/assets/js/core/helpers/editor.js#L80. 
Check the [CK Editor builder](https://ckeditor.com/ckeditor-5/online-builder/).
Here is an example.

```json
{
  "ems": {
    "paste": true
  },
  "toolbar": {
    "items": [
      "undo",
      "redo",
      "style",
      "heading",
      "|",
      "bold",
      "italic",
      "bulletedList",
      "numberedList",
      "removeFormat",
      "|",
      "outdent",
      "indent",
      "|",
      "link",
      "imageUpload",
      "insertTable",
      "mediaEmbed",
      "specialCharacters",
      "|",
      "findAndReplace",
      "sourceEditing"
    ],
    "shouldNotGroupWhenFull": true
  }
}
```



## version 6.0.x

### Renamed embed methods in web/skeleton templates

All controller methods have lost any trailing `Action`

* `emsch.controller.embed::renderBlockAction` must be replaced by `emsch.controller.embed::renderEmbed`
* `emsch.controller.embed::renderHierarchyAction` must be replaced by `emsch.controller.embed::renderHierarchy`

E.g.:

```twig
{{ render(controller('emsch.controller.embed::renderHierarchy', {
    'template': '@EMSCH/template/menu.html.twig',
    'parent': 'emsLink',
    'field': 'children',
    'depth': 5,
    'sourceFields': [],
    'args': {'activeChild': emsLink, 'extra': 'test'}
} )) }}
```

### Routes removed

* `template.index` must be replaced by `ems_core_action_index`
* `template.add` must be replaced by `ems_core_action_add`
* `template.edit` must be replaced by `ems_core_action_edit`
* `template.remove` must be replaced by `ems_core_action_delete`

### Deprecated twig filters

* `array_key` must be replaced by `ems_array_key`
* `format_bytes` must be replaced by `ems_format_bytes`
* `locale_attr` must be replaced by `ems_locale_attr`
* `data` must be replaced by `emsco_get`
* `url_generator` must be replaced by `ems_webalize`
* `get_environment` must be replaced by `emsco_get_environment`
* `get_content_type` must be replaced by `emsco_get_contentType`
* `data_label` must be replaced by `emsco_display`
* `emsch_ouuid` must be replaced by `ems_ouuid`
* `array_intersect` must be replaced by `ems_array_intersect`
* `merge_recursive` must be replaced by `ems_array_merge_recursive`
* `inArray` must be replaced by `ems_in_array`
* `soapRequest` must be replaced by `emsco_soap_request`
* `all_granted` must be replaced by `emsco_all_granted`
* `one_granted` must be replaced by `emsco_one_granted`
* `in_my_circles` must be replaced by `emsco_in_my_circles`
* `data_link` must be replaced by `emsco_data_link`
* `i18n` must be replaced by `emsco_i18n`
* `internal_links` must be replaced by `emsco_internal_links`
* `displayname` must be replaced by `emsco_display_name`
* `get_field_by_path` must be replaced by `emsco_get_field_by_path`
* `get_revision_id` must be replaced by the function `emsco_get_revision_id`

### Deprecated twig function

* `cant_be_finalized` must be replaced by `emsco_cant_be_finalized`
* `get_default_environments` must be replaced by `emsco_get_default_environment_names`
* `get_content_types` must be replaced by `emsco_get_content_types`
* `sequence` deprecated and must be replaced by `emsco_sequence`

### New dynamic mapping config which change the elasticsearch indexes

Before version 6 it was not possible to define elasticsearch dynamic mapping config. In other words, before version 6, every fields present in a document, that aren't strictly defined in the content type, a mapping was automatically guessed by elasticsearch.

Since version 6 the default dynamic mapping config has changed. New fields are ignored. These fields will not be indexed or searchable, but will still appear in the _source field of returned hits. These fields will not be added to the mapping, and new fields must be added explicitly into the content type.

You can reactivate the dynamic mapping with this environment variable:  `EMSCO_DYNAMIC_MAPPING='true'`. But it's not recommended. Check the [EMSCO_DYNAMIC_MAPPING documentation](elasticms-admin/environment-variables.md#emscodynamicmapping)

## version 5.14.x

* All tasks records will be **deleted** after deployment
  * Because we had to upgrade the database schema. [#778](https://github.com/ems-project/elasticms/pull/778)

* If you are using revision versions, you should run ```ems:environment:updatemetafield``` after deployment.

## version 5.7.x

* Added twig function [ems_template_exists](./site-building/twig.md#ems_template_exists)
* Added probe routes `/_readiness` and `/_liveness` for admin and web
* Added header support for [Redirect controller](./dev/client-helper-bundle/routing.md#redirect-controller).
* Added multipart support for [s3](./dev/common-bundle/storages.md#s3)
* Added infinity scrolling for [MediaLibrary](./dev/core-bundle/twig/component.md#media-library)
* Added draggable file upload for [MediaLibrary](./dev/core-bundle/twig/component.md#media-library)
* Added `ems:admin:restore` command
  ```bash
  ems:admin:restore --configs --force
  ems:admin:restore --documents --force
  ```
* Added `filename` option for upload assets command
  ```bash
  emsch:local:upload-assets --filename=/opt/src/local/skeleton/template/asset_hash.twig
  ```
  
  ```twig
  {% set assetPath = emsch_assets_version(include('@EMSCH/template/asset_hash.twig')) %}
  ```
* Deprecated ~~cant_be_finalized~~ use `emsco_cant_be_finalized`

## version 5.3.x

### Deprecated emsch_add_environment 

In dashboards/views and action, we call `emsch_add_environment` for rendering a template from emsch.
If elasticms-admin defines `EMSCH_ENV` and `EMSCH_ENVS`, this is not needed anymore.

```.env
EMSCH_ENV='preview'
EMSCH_ENVS='{"preview":{"alias":"example_preview", "router": false}}' 
```

EMSCH_ENV will mark the preview environment as default, the following can also be done:
```.env
EMSCH_ENVS='{"preview":{"alias":"example_preview", "default": true, "router": false}}' 
```

`Router` false, will disable the clientHelperBundle router the default environment. 
Maybe the skeleton has a match all route defined.

After defining remove the following line from all contentType(s) and dashboard(s).
```twig
{% do emsch_add_environment('preview'|get_environment.alias) %} 
```

## version 4.2.x

### Content type roles in twig
Replace `is_granted(contentType.createRole)` → `is_granted(contentType.roles.create)`
* createRole → roles.create
* editRole → roles.edit

## version 4.x

### Deprecated twig functions
* replace `{% spaceless %}` by `{% apply spaceless %}`
* replace `{% endspaceless %}` by `{% endapply %}`
* replace `{% for key, item in array if test %}` by  `{% for key, item in array|filter(key, item => test) %}`
* replace `transchoice` by `trans`
  * I.e. replace `{{ 'search.results'|transchoice(results.hits.total.value|default(response.total)) -}}`
  * by `{{ 'search.results'|trans({'%count%': results.hits.total.value|default(response.total)}) -}}`

### Asset custom twig functions
* replace `{{ emsch_assets(assets) }}` or `{%- do emsch_assets(assets) -%}` by `{%- set assetPath = emsch_assets_version(assets) -%}`
* replace `{{ assets('resource') }}?{{ assets_hash }}` by `{{ assets('resource', 'emsch') }}`

### Email custom twig functions
```twig
{%- set email = emsco_generate_email(subjectMail) -%}
{%- set email = email.setTo(toMail) -%}
{%- set email = email.setBody(bodyMail, 'text/html') -%}
{%- set email = email.setFrom(fromMail) -%}
{{- emsco_send_email(email) -}}
```
→
```twig
{%- set email = emsco_generate_email(subjectMail) -%}
{%- set email = email.to(toMail) -%}
{%- set email = email.html(bodyMail) -%}
{%- set email = email.from(fromMail) -%}
{{- emsco_send_email(email) -}}
```

### Misc
* replace `/\.hits\.total/` by `{% var.hits.total.value|default(var.hits.total) %}`
  * replace `/\[\'hits\'\][\'total\']/` by `var['hits']['total']['value']|default(var['hits']['total'])`
* remove the template environment
  * align template and preview for route, template and label
  * switch default environment `emsco:content:swith template preview`
* Do a force push to override the document
  * Keep in mind that all ouuids have changed, check in your content types for datalink to template documents
  * Rollback, in the routes.yaml, static templates have been replaced by their OUUID

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
