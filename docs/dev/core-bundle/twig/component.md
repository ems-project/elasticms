# Twig Components

Overview core twig components. 
Now we are using Symfony ux [twig components](https://symfony.com/bundles/ux-twig-component/current/index.html), 
in the feature we may use [live components](https://symfony.com/bundles/ux-live-component/current/index.html).  

The following components can be used in views/actions and dashboards.

<!-- TOC -->
* [Twig Components](#twig-components)
  * [Json Menu Nested](#json-menu-nested)
    * [Implementation (json-menu-nested)](#implementation-json-menu-nested)
    * [Templating (json-menu-nested)](#templating-json-menu-nested)
    * [Javascript (json-menu-nested)](#javascript-json-menu-nested)
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

| Property        | Default                         | Description                                                                                     |
|-----------------|---------------------------------|-------------------------------------------------------------------------------------------------|
| `id`            |                                 | **required** html id attribute                                                                  |
| `ems_link`      |                                 | **required** emsLink to the object                                                              |
| `field_path`    |                                 | **required** property path to the json menu nested field                                        |
| `columns`       | ```json[{name: 'structure'}]``` | Json array of columns (name required, width default 200). Title column will always be available |
| `template`      |                                 | see [templating](#templating-json-menu-nested)                                                  |
| `context`       |                                 | key/value array that will be passed to all twig blocks                                          |
| `context_block` |                                 | The passed block name, will be rendered on each request.                                        |

### Javascript & CSS (json-menu-nested)

The following example, will on load:
* activate an item `84f4260f-6224-4c1e-983d-d1e81753bf47`
* change the default modal size to lg
* get item `4f7dc5b6-54ff-4861-998a-bfc691ba2d12`

```javascript
window.addEventListener('emsReady', function () {
    const jsonMenuNested = window.jsonMenuNestedComponents['example-id-component'];
    jsonMenuNested.load({ activeItemId: '84f4260f-6224-4c1e-983d-d1e81753bf47'});
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

Full template
```twig
{%- block jmn_layout_top -%}
    <div class="jmn-top">
        <div class="text-right">{{ template.block('jmn_button_menu_add', _context)|raw }}</div>
    </div>
{%- endblock jmn_layout_top -%}

{%- block jmn_layout_footer -%}{% endblock jmn_layout_footer %}

{%- block jmn_column_title -%}<span>Title</span>{%- endblock jmn_column_title -%}
{%- block jmn_column_structure -%}<span>Structure</span>{%- endblock jmn_column_structure -%}

{%- block jmn_cell_title -%}
    {% if item.hasChildren %}
        <button class="jmn-item-icon jmn-btn-collapse" aria-expanded="{{- item in loadParents ? 'true' : 'false' -}}"></button>
    {% endif %}
    {% if node.icon %}
        <div class="jmn-item-icon"><i class="{{ node.icon }}"></i></div>
    {% endif %}
    <span>{{ item.label }}</span>
{%- endblock jmn_cell_title -%}

{%- block jmn_cell_structure -%}
    {{ template.block('jmn_button_menu_add', _context)|raw }}
    {{ template.block('jmn_button_item_move', _context)|raw }}
    {{ template.block('jmn_button_menu_more', _context)|raw }}
{%- endblock jmn_cell_structure -%}

{%- block jmn_button_item_edit -%}
    <button class="jmn-btn-edit" data-modal-size="md">Edit</button>
{%- endblock jmn_button_item_edit -%}

{%- block jmn_button_item_delete -%}
    <button class="jmn-btn-delete">Delete</button>
{%- endblock jmn_button_item_delete -%}

{%- block jmn_button_item_move -%}
    <button class="btn btn-sm btn-default jmn-btn-move">Move</button>
{%- endblock jmn_button_item_move -%}

{%- block jmn_button_item_view -%}
    <button class="jmn-btn-view" data-modal-size="md">View</button>
{%- endblock jmn_button_item_view -%}

{%- block jmn_button_item_add -%}
    <button class="jmn-btn-add" data-add="{{ addNode.id }}" data-modal-size="md">
        {% if addNode.icon %}<i class="{{ addNode.icon }}"></i>{% endif %}
        New {{ addNode.type|capitalize }}
    </button>
{%- endblock jmn_button_item_add -%}

{%- block jmn_menu_add -%}
    <ul class="dropdown-menu pull-right">
        {% for addNode in addNodes %}
            <li>{{ template.block('jmn_button_item_add', _context)|raw }}</li>
        {% endfor %}
    </ul>
{%- endblock jmn_menu_add -%}

{%- block jmn_button_add -%}
    <button type="button" class="btn btn-sm btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <i class="fa fa-plus"></i> Add
    </button>
{%- endblock jmn_button_add -%}

{%- block jmn_button_menu_add -%}
    {% set node = node|default(config.nodes.root) %}
    {% set addNodes = config.nodes.children(node) %}
    {% if addNodes|length > 0 %}
        <div class="btn-group btn-group-sm">
            {{ template.block('jmn_button_add', _context)|raw }}
            {{ template.block('jmn_menu_add', _context)|raw }}
        </div>
    {% endif %}
{%- endblock jmn_button_menu_add -%}

{%- block jmn_button_menu_more -%}
    <div class="btn-group btn-group-sm">
        {{ template.block('jmn_button_more', _context)|raw }}
        {{ template.block('jmn_menu_more', _context)|raw }}
    </div>
{%- endblock jmn_button_menu_more -%}

{%- block jmn_button_more -%}
    <button type="button" class="btn btn-sm btn-default dropdown-toggle jmn-btn-more" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <i class="fa fa-ellipsis-v"></i>
    </button>
{%- endblock jmn_button_more -%}

{%- block jmn_menu_more -%}
    <ul class="dropdown-menu pull-right">
        <li>{{ template.block('jmn_button_item_view', _context)|raw }}</li>
        <li>{{ template.block('jmn_button_item_edit', _context)|raw }}</li>
        <li>{{ template.block('jmn_button_item_delete', _context)|raw }}</li>
    </ul>
{%- endblock jmn_menu_more -%}

{%- block jmn_modal_title -%}
    {% if node.icon %}<i class="{{ node.icon }}"></i>&nbsp;{% endif %}
    {{ action|capitalize }} {{ node.type|capitalize }}
{%- endblock jmn_modal_title -%}

{%- block jmn_modal_form -%}
    {{ form_start(form) }}
    {{ form_widget(form.data) }}
    {% if form._item_hash is defined %}{{ form_widget(form._item_hash) }}{% endif %}
    {{ form_end(form) }}
{%- endblock jmn_modal_form -%}

{%- block jmn_modal_footer_form -%}
    <div class="pull-right">
        <button id="ajax-modal-submit" class="btn btn-sm btn-primary"><i class="fa fa-save"></i>&nbsp;Save</button>
        <button type="button" class="btn btn-sm btn-default" data-dismiss="modal">Cancel</button>
    </div>
{%- endblock jmn_modal_footer_form -%}

{%- block jmn_modal_preview -%}
    {{ block('_jmn_preview') }}
{%- endblock jmn_modal_preview -%}

{%- block jmn_modal_footer_close -%}
    <div class="pull-right">
        <button type="button" class="btn btn-sm btn-default" data-dismiss="modal">Close</button>
    </div>
{%- endblock jmn_modal_footer_close -%}
```

## Media library

The media library component show all documents inside a contentType with a folder tree.
Uploading a new file will create a new document.

* **Files list**: has infinity scrolling, so a folder can contain x amount of files.
* **Folders list**: limited to **5000** folders over all levels.

### Implementation (media-library)

If you use this [media_library.json](/files/contenttype_media_library.json ':ignore') contentType, the only required attribute is `id`.

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
| `searchQuery`     |                        | Example only load media files for an organization          |
| `template`        |                        | see [templating](#templating-media-library)                |
| `context`         |                        | see [templating](#templating-media-library)                |

### Templating (media-library)

Available in blocks:
* [Config](https://github.com/ems-project/elasticms/blob/HEAD/EMS/core-bundle/src/Core/Component/MediaLibrary/MediaLibraryConfig.php)
* [MediaFile](https://github.com/ems-project/elasticms/blob/HEAD/EMS/core-bundle/src/Core/Component/MediaLibrary/MediaLibraryFile.php) only in `mediaLibraryFileRow`
* The context if defined in config

The following example contains all possible blocks, with their default rendering.

* `mediaLibraryFileRow`: links have by default the `ems-id` data attribute: 
  
  When the dashboard is used as file or object browser (wysiwyg) the ems-id will be used as value.

```twig
{{ block("body", "@EMSCH/template/dashboard/media_library.twig") }}
```

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

{%- block mediaLibraryHeaderLeft -%}
    {% apply spaceless %}
        <div class="media-lib-container">
            {{ buttonHome|raw }}
        </div>
    {% endapply %}
{%- endblock mediaLibraryHeaderLeft -%}

{%- block mediaLibraryHeader -%}
    {% apply spaceless %}
        <div class="media-lib-container">
            {{ buttonAddFolder|raw }}
            {{ buttonUpload|raw }}
            {{ breadcrumb|raw }}
        </div>
    {% endapply %}
{%- endblock mediaLibraryHeader -%}

{%- block mediaLibraryFileRowHeader -%}
    {% apply spaceless %}
        <li>
            <div>Name</div>
            <div>Type</div>
            <div class="text-right">Size</div>
        </li>
    {% endapply %}
{%- endblock mediaLibraryFileRowHeader -%}

{%- block mediaLibraryFileRow -%}
    {% apply spaceless %}
        <li>
            <div><a href="{{ url }}" download="{{ media.file.filename }}" data-ems-id="{{ media.emsId }}">{{ media.file.filename }}</a></div>
            <div>{{ media.file.mimetype|trans({}, 'emsco-mimetypes') }}</div>
            <div class="text-right">{{ media.file.filesize|format_bytes }}</div>
        </li>
    {% endapply %}
{%- endblock mediaLibraryFileRow -%}

{%- block mediaLibraryFooter -%}
    <div class="media-lib-container"></div>
{%- endblock mediaLibraryFooter -%}
```

