# Twig functions

## emsch_assets

This function is deprecated and should be replaced by `emsch_assets_version`

## emsch_assets_version

This function specify the hash of the archive containing the assets of the website (JS, CSS, ...)

The second argument should be set to `null` as that argument has been deprecated in 5.19.x.

```twig
{%- do emsch_assets_version('hash', null) -%}
```

This function can be called only one time per Twig rendering. Otherwise, an error will be thrown.

Example base template.
```twig
<link rel="stylesheet" href="{{ asset('css/app.css', 'emsch') }}">
```

When you are developing you may want to use asset in a local folder (in the `public` folder) instead of a zip file. In order to do so, use the `EMSCH_ASSET_LOCAL_FOLDER` environment variable


## emsch_unzip

Like emsch_assets this will unzip a file into the required saveDir.
The function will also return an array, on success this array will contain the file path as key 
and a Symfony\Component\Finder\SplFileInfo object as value. 
```twig
{% set images = emsch_unzip('cf3adfdc15eae63f2040cf2c737ccb37a06ee1f5', 'example-images') %}
{% for path, info in images %}
    <img src="{{ path }}" alt="{{ info.filename }}" />
{% endfor %}
```

## ems_search_config

For accessing the search configuration (filters) before doing the actual search.
````twig
{% set search = emsch_search_config() %}
{% set choices = search.getFilter('name').getChoices() %}
````

In a search result page the search is passed to the template.
````twig
{% set activeFilters = search.getActiveFilters() %}
{% set choices = search.getFilter('name').getChoices() %}
````

Sorting example
````twig
{% if search.sorts|length > 0 %}
    <div class="custom-control custom-radio">
      <input type="radio" id="sortby_relevance" name="s" value="" class="custom-control-input" {{ null == search.sortBy ? 'checked="checked"' }}>
      <label class="custom-control-label" for="sortby_relevance">{{ 'sortby_relevance'|trans }}</label>
    </div>
    {% for s, sort in search.sorts %}
        <div class="custom-control custom-radio">
          <input type="radio" id="sortby_{{ s }}" name="s" value="{{ s }}" class="custom-control-input" {{ sort.field == search.sortBy ? 'checked="checked"' }} >
          <label class="custom-control-label" for="sortby_{{ s }}">{{ ('sortby_'~s)|trans }}</label>
        </div>
    {% endfor %}
{% endif %}
````

# Twig embed

## render hierarchy

```twig
{{ render(controller('emsch.controller.embed::renderHierarchyAction', {
    'template': '@EMSCH/template/menu.html.twig',
    'parent': 'emsLink',
    'field': 'children',
    'depth': 5,
    'sourceFields': [],
    'args': {'activeChild': emsLink, 'extra': 'test'}
} )) }}
```
Example menu.html.twig
```twig
<ul>   
    {% for a, childA in hierarchy.children %}
        <li {% if childA.active %}class="active"{% endif %}>  
            {{ childA.source._contenttype ~ ':' ~ childA.id }}
            {% if childA.children|length > 0 %}      
                <ul>
                    {% for b, childB in childA.children %}
                        <li {% if childB.active %}class="active"{% endif %}>{{ childB.source._contenttype ~ ':' ~ childB.id }}</li>
                    {% endfor %}
                </ul>
            {% endif %}
        </li>
    {% endfor %}
</ul>
```
Example menu.html.twig

## Fragment

From a design perspective it might be useful to isolate part of the DOM in sub-requests. For instance a block "last post" is the same on all post and on the homepage. 
By isolating this in a subrequest with `render` you will have a more readable code.

What you can do is to just import a twig. But if you use the render function instead, you'll be able to cache this specific piece of DOM and reduce the required resources:

```twig
{{ render(path('last_post', { last: 5 })) }}
```


Off course, you have to declare the `fragment_footer` route. You may want to hide those subrequest for the outside by using the embed's fragment function:

```twig
    {{ render(controller('emsch.controller.embed::fragment', {
        template: '@EMSCH/template/fragments/last_post.html.twig',
        context: {
            trans_default_domain: trans_default_domain,
            last: 5,
        },
    })) }}
```

Not need to define a route with this solution.

And if you have a reverse proxy in front of your application supporting [ESI](https://symfony.com/doc/current/http_cache/esi.html), i.e. varnish,
you can switch to `render_esi`:


```twig
    {{ render_esi(controller('emsch.controller.embed::fragment', {
        template: '@EMSCH/template/fragments/last_post.html.twig',
        context: {
            trans_default_domain: trans_default_domain,
            last: 5,
        },
    })) }}
```

Here the reverse proxy will calls the sub-requests by himself. So, globally, requests will use less memory. And the reverse proxy will also be able to cache part of the DOM. 
I.e. the footer, which is basically always the same, won't have to be generated for each query. Even if the cache's TTL is short, it will help to absorb charge's peaks with less resources. 

## Cacheable fragment

Some repetitive computes can also be cached. For that you may call the `cacheableFragment` method: 


```twig
    {% set structure = render(controller('emsch.controller.embed::cacheableFragment', {
        cacheType: 'menu',
        template: '@EMSCH/template/fragments/structure_from_menu_documents.json.twig',
        context: {
            trans_default_domain: trans_default_domain,
            last: 5,
        },
    }))|ems_json_decode %}
```

The `cacheType` parameter is the content type's name used to invalidate the response in cache (if something has been updated) 


## emsch_http_error

Cancel the curent rendering an return the specified HTTP return code. Useful to redirect to another URL.

Return a 404:

```twig
{% do emsch_http_error(404, 'Page not found') %}
```

Redirect to Symfony:

```twig
  {% do emsch_http_error(307, "Temporary Redirect to #{url}", {
      location: url,
  }) %}
  ```

