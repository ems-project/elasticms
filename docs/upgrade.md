# Upgrade

  * [version 5.19.x](#version-519x)
  * [version 5.17.x](#version-517x)
  * [version 5.15.x](#version-515x)
  * [version 5.14.x](#version-514x)
  * [version 5.7.x](#version-57x)
  * [version 5.3.x](#version-53x)
  * [version 4.2.x](#version-42x)
  * [version 4.x](#version-4x)
  * [Tips and tricks](#tips-and-tricks)

## version 5.19.x

* Xliff command options have been updated
  * The `--filename` option in the `emsco:xliff:extract` command has been replaced by a `--basename` option and does not contains a path anymore, just a file basename.

    Example replace ```emsco:xliff:extract live '{}' nl de title --filename=/tmp/pages-nl-to-de.xlf```
     by ```emsco:xliff:extract live '{}' nl de title --basename=pages-nl-to-de.xlf```
  * In case of warning or error in the `emsco:xliff:update` command the report file is no more available locally. The report is upladed in the admin's storages. The directly get a link to the report you need to specify a `--base-url` option.

    Example ```emsco:xliff:update /tmp/pages-nl-to-de.xlf --base-url=https://my-admin.my-project.tld```
* You should not specify a folder where to expand website assets in the `emsch_assets_version` twig function, in this case the function returns `null`.
  * By default, if you specify `null` (e.g. `{% do emsch_assets_version(include('@EMSCH/template/asset_hash.twig'), null) %}`) as second arguments, the `emsch` assets will have a an url like `/bundle/253b903b1fb3ac30975ae9844a0352a65cdcfa3d/site.css` which urls will be resolved by the route `EMS\CommonBundle\Controller\FileController::assetInArchive`
  * It's also possible the defined you own route for assets in archive, if you route is not immutable (does not contain the archive hash) you must specify the `maxAge` argument (by default it's set to one week): 
```yaml
emsch_demo_asset_in_archive:
  config:
    path: '/assets_in_archive/{path}'
    requirements: { path: .* }
    defaults: { hash: 253b903b1fb3ac30975ae9844a0352a65cdcfa3d, maxAge: 3600 }
    controller: 'EMS\CommonBundle\Controller\FileController::assetInArchive'
```

## version 5.17.x

* Check routes single colon is deprecated

  Example replace ```emsch.controller.router:redirect``` by ```emsch.controller.router::redirect```

## version 5.15.x

* Form routes are available inside the elasticms-admin
  * Skeleton no longer need to proxy the form routes for making form working inside channels. [#848](https://github.com/ems-project/elasticms/pull/848)
* The form debug routes are no longer 'dev' mode only
* The form debug routes are available inside the elasticms-admin

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
