# Twig functions

| name |  description
| --- | --- | 
| [emsch_add_environment](#emsch_add_environment) | Dynamically add an client helper's environment

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