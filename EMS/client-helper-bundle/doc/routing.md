# Routing

## Pdf generation

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

## Spreadsheet generation

For enabling spreadsheet generation use the **emsch.controller.spreadsheet** controller
```yaml
test_xlsx:
  config:
    path: /test.xlsx
    controller: 'emsch.controller.spreadsheet'
  template_static: template/test/xlsx.json.twig
  order: 4
```

In Twig you can set the spreadsheet options by generating a JSON
```twig
{% set config = {
    "filename": "custom-filename",
    "disposition": "attachment",
    "writer": "xlsx",
    "sheets": [
        {
            "name": "Sheet 1",
            "rows": [
                ["A1", "A2"],
                ["B1", "B2"],
            ]
        },
        {
            "name": "Sheet 2",
            "rows": [
                ["A1", "A2"],
                ["B1", "B2"],
            ]
        },
    ]
} %}

{{- config|json_encode|raw -}}
```

Two writer are supported:
 - `xlsx`: Generate a Microsoft Excel file
 - `csv`: Generate a CSV file

Add style on Cell are available [See on EMSCommonBundle documentation](https://github.com/ems-project/EMSCommonBundle/tree/master/doc/spreadsheet.md)

## Route to an asset

A route may also directly returns an asset:
```json
{
    "path": "/{_locale}/example-pdf/{filename}",
    "controller": "emsch.controller.router:asset",
    "requirements": {
      "_locale": "fr|nl"
    }
}
```

The template must returns a json like this one:

```json
{
  "hash": "aaaaabbbbbcccccdddd111112222",
  "config": {
    "_mime_type": "application/pdf",
    "_disposition": "inline"
  },
  "filename": "demo.pdf"

}
```

 - `hash`: Asset's hash
 - `config`: Config's hash or config array (see common's processor config)
 - `filename`: File name

This json may also contain an optional `immutable` boolean option [default value = false]:

```json
{
  "hash": "aaaaabbbbbcccccdddd111112222",
  "config": {
    "_mime_type": "application/pdf",
    "_disposition": "inline"
  },
  "filename": "demo.pdf",
  "immutable": true
}
```

## Profiler

You can disable the profiler for a specific route, by setting **_profiler** to false.

```yaml
example:
    config:
        path: '/example-no-profiler'
        defaults: { _profiler: false }
    template_static: template/page.html.twig
```


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

### Return HTTP codes:
* **201**: On the first request when nothing is cached, this means the sub-request is started
* **202**: If the sub-request is still running
* **200**: The sub-request was finished and the response comes from the cache
* **500**: An exception has occurred and this is now in cache. Check the error logs.
  * Max memory limit reached? 
  * Max execution limit reached, you can increase this on the route.

### Note

For now everything is cached using the symfony cache, this means if we restart the server the cache is cleared.
The timestamp in the route can be the max _finalization time of your content types, this way the cache will not be used if the content has changed.

This setup only works with php-fpm (no windows) because we continue the process after the response was send (onKernelTerminate).
> Internally, the HttpKernel makes use of the fastcgi_finish_request PHP function. This means that at the moment, only the PHP FPM server API is able to send a response to the client while the server's PHP process still performs some tasks. With all other server APIs, listeners to kernel.terminate are still executed, but the response is not sent to the client until they are all completed.


## Search route

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

## Redirect route

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
 - `url`: the target url to redirect to
 - `status`: the HTTP return's code. Default value: 302
 - `message`: A 404 message. Default value 'Page not found'

If the url parameter is not defined, the controller will throw a 404 with the message parameter.
