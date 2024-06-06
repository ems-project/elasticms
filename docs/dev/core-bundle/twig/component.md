# Twig Components

Overview core twig components. 
Now we are using Symfony ux [twig components](https://symfony.com/bundles/ux-twig-component/current/index.html), 
in the feature we may use [live components](https://symfony.com/bundles/ux-live-component/current/index.html).  

The following components can be used in views/actions and dashboards.

<!-- TOC -->
* [Twig Components](#twig-components)
  * [Json Menu Nested](#json-menu-nested)
    * [Implementation (json-menu-nested)](#implementation-json-menu-nested)
    * [Javascript and CSS (json-menu-nested)](#javascript-and-css-json-menu-nested)
    * [Templating (json-menu-nested)](#templating-json-menu-nested)
  * [Media library](#media-library)
    * [Implementation (media-library)](#implementation-media-library)
    * [Templating (media-library)](#templating-media-library)
<!-- TOC -->

!> Both components support overwriting the blocks, so the emsch templates must be loaded with `EMSCH_ENVS`.
Using `emsch_add_environment` twig function, will not work. see [Upgrade 5.3.x](/upgrade.md#version-53x)

## Json Menu Nested

The json menu nested component for working with a [json menu nested](/dev/common-bundle/json-menu-nested.md).
This component replaces [emsco_json_menu_nested](/dev/core-bundle/twig/json-menu-nested.md) twig function.

Improvements over the emsco_json_menu_nested: 
* Pure vanilla JS (no jQuery for sorting)
* Users can work simultaneously
* The component does not know the full structure, each action is handled independent
* Move items between to two lists
* Collapse all with long press on the collapse button
* Better templating

### Implementation (json-menu-nested)

```twig
{% set document = 'structure:ZBgj1IkBt_Q7j_c9Rj2a'|emsco_get %}
{{ component('json_menu_nested', {
    'id': 'example-structure',
    'ems_link': document.emsLink,
    'field_path': '[structure]'
}) }}
```

| Property         | Default                         | Description                                                                                     |
|------------------|---------------------------------|-------------------------------------------------------------------------------------------------|
| `id`             |                                 | **required** html id attribute                                                                  |
| `ems_link`       |                                 | **required** emsLink to the object                                                              |
| `field_path`     |                                 | **required** property path to the json menu nested field                                        |
| `columns`        | ```json[{name: 'structure'}]``` | Json array of columns (name required, width default 200). Title column will always be available |
| `template`       |                                 | see [templating](#templating-json-menu-nested)                                                  |
| `context`        |                                 | key/value array that will be passed to all twig blocks                                          |
| `context_block`  |                                 | The passed block name, will be rendered on each request.                                        |
| `active_item_id` |                                 | Highlight the item with the passed id                                                           |

### Javascript and CSS (json-menu-nested)

The following example, will on load:
* change the default modal size to lg
* get item `4f7dc5b6-54ff-4861-998a-bfc691ba2d12`

```javascript
window.addEventListener('emsReady', function () {
    const jsonMenuNested = window.jsonMenuNestedComponents['example-id-component'];
    jsonMenuNested.modalSize = 'lg';
    jsonMenuNested.itemGet('4f7dc5b6-54ff-4861-998a-bfc691ba2d12').then((json) => {console.debug(json); });
});
```

The following example shows the css variable that can be changed.
```css
.json-menu-nested-component {
    --jmn-color-bg: #F0F0F0;
    --jmn-color-border: #d7d7d7;
    --jmn-color-bg-active: #F0F8FF;
    --jmn-color-light: #F8F8F8;
    --jmn-border-radius: 5px;
    --jmn-gap: 10px;
    --jmn-icon-size: 30px;
}
```

### Templating (json-menu-nested)

Overwriting the blocks can be done by defining a value for the `template` option. Use `_self` for overwriting in the same template.
Important blocks that start with `_jmn` can't be overwritten.

See the default [template](https://github.com/ems-project/elasticms/blob/HEAD/EMS/core-bundle/src/Resources/views/components/json_menu_nested/template.twig) for all available blocks.

Example:
- Add a new column named 'example'
- In the block `jmn_column_example` we use variable `column_label` passed through the context config
- In the block `jmn_cell_example` we use the variable `page_label` passed through the block `example_context`

The `context_block` will be rendered on each draw of the component. After each action a redraw is done.

```twig
{% block dashboardBody %}
  {% set document = 'structure:ZBgj1IkBt_Q7j_c9Rj2a'|emsco_get %}
  {{ component('json_menu_nested', {
      'id': 'example-structure',
      'ems_link': document.emsLink,
      'field_path': '[structure]',
      'template': _self,
      'context': { 'column_label': 'EXAMPLE' },
      'context_block': 'block_context',
      'active_item_id': app.request.get('activeItemId')|default(null),
      'columns': [
          { 'name': 'example', 'width': 50 },
          { 'name': 'structure' },
      ]
  }) }}
{% endblock %}

{% block jmn_column_example %}{{ column_label }}{% endblock %}
{% block jmn_cell_example %}{{ page_label }}{% endblock %}

{% block example_context %}
{% apply spaceless %}
    {% set pageDisplay = 'page:c04ee9be-2d25-4baf-a810-3ef4096e7d75'|emsco_display %}
    {% set context = { 'page_label': pageDisplay } %}
{% endapply %}
{{ context|json_encode|raw }}
{% endblock %}
```

Available in each blocks:
* [config](https://github.com/ems-project/elasticms/blob/HEAD/EMS/core-bundle/src/Core/Component/JsonMenuNested/Config/JsonMenuNestedConfig.php): object build from passed configuration
* [column](https://github.com/ems-project/elasticms/blob/HEAD/EMS/core-bundle/src/Core/Component/JsonMenuNested/Config/JsonMenuNestedColumn.php): object containing name and width
* [node](https://github.com/ems-project/elasticms/blob/HEAD/EMS/core-bundle/src/Core/Component/JsonMenuNested/Config/JsonMenuNestedNode.php): object contains field type information: icon, label, type.
* [template](https://github.com/ems-project/elasticms/blob/HEAD/EMS/core-bundle/src/Core/Component/JsonMenuNested/Template/JsonMenuNestedTemplate.php): object used for rendering
* [menu](https://github.com/ems-project/elasticms/blob/HEAD/EMS/common-bundle/src/Json/JsonMenuNested.php): the parent json menu nested of the current item
* [item](https://github.com/ems-project/elasticms/blob/HEAD/EMS/common-bundle/src/Json/JsonMenuNested.php): the item that rendered

## Media library

The media library component show all documents inside a contentType with a folder tree.
Uploading a new file will create a new document.

* **Files list**: has infinity scrolling, so a folder can contain x amount of files.
* **Folders list**: limited to **5000** folders over all levels.

### Implementation (media-library)

If you use this [media_library.json](/files/contenttype_media_library.json ':ignore') contentType, the only required attribute is `id`.
For a more advanced implementation look into our [demo project](https://github.com/ems-project/elasticms-demo).

```twig
{{ component('media_library', {
    'id': 'examaple-media-lib'
}) }}
```

| Property          | Default                | Description                                                |
|-------------------|------------------------|------------------------------------------------------------|
| `id`              |                        | **required** html id attribute                             |
| `contentTypeName` | media_file             | **required** contentType name                              |
| `fieldPath`       | media_path             | **required** Field name for path value                     |
| `fieldPathOrder`  | media_path.alpha_order | Used for sorting folders and files.                        |
| `fieldFolder`     | media_folder           | **required** Field name for folder value                   |
| `fieldFile`       | media_file             | **required** Field name for asset                          |
| `defaultValue`    |                        | Key/value array for defining default, example organization |
| `searchSize`      | 100                    | Used for search and infinity scrolling                     |
| `searchQuery`     | see config             | Example only load media files for an organization          |
| `searchFileQuery` |                        | Define the search query used for searching file documents  |
| `template`        |                        | see [templating](#templating-media-library)                |
| `context`         |                        | see [templating](#templating-media-library)                |

- [config](https://github.com/ems-project/elasticms/blob/HEAD/EMS/core-bundle/src/Core/Component/MediaLibrary/Config/MediaLibraryConfig.php)
- [config factory](https://github.com/ems-project/elasticms/blob/HEAD/EMS/core-bundle/src/Core/Component/MediaLibrary/Config/MediaLibraryConfigFactory.php)

### Templating (media-library)

See the default [template](https://github.com/ems-project/elasticms/blob/HEAD/EMS/core-bundle/src/Resources/views/components/media_library/template.twig) for all available blocks.

* `media_lib_file`: links have by default the `ems-id` data attribute: 
  
  When the dashboard is used as file or object browser (wysiwyg) the ems-id will be used as value.

```twig
{{ block("body", "@EMSCH/template/dashboard/media_library.twig") }}
```

Example add an extra 'go to revision' column. 

```twig
{% block body %}
    {{ component('media_library', { 
        'id': 'examaple-media-lib', 
        'template': _self,
        'context': {
            'example': 'example'
        } 
    }) }}
{% endblock body %}

{%- block media_lib_file_header -%}
    <div>Name</div>
    <div>Type</div>
    <div>Revision</div>
    <div class="text-right">Size</div>
{%- endblock media_lib_file_header -%}

{%- block media_lib_file -%}
    <div><a href="{{- mediaFile.urlView -}}" download="{{- mediaFile.name -}}" data-ems-id="{{- mediaFile.emsId -}}">{{- mediaFile.name -}}</a></div>
    <div>{{- mediaFile.file.mimetype|trans({}, 'emsco-mimetypes') -}}</div>
    <div><a href="{{ path('emsco_view_revisions', { 'type': mediaFile.document.contentType, 'ouuid': mediaFile.id  }) }}">show revision</a></div>
    <div class="text-right">{{- mediaFile.file.filesize|default(0)|format_bytes -}}</div>
{%- endblock media_lib_file -%}
```
