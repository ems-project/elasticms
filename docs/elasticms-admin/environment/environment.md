# Environment

An environment is used by a [ContentType](../contentType/contentType.md), all revisions will be indexed into the alias.
The environment attach to a contentType is the `default` environment for this contentType.

From the default environment we can publish/unpublish to other environments.
Often an elasticms has 2 environments `preview` and `live`. 

<!-- TOC -->
* [Environment](#environment)
  * [Properties](#properties)
  * [Publish Role](#publish-role)
  * [Template publication](#template-publication)
<!-- TOC -->

## Properties 

| Property            | Description                                                      |
|---------------------|------------------------------------------------------------------|
| name                | Internal name                                                    |
| label               | Display label                                                    |
| color               | Display color                                                    |
| alias               | Elasticsearch alias (EMSCO_INSTANCE_ID + name)                   |
| circles             |                                                                  |
| inDefaultSearch     |                                                                  |
| managed             |                                                                  |
| snapshot            |                                                                  |
| updateReferrers     |                                                                  |
| templatePublication | Twig template, see [Template publication](#template-publication) |
| publishRole         | Publish Role, see [Publish Role](#publish-role)                  |
| baseUrl             | Text field for defining an baseUrl                               |

## Publish Role

Block environment publication by [user role](../user/user.md#Roles), on revision detail page and compare environments page.

This overwrites the publish role on the [contentType](../contentType/contentType.md#Roles).

> This does not apply for the default environment, publication in the default environment is managed 
> by the [contentType edit role](../contentType/contentType.md#Roles).

## Template publication

You can block publication to an environment by defining a publication template.
- If the template adds a warning or error, the publication is block. 
- If the template add an info message, the publication is not block.

```twig
{% set markdownMessage %}
**Validation failed**
* Document {{ revision.label }} is not validated (checkbox)
{% endset %}

{% if revision.contentType == 'page' and false == document.source.validated|default(false) %} 
    {% do publication.addWarning(markdownMessage) %}
{% endif %}
```

> **Template context**
>
> * `publication` : publication object (methods addWarning, addError, addInfo)
> * `environment` : current environment
> * `revision` : revision to be published
> * `document` : elasticsearch document of the revision (default environment) 