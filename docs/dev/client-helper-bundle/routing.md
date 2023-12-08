# Routing

<!-- TOC -->
* [Routing](#routing)
  * [Options](#options)
  * [Config Defaults](#config-defaults)
  * [Controllers](#controllers)
    * [Redirect controller](#redirect-controller)
    * [Search controller](#search-controller)
    * [Pdf controller](#pdf-controller)
    * [Spreadsheet controller](#spreadsheet-controller)
    * [Asset controller](#asset-controller)
  * [EMSCH cache (sub-request)](#emsch-cache-sub-request)
<!-- TOC -->

## Options

Skeleton routes can have the following options

* `config`: required: define the symfony routing config (path, defaults, requirements) 
* `template_static`: optional: define a template path
* `template_source`: optional: define a property path in the document received from the route query. example *[template]*
* `query`: optional: search a document, if not found 404.
* `index_regex`: optional: define an index regex for executing the query

The following route demonstrates the power of skeleton routes.
Inside `template_static|query|index_regex` options we can replace by route params, pattern %param%.

```yaml
home:
    config:
        path: '{_locale}/{alias}/page/{id}'
        defaults: { _locale: 'nl' }
        requirements: { _locale: 'nl|fr', alias: 'snapshot1|snapshot2' }
    template_static: template/homepage_%alias%.html.twig
    query: '{"query":{"bool":{"must":[{"term":{"_contenttype":{"value":"page"}}},{"term":{"_id":{"value":"%id%"}}}]}},"size":1}'
    index_regex: demo_ma_%alias%
```

## Config Defaults

| Name           | value      | Description                                                                                                                                          |
|----------------|------------|------------------------------------------------------------------------------------------------------------------------------------------------------|
| _profiler      | true/false | You can disable the profiler for a specific route, by setting **_profiler** to false.                                                                |
| _authenticated | true/false | The AuthenticatedListener will throw an **AccessDeniedException** if the user is not fully authenticated. See [security](/elasticms-web/security.md) |


## Controllers

### Redirect controller

A route can be defined in order to redirect the request to another url. An easy approach is by using the redirect Symfony controller:

````yaml
favicon_ico:
    config:
      path: /favicon.ico
      controller: 'Symfony\Bundle\FrameworkBundle\Controller\RedirectController::urlRedirectAction'
      defaults: { permanent: true, path: '/bundles/assets/static/icon64.png' }
````

But if you need logic to specify the redirect url you may use the emsch redirect controller's function:

````yaml
favicon_ico:
  config:
    path: /favicon.ico
    controller: 'emsch.controller.router::redirect'
  template_static: template/ems/redirect_favicon.json.twig
````

And in the redirect_favicon.json.twig template:

````twig
{% apply spaceless %}
      {% set assetPath = emsch_assets_version('240c99f842c118a733f14420bf40e320bdb500b9') %}
      {{ {'url': asset('static/favicon-96x96.png', 'emsch'), 'status': 301 }|json_encode|raw }}
{% endapply %}
````

The template's response should be a JSON containing those optional parameters:
- `path`: path for returning BinaryFileResponse
- `url`: the target url to redirect to, required if path is not defined
- `status`: the HTTP return's code. Default value: 302
- `message`: A 404 message. Default value 'Page not found'
- `headers`: array for defining the response headers

If the url parameter is not defined, the controller will throw a 404 with the message parameter.

Instead of redirecting via an HTTP redirect response you can also directly return an assets. To do so, instead of giving a path to redirect to, give a path to a file:

````twig
{% extends '@EMSCH/template/variables.twig' %}

{%- block request %}
    {% apply spaceless %}
        {{ {
            path: emsch_asset('img/head/icon.png', {
                _config_type: 'image',
                _width: 128,
                _height: 128,
                _quality: 0,
                _get_file_path: true,
            }),
        }|json_encode|raw }}
    {% endapply %}
{% endblock request -%}
````

In this previous example we assume that a call to the `emsch_assets_version` function has been made in the `template/variables.twig` template.


This controller can also be used to redirected to another controller (as a subrequest). 
In this example we internally redirect a route into the FileController in order to exploit to range headers for a media file content type.

```yaml
emsch_media_file:
    config:
        path: '/media-files{path}'
        requirements: { path: .+ }
        controller: 'emsch.controller.router::redirect'
    query: '{"query":{"bool":{"must":[{"terms":{"_contenttype":["media_file"]}},{"terms":{"media_path":["%path%"]}}]}},"size":1}'
```

```twig
{%- block request %}
    {% apply spaceless %}
        {{ {
            controller: 'EMS\\CommonBundle\\Controller\\FileController::resolveAsset',
            path: {
               fileField: source.media_file,
            },
        }|json_encode|raw }}
    {% endapply %}
{% endblock request -%}
```

This redirect may take 3 parameters:

 * `controller`: string identifying the controller where the request must be redirected. This parameter is mandatory.
 * `path`: associative array containing the named parameters to pass to the controller's method. Default value `[]`.
 * `query`: associative array containing the non-mandatory parameters (such those passed via the request to the controller). Default value `[]`.



### Search controller

See the [search documentation](./search.md) fo more information.

I.e.:
````yaml
emsch_search:
    config:
        path: { en: search, fr: chercher, nl: zoeken, de: suche }
        defaults: {
           search_config:{
             "types": ["page", "publication", "slideshow"],
             "fields": ["_all"],
             "sizes": [10],
             "sorts": {
               "recent": {"field": "published_date", "order": "desc", "unmapped_type": "date", "missing":  "_last"}
             }
           }
        }
        controller: 'emsch.controller.search::handle'
````

### Pdf controller

For enabling pdf generation use the **emsch.controller.pdf** controller
```json
{
    "path": "/{_locale}/example-pdf",
    "controller": "emsch.controller.pdf",
    "requirements": {
      "_locale": "fr|nl"
    }
}
```
In Twig you can set/override the pdf options with custom meta tags in the head section
```html
<head>
    <title>Title</title>
    <meta name="pdf:filename" content="example.pdf" />
    <meta name="pdf:attachment" content="true" />
    <meta name="pdf:compress" content="true" />
    <meta name="pdf:html5Parsing" content="true" />
    <meta name="pdf:orientation" content="portrait" />
    <meta name="pdf:size" content="a4" />
</head>
```

### Spreadsheet controller

For enabling spreadsheet generation use the **emsch.controller.spreadsheet** controller
```yaml
test_xlsx:
  config:
    path: /example-spreadsheet
    controller: 'emsch.controller.spreadsheet'
  template_static: template/test/spreadsheet.json.twig
  order: 4
```

Add style on Cell are available [See on EMSCommonBundle documentation](/dev/common-bundle/spreadsheet.md)

Example writer `xlsx`
```twig
{% set config = {
    "filename": "example",
    "disposition": "attachment",
    "writer": "xlsx",
    "sheets": [
        { "name": "Sheet 1", "rows": [ ["A1", "A2"], ["B1", "B2"] ] },
        { "name": "Sheet 2", "rows": [ ["A1", "A2"], ["B1", "B2"] ] },
    ]
} %}
{{- config|json_encode|raw -}}
```

Example writer `csv`
```twig
{% set config = {
    "filename": "example",
    "disposition": "attachment",
    "writer": "csv",
    "csv_separator": ",",
    "sheets": [
        { "rows": [ ["A1", "A2"], ["B1", "B2"], ["C1", "C2"] ] }
    ]
} %}
{{- config|json_encode|raw -}}
```

### Asset controller

Routes can also return an assets, generated by a template containing json.

```yaml
example_asset:
    config:
        path: /example-asset/{filename}
        controller: 'emsch.controller.router::asset'
    template_static: template/example_asset.json.twig
```

```twig
{% set assetConfig = {
  "hash": "c3499c2729730a7f807efb8676a92dcb6f8a3f8f",
  "config": {
    "_mime_type": "application/pdf",
    "_disposition": "inline"
  },
  "filename": "demo.pdf",
  "headers": {
    "X-Robots-Tag": "noindex"
  },
  "immutable": false
} %}
{{- assetConfig|json_encode|raw -}}
```

 - `hash`: Asset's hash
 - `config`: Config's hash or config array (see common's processor config)
 - `filename`: File name
 - `headers`: Associative with response headers. 
 - `immutable`: optional for defining if the asset is immutable [devault value = false]

## EMSCH cache (sub-request)

For routes that **not** return a streamable response we can enable caching that is generated in a subRequest.
The pdf controller already has support for streams and can fallback to response when using _emsch_cache.

```yaml
pdf_example:
    config:
        path: '/my-pdf-example/{_locale}/{id}/{timestamp}'
        requirements: { _locale: fr|nl, id: .+, timestamp: .+ }
        defaults: { 
          _emsch_cache: { key: 'pdf_example_%_locale%_%id%_%timestamp%', limit: 300 } 
        }
        controller: emsch.controller.pdf
    query: '{"query":{"bool":{"must":[{"term":{"_contenttype":{"value":"page"}}},{"term":{"id":{"value":"%id%"}}}]}}}'
    template_static: template/my-pdf-example.html.twig
```

Return HTTP codes:
* **201**: On the first request when nothing is cached, this means the sub-request is started
* **202**: If the sub-request is still running
* **200**: The sub-request was finished and the response comes from the cache
* **500**: An exception has occurred and this is now in cache. Check the error logs.
  * Max memory limit reached? 
  * Max execution limit reached, you can increase this on the route.


> For now everything is cached using the symfony cache, this means if we restart the server the cache is cleared. The 
> timestamp in the route can be the max _finalization time of your content types, this way the cache will not be used 
> if the content has changed.

> This setup only works with php-fpm (no windows) because we continue the process after the response is finished
> (onKernelTerminate). 
> 
> Internally, the HttpKernel makes uses of the fastcgi_finish_request PHP function. This means 
> that for now, only the PHP FPM server API can send a response to the client while the server's PHP 
> process still performs some tasks. 
> 
> With all other server APIs, listeners to `kernel.terminate` are still executed, but the response is not sent to 
> the client until they are all completed.

