# Old school hierarchy content type to JSON Nested menu

In the past you might have configured some nested content type with a `children` data link. The one that you handle with the `Hierarchical view` admin's view.

## Create a document's action

 - Go to the `menu` content type's action in the admin.
 - Add a `Embed` action

The following boy will generate a JSON ready to be pasted in a new conet type's document:

```twig
{% macro recu(contentType, structure, menu, menus) %}
    {% for item in menu.children|default([]) %}
        {% set splited = item|split(':') %}
        {% if splited|first == contentType.name %}
            {% set child = menus[splited|last] %}
            {% do structure.addChildByArray({
                id: splited|last,
                label: child.menu_root,
                type: 'menu',
                object: {
                    label: child.menu_root,
                    fr: {
                        show: child.show_fr|default(false),
                        title: child.title_fr|default(''),
                    },
                    nl: {
                        show: child.show_nl|default(false),
                        title: child.title_nl|default(''),
                    },
                    en: {
                        show: child.show_en|default(false),
                        title: child.title_en|default(''),
                    },
                    de: {
                        show: child.show_d|default(false),
                        title: child.title_de|default(''),
                    },
                }
            }) %}
            {{ _self.recu(contentType, structure.getItemById(splited|last), child, menus) }}
        {% else %}
            {% set data = item|data %}
            {% if data %}
                {% do structure.addChildByArray({
                    id: splited|last,
                    label: data.title_fr|default(data.label_fr|default()),
                    type: 'link',
                    object: {
                        label: data.title_fr|default(data.label_fr|default()),
                        link: item,
                        fr: {
                            title: data.title_fr|default(data.label_fr|default()),
                        },
                        nl: {
                            title: data.title_nl|default(data.label_nl|default()),
                        },
                        en: {
                            title: data.title_en|default(data.label_en|default()),
                        },
                        de: {
                            title: data.title_de|default(data.label_de|default()),
                        },
                    }
                }) %}
            {% endif %}
        {% endif %}
    {% endfor %}
{% endmacro %}





{# initiate an empty JSON nested menu #} 
{% set structure = '{}'|ems_json_menu_nested_decode %}

{# gets all menu document in order to get them one by one #} 
{% set menus = {
    type: 'menu',
    index: contentType.environment.alias,
    size: 500,
}|search.hits.hits|ems_array_key('_id')|map(p => p._source) %}


{# recursively walk in the menu documents and fill the JSON Nested object #} 
{{ _self.recu(contentType, structure, source, menus) }}


{# render the JSON ready to be pasted in the revision JSON edit #} 
<pre>{{ {
    menu_json: structure.toArrayStructure()|json_encode,
    menu_root: source.menu_root
}|json_encode(constant('JSON_PRETTY_PRINT')) }}</pre>
```

