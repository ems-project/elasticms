# JsonMenuNested

JsonMenuNestedStructure is a kind of elasticms field serializing a JSON in a string. That JSON has a specific structure. It's an array of JsonMenuNestedItem; A JsonMenuNestedItem has the following fields:
 - `id` : contain a unique id (string) for the JsonMenuNestedItem (unique in the structure only)
 - `label` : contain a non-mandatory string labelling the JsonMenuNestedIte
 - `type` : string identifying the subform type of this JsonMenuNestedItem 
 - `object` : contain the JSON of the subform
 - `children` : contains a non-mandatory array of JsonMenuNestedItem (recursive structure)

# Twig filter ems_json_menu_nested_decode

With this filter you can parse a JsonMenuNested field and get a [JsonMenuNested object](../src/Json/JsonMenuNested.php)

## JsonMenuNested object

### Searching for a object value

Searching inside a JsonMenuNested you can use the search method on the object.

Arguments:
- `propertyPath`: the property path defined for symfony's [property accessor](https://symfony.com/doc/current/components/property_access.html)
- `value`: the value to match
- `type`: optional argument, for limiting the search on only items of the type passed

It returns an iterable result, if you are only intressed in one object use twig filter first.

> Example searching for a item which contains page_nl with the falue 'example:ouuid'
```twig
{% set structureJson = '{...}'|ems_json_menu_nested_decode %}
{% set item = structure.search("[page_nl]", "example:ouuid")|first %}
```
> Example searching for page items from the author John
```twig
{% set structureJson = '{...}'|ems_json_menu_nested_decode %}
{% set johnsPages = structure.search("[author]", "John", "page") %}
{% for page in johnsPages %}...{% endfor %}
```

### Generate a breadcrumb

JsonMenuNested contains a breadcrumb method useful in order to generate breadcrumb:

```twig
        {% if pageInStructure %}
            {% set structure = attribute(pageInStructure.structure, 'structure_'~locale)|default('{}')|ems_json_menu_nested_decode %}
            {% if attribute(structure, 'breadcrumb') is defined %}
                {% for item in structure.breadcrumb(pageInStructure.uid) %}
                    <li class="breadcrumb-item">
                        {%  if attribute(paths, [pageInStructure.sid, item.id]|join(':'))|default(false) %}
                            <a href="{{ path('match_all', {path: attribute(paths, [pageInStructure.sid, item.id]|join(':')) }) }}">
                                {{ item.label }}
                            </a>
                        {% else %}
                            {{ item.label }}
                        {%  endif %}
                    </li>
                {% endfor %}
            {% else %}
                <li class="breadcrumb-item">Please update to skeleton 3.7.8 to get a breadcrumb</li>
            {% endif %}
        {%  endif %}
```

You can also get the breadcrumb in the reverse order:
```twig
    {% for item in structure.breadcrumb(pageInStructure.uid, true) %}
        ...
    {% endfor %}
```