# JsonMenu

THIS TYPE OF ELAsTICMS FILED IS DEPRECATED, SEE [JSON MENU NESTED FIELD](json-menu-nested.md).

JsonMenuStructure is a kind of elasticms field serializing a JSON in a string. That JSON has a specific structure. It's an array of JsonMenuItem; A JsonMenuItem has the following fields:
 - `id` : contain a unique id (string) for the JsonMenuItem (unique in the structure only)
 - `label` : contain a non-mandatory string labelling the JsonMenuIte
 - `type` : string identifying the subform type of this JsonMenuItem 
 - `object` : contain the JSON of the subform
 - `children` : contains a non-mandatory array of JsonMenuItem (recursive structure)

# Twig filter ems_json_menu_decode

With this filter you can parse a JsonMenu field and get a [JsonMenu object](https://github.com/ems-project/elasticms/tree/4.x/EMS/common-bundle/src/Json/JsonMenu.php)

## JsonMenu object

### Generate a breadcrumb

JsonMenu contains a breadcrumb method useful in order to generate breadcrump:

```twig
        {% if pageInStructure %}
            {% set structure = attribute(pageInStructure.structure, 'structure_'~locale)|default('{}')|ems_json_menu_decode %}
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
