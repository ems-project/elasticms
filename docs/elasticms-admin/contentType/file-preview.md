# File preview

In elasticMS admin, when you have a file field, the system tries to generate a thumbnail, if the file is an image.

This mechanism appears in both the revision form and the revision view.

By default, the admin applies this image config in order to generate the preview:

```json
{
    "_config_type": "image",
    "_quality": 0,
    "_height": 200,
    "_width": 300,
    "_resize": "fill",
    "_gravity": "center"
}
```

## Override the preview config

By defining a `preview` asset config you'll be able to override that config for all content at once.

```dotenv
EMSCO_ASSET_CONFIG='{"preview":{"_config_type": "image","_quality": 0,"_height": 225,"_width": 400,"_resize": "fillArea","_gravity": "center", "_radius": 12}}'
```

## Override the preview config for one specific field

You can use the `EMSCO_ASSET_CONFIG` environment variable to define mutiple asset configs. Then you can refer to those config's names in the Display option of any file field configs.


```dotenv
EMSCO_ASSET_CONFIG='{"news":{"_config_type": "image","_quality": 0,"_height": 225,"_width": 400,"_resize": "fillArea","_gravity": "center", "_radius": 12}}'
```


## Using the asset configs in actions, views and dashboards

With the twig `emsco_asset_path` function you can generate a thumbnail using one of the defined asset configs:

```html
<img src="{{ emsco_asset_path(source.image, 'preview') }}">
```

