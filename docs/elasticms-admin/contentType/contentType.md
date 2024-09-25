# ContentType

ContentType contain the structure, default environment and information for all related revisions.

<!-- TOC -->
* [ContentType](#contenttype)
  * [Properties](#properties)
  * [Default value](#default-value)
  * [Fields](#fields)
  * [Settings](#settings)
    * [Tasks](#tasks)
  * [Roles](#roles)
* [Actions](#actions)
  * [Embed](#embed)
  * [Export](#export)
    * [Spreadsheet](#spreadsheet)
      * [Example export](#example-export)
      * [Example export jsonMenuNested](#example-export-jsonmenunested)
  * [Import](#import)
    * [Example import jsonMenuNested](#example-import-jsonmenunested)
  * [External link](#external-link)
  * [Raw HTML](#raw-html)
  * [Notification](#notification)
  * [Job](#job)
  * [Pdf](#pdf)
* [Transformers](#transformers)
  * [Html Attribute Transformer](#html-attribute-transformer)
    * [Config](#config)
    * [Examples](#examples)
  * [Html Empty Transformer](#html-empty-transformer)
    * [Config](#config-1)
  * [Html remove node transformer](#html-remove-node-transformer)
* [Views](#views)
  * [Calendar view](#calendar-view)
  * [Criteria view](#criteria-view)
  * [DataLink view](#datalink-view)
    * [Twig content template](#twig-content-template)
    * [Example](#example)
  * [Export view](#export-view)
  * [Gallery view](#gallery-view)
  * [Hierarchical view](#hierarchical-view)
  * [Importer view](#importer-view)
  * [Keywords view](#keywords-view)
  * [Report view](#report-view)
  * [Sorter view](#sorter-view)
<!-- TOC -->

## Properties

| Property             | Description                                  |
|----------------------|----------------------------------------------|
| name                 | Internal name                                |
| pluralName           | Display plural name                          |
| singularName         | Display single name                          |
| icon                 | Display icon in menu, dropdown, revision     |
| color                | Display color                                |
| description          | Internal description (not visible for users) |
| indexTwig            | **Deprecated**                               |
| extra                |                                              |
| askForOuuid          |                                              |
| refererFieldName     |                                              |
| sortOrder            |                                              |
| rootContentType      |                                              |
| editTwigWithWysiwyg  |                                              |
| webContent           |                                              |
| autoPublish          |                                              |
| active               |                                              |
| environment          |                                              |
| defaultValue         | See [Default value](#default-value)          |
| versionTags          |                                              |
| versionOptions       |                                              |
| versionDateFromField |                                              |
| versionDateToField   |                                              |
| roles                | Json field see [roles](#Roles)               |
| fields               | Json field see [fields](#Fields)             | 
| settings             | See [settings](#Settings)                    | 

## Default value

On a content type you can define a default value for the revisions.
The result should be a valid JSON rendered by Twig template.

> **Template context**
> 
> * `environment` : the default environment of the content type
> * `contentType` : the content type entity
> * `currentUser` : the authenticated user

## Fields

On a content type we can define fields from the elasticsearch mapping.
These are used for displaying revision information.

| Field       | Description                                                                             |
|-------------|-----------------------------------------------------------------------------------------|
| display     | Expression for display the revision using [emsco_display](./dev/core-bundle/twig/core#emsco_display) |
| label       | Display label for the revision                                                          |
| color       | Display color for the revision                                                          |
| sort        | Default sorting in choice lists (better use querySearch)                                |
| tooltip     | Add tooltip on dataLinks                                                                |
| circles     | Field containing the revision circles                                                   |
| business_id | Used in export/import documents                                                         |
| category    | Used in criteria view                                                                   |
| asset       | Used in asset link from WYSIWYG                                                         |

## Settings

| Setting               | Description                          |
|-----------------------|--------------------------------------|
| Task enabled          | see [#Tasks]                         |
| Hide revision sidebar | If enabled will not show the sidebar |

### Tasks

When tasks are enabled, every user can create, handle, validate tasks inside a revision.

If a user completes a task, he can **only** validate the task if he is the requester.

Only the requester and task admins can delete the tasks

On all tasks steps the assignee and/or requester will receive **emails**.

Users who have the role `TASK_MANAGER` can see all current tasks in their dashboard overview.

Task admins can also delete tasks, but the requester will receive an email.

## Roles

On a content type you can define a [user role](../user/user.md#Roles) for permissions.

| Permission       | Description                                                                                                                                  |
|------------------|----------------------------------------------------------------------------------------------------------------------------------------------|
| view             | Display contentType in menu, enable dataLinks                                                                                                |
| create           | Grant creation of new revisions                                                                                                              |
| edit             | Grant update revision                                                                                                                        |
| publish          | Grant publication to other environments, can be overruled by [environment publish role](../environment/environment.md#publish-role) |
| delete           | Grant delete revision                                                                                                                        |
| trash            | Trash functional enabled (put back deleted revisions)                                                                                        |
| archive          | Grant archive revision (unpublish default environment)                                                                                       |
| owner            | Can be revision owners                                                                                                                       |
| show_link_create | Display creation link in navigation                                                                                                          |
| show_link_search | Display search link in navigation                                                                                                            |

# Actions

| Render option                   | description                          |
|---------------------------------|--------------------------------------|
| [Embed](#embed)                 | Create a custom action page          |
| [Export](#export)               | Create an export file (csv, xml, ..) |
| [External link](#external-link) | Create an external link              |
| [RawHTML](#raw-html)            | Custom raw html action               |
| [Notification](#notification)   | Create a new notification            |
| [Job](#job)                     | Start a new job                      |
| [PDF](#pdf)                     | Generate a pdf                       |

## Embed
The body is used for creating a new page.
Good for generating overviews or custom reports.

## Export
Export a generated file.

### Spreadsheet

If you enable the spreadsheet checkbox, the body needs to return a valid json.
This json is passed to the common Spreadsheet generator. 

- `mimetype` can be left empty, because it will be set by the spreadsheet generator.
- `extension` is required and can be **csv** or **xlsx**

If you use the **csv** extension the body can only contain one sheet.

#### Example export

```twig
{{- [
    { 
        "name": "sheet1",
        "rows": [
            ["bundle", "description"],
            ["core-bundle", "symfony core bundle"],
            ["common-bundle", "symfony common bundle"]
        ]
    }
]|json_encode|raw -}}
```

#### Example export jsonMenuNested

Export the nested object fields `title_nl`, `title_fr`, `date_start`, `date_end` from the field named `items`.

````twig
{%- set columns = ['title_nl', 'title_fr', 'date_start', 'date_end'] -%}
{%- set rows = [] -%}
{%- set rows = rows|merge([columns]) -%}
{%- set codes = source.items|default('[]')|ems_json_menu_nested_decode -%}
{%- for item in items.children -%}
    {%- set row = [] -%}
    {%- for column in columns -%}
        {%- if column in ['date_start', 'date_end'] -%}
            {%- set objectDate = attribute(item.object, column)|default(false) -%}
            {%- set row = row|merge([ objectDate ? objectDate|date('d-m-Y') : '' ]) -%}
        {%- else -%}
            {%- set row = row|merge([ attribute(item.object, column)|default('') ]) -%}
        {%- endif -%}
    {%- endfor -%}
    {%- set rows = rows|merge([row]) -%}
{%- endfor -%}
{{- [ { "name": "export jsonMenuNested", "rows": (rows) }]|json_encode|raw -}}
````

## Import
This actions shows a modal with a file upload field, only allowing *xlsx* or *csv* files.
On submit the data is parsed and imported in the default environment.

For the moment we only support jsonMenuNested import data.
Generated by the [export action](#example-export-jsonmenunested) for example.

The body template requires a block name `config` which outputs a valid json and defining the following:
* `type`: jsonMenuNested
* `field`: target import field name
* `columns`: required column names 

Passing an empty or invalid file, will result in error messages inside the modal.

For importing a jsonMenuNested we should build the object. 
This is done by defining a block named `row`, which outputs a valid json.

### Example import jsonMenuNested

```twig
{%- block config -%}
{{- {
    type: 'jsonMenuNested',
    field: 'items',
    columns: ['title_nl', 'title_fr', 'date_start', 'date_end']
}|json_encode|raw -}}
{%- endblock -%}

{%- block row -%}
{%- set label = [row.title_nl, row.title_fr]|join(' / ') -%}
{{- {
    id: ems_uuid(),
    label: (label),
    type: 'item',
    object: (row|merge({
        date_start: (row.date_start|default(false) ? row.date_start|date(constant('\DateTime::ATOM')) : null),
        date_end: (row.date_end|default(false) ? row.date_end|date(constant('\DateTime::ATOM')) : null)
    }))
}|json_encode|raw }}
{%- endblock -%}
```

## External link
The body is the href attribute for the external link.
You can also use the raw render option for more flexibility.

## Raw HTML
Only if the body returns html the output will be visible.
With the HTML render option you can even overwrite the icon.

## Notification
Creates a new ems notification

## Job
Start a new job, the body should be the command with arguments and options.

## Pdf
Similar to the export render option, but will always generate a pdf.

# Transformers
In the "Migration Options" of contenttype field you can add one or more transformers.
For each transformer you need to define a JSON config.
When running the transform command these transformers will be applied.

| Name                                                          | Description                                       | Field   |
|---------------------------------------------------------------|---------------------------------------------------|---------|
| [Html Attribute Transformer](#html-attribute-transformer)     | Remove html attribute or remove attribute values. | wysiwyg | 
| [Html Empty Transformer](#html-empty-transformer)             | Clean empty html content                          | wysiwyg | 
| [Html Remove Node Transformer](#html-remove-node-transformer) | Clean empty html content                          | wysiwyg | 

## Html Attribute Transformer
Only available for WYSIWYG field types.
### Config
* **attribute** : required, which attribute you want to transform
* **element** : default (*), which html element
* **remove** : default (false), remove the attribute
* **remove_value_prefix** : default (null), remove all values starting by from **class** or **style** attributes.

### Examples
> Remove all style attributes for all table elements
```json
{"attribute": "style", "element": "table", "remove": "true"}
```
> Remove all cellpadding attributes for all table elements
```json
{"attribute": "cellpadding", "element": "table", "remove": "true"}
```
> Remove all style values related to font-size
```json
{"attribute": "style", "element": "*", "remove_value_prefix": "font-size"}
```
> Remove all class values starting with 'font' from all divs
```json
{"attribute": "class", "element": "div", "remove_value_prefix": "font-"}
```

## Html Empty Transformer
Only available for WYSIWYG field types.
Clean content without textual content
### Config
> No config required

Example transformer to null
```html
<p style="text-align: justify;"> </p> <div class="example" style="text-align: justify;"> </div> <p> </p>
```
```html
<html lang="en"><body><h1>            </h1><p>&nbsp;       </p></body>        </html>
```

## Html remove node transformer
> Remove all span elements
```json
{"element": "span"}
```
> Remove all span that have a class attribute containing *delete*
```json
{"element": "span", "attribute": "class", "attribute_contains": "delete"}
```

# Views

| Name                                        | Description                                                                       |
|---------------------------------------------|-----------------------------------------------------------------------------------|
| [CalendarViewType](#calendar-view)          | A view where you can schedule your object                                         |
| [CriteriaViewType](#criteria-view)          | A view where we can massively edit content types having criteria                  | 
| [DataLinkViewType](#datalink-view)          | Manipulate the choices in a data link of this content type                        | 
| [ExportViewType](#export-view)              | Perform an elasticsearch query and generate a export with a twig template         |  
| [GalleryViewType](#gallery-view)            | A view where you can browse images                                                | 
| [HierarchicalViewType](#hierarchical-view)  | Manage a menu structure (based on a ES query)                                     | 
| [ImporterViewType](#importer-view)          | Form to import a zip file containing JSON files                                   | 
| [KeywordsViewType](#keywords-view)          | A view where all properties of kind (such as keyword) are listed on a single page |  
| [ReportViewType](#report-view)              | Perform an elasticsearch query and generate a report with a twig template         |  
| [SorterViewType](#sorter-view)              | Order a sub set (based on a ES query)                                             |  


## Calendar view
A view where you can schedule your object

## Criteria view
A view where we can massively edit content types having criteria

## DataLink view
> Manipulate the choices in a data link of this content type.

It is used by the searchApi when creating an internal link inside a WYSIWYG.
The view template does not need to return anything, it needs to add data to the passed **dataLinks** object.
This view will be excluded from the elasticms menu navigation.

### Twig content template

| Name        | Instance                                                                                                            | 
|-------------|---------------------------------------------------------------------------------------------------------------------|
| view        | [Entity\View](https://github.com/ems-project/EMSCoreBundle/blob/4.x/src/Entity/View.php)                         | 
| contentType | [Entity\contentType](https://github.com/ems-project/EMSCoreBundle/blob/4.x/src/Entity/ContentType.php)           |
| environment | [Entity\environment](https://github.com/ems-project/EMSCoreBundle/blob/4.x/src/Entity/Environment.php)           |
| dataLinks   | [Core\Document\DataLinks](https://github.com/ems-project/EMSCoreBundle/blob/4.x/src/Core/Document/DataLinks.php) |


### Example
> A document contains a json menu nested structure, and you want to select a node (id) inside this structure.
> The WYSIWYG has a language defined and is also passed to the twig context.

```twig
{% set searchStructures = { 
    "index": environment.alias,
    "size": 50,
    "body": {
        "query": { "bool": { "must":[ {"term": { "_contenttype": {"value":"my_structure"} } } ] } },
        "sort": [ { "order": { "order": "asc" } } ]
    }
}|search.hits.hits %}

{% set structures = [] %}
{% for h in searchStructures %}
    {% set structures = structures|merge([{
        'id': h._id,
        'type': 'structure',
        'label': (h._source.label),
        'object': { "label": (h._source.name) },
        'children': (h._source.structure|default('{}')|ems_json_decode)   
    }]) %}
{% endfor %}
{%- set structureMenu = structures|json_encode|ems_json_menu_nested_decode -%}

{% set locale = dataLinks.locale|default('fr') %}

{% set patterns = dataLinks.pattern|split('>')|map(v => v|trim) %}
{% set pattern = patterns|join('.*') %} {# searching for "Example > link" will patch "This Example > test > test2 > link" #}
{% set matchRegex = "/.*#{pattern}.*/i"  %}

{% for item in structureMenu %}
    {% set path = [] %}
    {%- for p in item.path -%}{%- set path = path|merge([p.object.label]) -%}{%- endfor -%}
    
    {% set text = path|join(' > ') %}
    {% if text matches matchRegex %}{% do dataLinks.add( ("my_node:#{item.id}"), text ) %}{% endif %}
{% endfor %}
```

## Export view
Perform an elasticsearch query and generate a export with a twig template

## Gallery view
A view where you can browse images

## Hierarchical view
Manage a menu structure (based on a ES query)

## Importer view
Form to import a zip file containing JSON files

## Keywords view
A view where all properties of kind (such as keyword) are listed on a single page

## Report view
Perform an elasticsearch query and generate a report with a twig template

## Sorter view
Order a sub set (based on a ES query)
