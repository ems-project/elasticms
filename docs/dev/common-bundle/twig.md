# TOC

* [Twig Functions](#twig-functions)
  * [ems_html](#ems_html)
  * [ems_nested_search](#ems_nested_search)
  * [ems_image_info](#ems_image_info)
  * [ems_uuid](#ems_uuid)
  * [ems_store_read](#ems_store_read)
  * [ems_store_save](#ems_store_save)
  * [ems_store_delete](#ems_store_delete)
* [Twig filters](#twig-filters)
  * [ems_anti_spam](#ems_anti_spam)
  * [ems_html_encode](#ems_html_encode)
  * [ems_markdown](#ems_markdown)
  * [ems_stringify](#ems_stringify)
  * [ems_asset_average_color](#ems_asset_average_color)
  * [ems_replace_regex](#ems_replace_regex)
  * [ems_html_decode](#ems_html_decode)
  * [ems_hash](#ems_hash)
  * [format_bytes](#format_bytes)
  * [ems_ascii_folding](#ems_ascii_folding)
  * [ems_template_exists](#ems_template_exists)



# Twig Functions

## ems_html

Will return a instance of [EmsHtml](https://github.com/ems-project/elasticms/tree/4.x/EMS/common-bundle/src/Common/Text/EmsHtml.php) which is an extension of Twig\Markup.
You can easyly replace text, remove html tags, printUrls.

### Example
> This example is usefull for generating pdf's.
- This wil replace 'example' by 'EXAMPLE' and 'searchX' by 'replaceX'
- Remove all a tags if the attribute text contains 'mywebsite.com' and will keep the content
- Print urls will transform the a tags based on the format (default: ':content (:href)')

```twig
{% set description = ems_html(page.description) %}
{% do description
    .replace({ 'example': 'EXAMPLE', 'searchX': 'replaceX' })
    .removeTag('a', ".*?mywebsite.com.*?", true)
    .printUrls(':content (<i><u>:href</u></i>)')
%}
{{ description|emsch_routing_config(emschRoutingConfig) }}
```

## ems_nested_search

Search all choices of a nested field, and this function will runtime cache the result.

### Arguments
- **alias**: name of the elasticsearch alias
- **contentTypeNames**: string or array of contentType names
- **nestedFieldName**: namem of the nestedField
- **search**: key/value array, key is nested property name, value is search value

### Example

The following example will build 3 variables by using the *ems_nested_search*, the choices will only build once and cached.

```twig
{% set example1 = ems_nested_search('my_alias', 'structure', 'documents', {'id': 'd2214354-a946-4e60-8e1a-921a643df3ad'}) %}
{% set example2 = ems_nested_search('my_alias', 'structure', 'documents', {'id': '9d501b0f-13c1-42e1-a4ae-242650dc6dbd'}) %}
{% set example3 = ems_nested_search('my_alias', 'structure', 'documents', {'id': '0186c0ac-4d8f-4755-a8f0-afa9fb86d599'}) %}
```

## ems_image_info
Retrieve information (size, resolution, mime type and extension) about an image, based on its hash.
If the hash can not be recognized as an image or does not exist, **_null_** is returned.

### Arguments
- **hash**: hash(sha1) of the image

Where _'4ef5796bb14ce4b711737dc44aa20bff82193cf5'_ is the hash of a jpg
```twig
{{ ems_image_info('4ef5796bb14ce4b711737dc44aa20bff82193cf5') }}

// will return

{
    'width': 128,
    'height': 245,
    'mimeType': 'image/jpg',
    'extension': jpg,
    'heightResolution': 96,
    'widthResolution': 96
}
```

## ems_uuid

Generate a version 4 (random) UUID. [More info](https://uuid.ramsey.dev/en/stable/rfc4122/version4.html).

````twig
{{ ems_uuid() }} {# displays: 21.16 KB #}
````

## ems_store_read

Retrieve, or initialize, an associative array (a.k.a. store data) for a given key from the first Store Data Services where the key is available. See the [Stora Data documentation](../../recipes/store-data.md) for more details.

````twig
{% set data = ems_store_read('forum') %}
<form method="post" action="{{ path('emsch_update_store') }}">
    <textarea name="data" cols="10">{{ data.get('[data]') }}</textarea>
    <input name="submit" type="submit" value="Submit">
</form>
````


## ems_store_save

Update a store data in all store data services. This function must be called in a non-safe request (i.e. `POST` or `PUT`). See the [Stora Data documentation](../../recipes/store-data.md) for more details.

```yaml
emsch_update_store:
    config:
        path: '/post-data'
        controller: 'emsch.controller.router::redirect'
        method: [POST]
    template_static: template/redirects/post-data.json.twig
```

````twig
{%- block request %}
{% apply spaceless %}
  {% set data = ems_store_read('forum') %}
  {% do data.set('[data]', app.request.get('data')) %}
  {% do ems_store_save(data) %}

  {{ {
    url: path('home'),
  }|json_encode|raw }}
{% endapply %}
{% endblock request -%}
````


## ems_store_delete

Delete a store data in all store data services. This function must be called in a non-safe request (i.e. `POST` or `PUT`). See the [Stora Data documentation](../../recipes/store-data.md) for more details.

```yaml
emsch_delete_store:
    config:
        path: '/delete-post-data'
        controller: 'emsch.controller.router::redirect'
        method: [POST]
    template_static: template/redirects/delete-post-data.json.twig
```

````twig
{%- block request %}
{% apply spaceless %}
  {% do ems_store_delete('forum') %}

  {{ {
    url: path('home'),
  }|json_encode|raw }}
{% endapply %}
{% endblock request -%}
````

# Twig filters

## ems_anti_spam

For obfuscation of pii on your website when the user agent is a robot.

Implementation details are based on http://www.wbwip.com/wbw/emailencoder.html using `ems_html_encode`.
The following data can be obfuscated (even inside a wysiwyg field):

- emailadress `no_reply@example.com`
````twig
{{- 'no_reply@example.com'|ems_anti_spam -}}
````
- phone number in `<a href="tel:____">`
````twig
{{- '<a href="tel:02/123.50.00">repeated here, the number will not be encoded</a>'|ems_anti_spam -}}
````
- custom selection of pii using a span with class "pii"
````twig
{{- '<span class="pii">02/123.50.00</span>'|ems_anti_spam -}}
````

See unit test for more examples.

Note: Phone numbers are only obfuscated if they are found inside "tel:" notation. When a phone is used
outside an anchor, the custom selection of pii method should be used.

Note: When using custom selection of pii, make sure that no HTML tags are present inside the pii span.

Note: the custom selection pii span is only present in the backend. The obfuscation method removes the span
tag from the code that is sent to the browser.

## ems_html_encode

You can transform any text to its equivalent in html character encoding.

````twig
{{- 'text and téxt'|ems_html_encode -}}
````

See unit test for more examples.

## ems_markdown

Filter converting a Markdown text into an HTML text following the GitHub standards. 

```twig
{{ source.body|ems_markdown }}
```

## ems_stringify

Filter converting any scalar value, array or object into a string.

```twig
{{ someObject|ems_stringify }}
{{ someArray|ems_stringify }}
```
## ems_asset_average_color

Filter returning the average color, in CSS rgb format, of a passed hash.

I.e.
```twig
{{ 'ed266b89065e74483248da7ff71cb80e3cca40a5'|ems_asset_average_color}}
```

Will return `#666666`. It might be useful in order to define a background color:

```twig
{{ ems_asset_path({
    filename: 'avatar.jpg',
    sha1: avatarHash,
    mimetype: 'image/jpeg',
}, {
    _config_type: 'image',
    _resize: 'fill',
    _background: avatarHash|ems_asset_average_color,
    _width: 400,
    _height: 600,
    _quality: 80,
    _get_file_path: localPath,
}) }}
```
```twig
style="background-color: {{ avatarHash|ems_asset_average_color }}"
 ```

## ems_replace_regex

Apply php **preg_replace** function on a text string. All possible exceptions are catched and logged as warning.

Example replace all ems links by a span tag.
```twig
{% set text %}
    <h1>Example</h1>
    <p><a href="ems://object:page:dabc33113a53866fe1a1443b42a4c16d1f4bc138">Homepage</a></p>
    <p><a href="ems://object:page:7cc6310cc57818bb571e706ede0a4c10623b430c">News</a></p>
    <p>the end</p>
{% endset %}
{{ text|ems_replace_regex('/<a.*?href="ems:\\/\\/\\S+".*?>(.*?)<\\/a>', '<span>$1</span>')|raw }}
```

## ems_html_decode

Convert HTML entities to their corresponding characters

The following example will generate a `è` :

```twig
{{ '&grave;'|ems_html_decode|json_encode|raw }}
```

### Other parameters:

 - flags: [refers to html_entity_decode's flags paramter](https://www.php.net/manual/en/function.html-entity-decode.php), default value `ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5`
 - encoding: [defining the encoding used when converting characters](https://www.php.net/manual/en/function.html-entity-decode.php), default value `"UTF-8""`


## ems_hash

Generate a hash value from the message. See the [PHP hash function](https://php.net/manual/en/function.hash.php).

```twig
{{ 'foobar'|ems_hash }}
{{ 'foobar'|ems_hash('sha1') }} {# outputs 8843d7f92416211de9ebb963ff4ce28125932878 #}
```

### Other parameters:

- algo: [refers to the hash's algo parameter](https://php.net/manual/en/function.hash.php), default value `null` which means that the `ems_common.hash_algo` will be used
- binary: [refers to the hash's binary parameter](https://php.net/manual/en/function.hash.php), default value `false`. When set to `true`, outputs raw binary data

## format_bytes

Useful to generate a human readable file size from an interger.

````twig
{{ 21666|format_bytes }} {# displays: 21.16 KB #}
````

A second 'precision' parameter can be defined:

````twig
{{ 21666|format_bytes(1) }} {# displays: 21.2 KB #}
````

## ems_ascii_folding

Convert UTF-8 characters in string by their equivalent in the "old" ascii table:

````twig
{{ 'Chemin d''accès: î$]&²'|ems_ascii_folding }} {# displays: Chemin d acces: i$]&² #}
````

It's useful if you want to sort an array regardless accented characters:

````twig
{% set sortedArray = notSortedArray|sort((a, b) => a|ems_ascii_folding <=> b|ems_ascii_folding) %}
````

## ems_template_exists

Test if a template exists or not. This function works with all kind of templates:

````twig
{% if not ems_template_exists("@EMSCH/template/page/#{name}.html.twig") %}
  {% do emsch_http_error(404, 'Page not found') %}
{% endif %}
````
