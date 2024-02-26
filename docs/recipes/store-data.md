# Store Data

The store data functionalities are a collection of twigs functions that allow to persist data for a given key (key/values database). Those functionalities are available in both elasticms-admin and elasticms-web.

Use cases:
 * A shopping list in the admin
 * A basic pool
 * User shortcuts

## Define the persistence services

You can define multiple databases (services) in order to physically save the store data. The idea is to have several level of store services from volatile with high performance to secure and slow.

In order to keep store data synchronized, the store data services must be clustered. It's important if your application is running multiples containers or if you have multiple applications sharing the data.

Use the `EMS_STORE_DATA_SERVICES` environment variable to define the store data services:

```yaml
EMS_STORE_DATA_SERVICES='[{"type":"cache"},{"type":"db"}]'
```
 
By default, the store data are disabled. But for the elasticms-admin where the data are saved in the admin's DB (and only there).

## Type of persistence services

### DB

The data are saved in the application DB.

This type doesn't have extra parameters.

Example: EMS_STORE_DATA_SERVICES='[{"type":"db"}]'

### Cache

The data are saved in the application cache as defined by the `EMS_CACHE` variable.

This type doesn't have extra parameters.

Example: `EMS_STORE_DATA_SERVICES='[{"type":"cache"}]'`

CAUTION: If your application runs multiple containers, you should use the redis cache. Otherwise the data might not be shared between users 

CAUTION: The cache services should be always be used in combination with another service:

```yaml
EMS_STORE_DATA_SERVICES='[{"type":"cache"},{"type":"db"}]'
```

Parameters:
* `type`: with the value `cache`
* `ttl`: Time to live (in seconds)(optional)

### File storage

The data are saved in a folder.

Parameters:
 * `type`: with the value `fs`
 * `path`: path to a folder where the sata will be stored


Example: 
```yaml
EMS_STORE_DATA_SERVICES='[{"type":"fs", "path":"/opt/store_data"}]'
```

### S3

The data are saved in a S3 bucket.

Parameters:
 * `type`: with the value `s3`
 * `credentials`: S3 credentials e.g. `{"version":"2006-03-01","credentials":{"key":"accesskey","secret":"secretkey"},"region":"us-east-1","endpoint":"http://localhost:9000","use_path_style_endpoint":true}`
 * `bucket`: bucket's name
 * `ttl`: Time to live (by default data stay forever)


Example: 
```yaml
EMS_STORE_DATA_SERVICES='[{"type":"s3", "bucket":"session", "credentials": {"version":"2006-03-01","credentials":{"key":"accesskey","secret":"secretkey"},"region":"us-east-1","endpoint":"http://localhost:9000","use_path_style_endpoint":true}}]'
```

## Using it

Here is a form. It retrieves (or intializes) a `forum` data. And it extracts the `data` value as value form the `data` textarea.

```twig
{% set data = ems_store_read('forum') %}
<form method="post" action="{{ path('emsch_update_store') }}">
    <textarea name="data" cols="10">{{ data.get('[data]') }}</textarea>
    <input name="submit" type="submit" value="Submit">
</form>
```

On submit, a post is sent to the `emsch_update_store` route. Which only allows `POST` method (a non-safe method):

```yaml
emsch_update_store:
    config:
        path: '/post-data'
        controller: 'emsch.controller.router::redirect'
        method: [POST]
    template_static: template/redirects/post-data.json.twig
```

The `forum` data is retrieved from the first data store service, the `data` field is updated. Then the data is saved: updated in all store data services.

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


## Known issues

In combination with varnish the request may not be refreshed.
