# Twig functions

| Name                                            | Description                                        |
|-------------------------------------------------|----------------------------------------------------|
| [emsch_add_environment](#emsch_add_environment) | Dynamically add an client helper's environment     |
| [emsch_asset](#emsch_asset)                     | Ability to use asset's processor on website assets |




## emsch_add_environment

This function is useful to be used in elasticms admin's actions, views and dashboards in order to dynamically load a Client helper environment.

Once loaded it will be possible to load templates from a content type or use the emsch_routing and emsch_routing_config filters:

Example in an elasticms admin's view. As the environment's name is by default used as alias loading a environment might be as short as:
```twig
{% do emsch_add_environment(environment.alias) %}
```

Or it can be fully defined. See the [environment config](../environment.md):

```twig
{% do emsch_add_environment(environment.name, {
    alias: environment.alias,
    remote_cluster: 'other_cluster',
}) %}
```

N.B. This function does not have output


## emsch_asset

This function is working mostly like the regular `asset` twig function. Except that this one need an asset config array as second parameter.

I.e.:

```twig
<img src="{{ emsch_asset('img/head/icon.png', {
        _config_type: 'image',
        _width: 310,
        _height: 150,
        _quality: 0,
        _resize: 'fill',
        _gravity: 'west',
        _background: '#2e2e2e',
    }) }}">
```

Instead of generating a regular website asset path it will generate a processed asset path like this:`file/f14f3b6b044a4a37dcabae029746235ab3459ee9/processor/icon.png`.

Useful in order to avoid managing assets in multiple sizes in your source base.

