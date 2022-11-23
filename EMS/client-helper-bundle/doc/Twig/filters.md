# Twig filters

| name |  description
| --- | --- | 
| [emsch_routing](#emsch_routing) | Transform ems links inside a string 
| [emsch_routing_config](#emsch_routing_config) | Transform ems links inside a string with extra config
| [emsch_data](#emsch_data) | [**DEPRECATED**] Get array data from ems link 
| [emsch_get](#emsch_get) | Get document object from ems link

## emsch_routing
Transforms emsLinks (ems://object:type:id) inside a string by defined templates. 
These templates normally follow the following naming convention: **{type}.ems_link.twig**

The filter accepts two optional parameters:
> - **locale**: is forwarded to the render template
> - **baseUrl**: the result of the rendering will be prefix with this baseUrl

```twig
{% set content %}
Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce auctor scelerisque neque, eget fringilla dui pellentesque at. 
Etiam quis ex sed velit fermentum blandit id <a href="ems://object:page:AXY5brhavwK7S-Z8saw1">page1</a> leo. Sed non erat mattis, facilisis ipsum non, varius urna. 
Ut aliquam enim <a href="ems://object:page:AXY5brhavwK7S-Z8saw1">page2</a> dui dignissim tincidunt. Praesent efficitur ipsum ac eros lobortis, at mollis nulla placerat.
{% endset %}

{{ content|emsch_routing }}
```

## emsch_routing_config
Works like [emsch_routing](#emsch_routing) but only accepts 1 optional parameter called config.

Possible config properties:
> - **locale**: is forwarded to the render template (will be added to the context array)
> - **baseUrl**: the result of the rendering will be prefix with this baseUrl
> - **dynamic_types**: allow emsLinks with these types and do NOT fetch the document
> - **context**: an array for extending the context passed to the templates

```twig
{% set content %}
Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce auctor scelerisque neque, eget fringilla dui pellentesque at. 
Etiam quis ex sed velit fermentum blandit id <a href="ems://object:my_type:AXY5brhavwK7S-Z8saw1">custom type</a> leo. Sed non erat mattis, facilisis ipsum non, varius urna. 
Ut aliquam <a href="ems://object:page:AXY5brhavwK7S-Z8saw1">page</a> ac dui dignissim tincidunt. Praesent efficitur ipsum ac eros lobortis, at mollis nulla placerat.
{% endset %}

{{ content|emsch_routing_config({ 
    'locale': 'de', 
    'baseUrl': '/test/',
    'dynamic_types': ['my_type'],
    'context': {
        'extraParam': 'test'
    }
}) }}
```

## emsch_data
Deprecated because it return an array and we always have todo |first for getting the first entry.
```twig
{% set page = "ems://page:AXY5brhavwK7S-Z8saw1"|emsch_data|first|default(false) %}
```

## emsch_get
Returns a object of the type [Document](https://github.com/ems-project/EMSCommonBundle/blob/master/src/Common/Document.php)
```twig
{% set page = "ems://page:AXY5brhavwK7S-Z8saw1"|emsch_get %}
```
 