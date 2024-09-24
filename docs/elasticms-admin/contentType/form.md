# ContentType's encoding form

By content type you have to define an encoding form. That form is defined by a structure of [DataFields](https://github.com/ems-project/elasticms/tree/5.x/EMS/core-bundle/src/Form/DataField).

There is 6 different kinds of DataFields:

 * Simple: The DataFields the DataField corresponds to one, and only one, field in the elesticsearch mapping
 * Complex:  The DataFields the DataField corresponds to many fields in the elesticsearch mapping
 * Compound: The DataFields is composed by other children DataFields, simple and complex children fields will be nested fields in elasticsearch's mapping
 * Layout: The DataFields doesn't correspond to anything in elasticsearch, it just about form's layout within the ElasticMS Admin interface
 * Mapping: The DataFields doesn't correspond to anything in the ElasticMS Admin interface, it just about the mapping of elasticsearch
 * JSON: The DataFields is composed by other children DataFields,  simple and complex children fields will be serialized in a JSON text

You have several types of DataField available to define the form structure :

| Type                 | Kind     | In the revision form                                                                   | In elastisearch                                                                                            | Has Child           | Deprecated                        |
|----------------------|----------|----------------------------------------------------------------------------------------|------------------------------------------------------------------------------------------------------------|---------------------|-----------------------------------|
| Asset                | Complex  | Mini app allowing to upload/download/drag-n-drop file(s)                               | Array or array of array of meta information about files (hash, filename, filesize and mimetype)            | No                  |                                   |
| Checkbox             | Simple   | Checkbox                                                                               | Boolean                                                                                                    | No                  |                                   |
| Choice               | Simple   | Radio buttons, Check boxes, Single-Select Combobox or Multi-Select Combobox            | String or array of string                                                                                  | No                  |                                   |
| Code                 | Simple   | [ACE editor](https://ace.c9.io/)                                                       | text                                                                                                       | No                  |                                   |
| Collection           | Compound | A dynamic and reorderable list of subforms                                             | nested object                                                                                              | Many                |                                   |
| Color                | Simple   | A color picker                                                                         | text or keyword                                                                                            | No                  |                                   |
| Computed             | Complex  | No input but a template in the revision view                                           | free defined mapping                                                                                       | May have            |                                   |
| Container            | Layout   | Just a container to organize children fields                                           | [N/A]                                                                                                      | Yes                 |                                   |
| CopyTo               | Mapping  | [N/A]                                                                                  | Allow to defined mapping without form's field                                                              | No                  |                                   |
| DataLink             | Simple   | Document picker                                                                        | keyword (string or array of strings)                                                                       | No                  |                                   |
| Date                 | Simple   | Date picker                                                                            | date (one or many)                                                                                         | No                  |                                   |
| DateRange            | Complex  | Date range picker                                                                      | 2 dates (nested or not)                                                                                    | No                  |                                   |
| DateTime             | Simple   | Date time picker                                                                       | date                                                                                                       | No                  |                                   |
| Email                | Simple   | Email                                                                                  | text or keyword                                                                                            | No                  |                                   |
| Form                 | Compound | Load the corresponding form entity                                                     | Load the corresponding form entity                                                                         | Many                |                                   |
| Hidden               | Simple   |                                                                                        |                                                                                                            | No                  | Use a hidden class                |
| Holder               | Layout   | Invisible container                                                                    | [N/A]                                                                                                      | Many                |                                   |
| Icon                 | Simple   | FontAwsome 3 class picker                                                              | text or keyword                                                                                            | No                  | Avoid it                          |
| IndexedAsset         | Complex  | Mini app allowing to upload/download/drag-n-drop file(s) with Tika extractions         | Array of meta information about files (hash, filename, filesize, mimetype, language, content, author, ...) | No                  |                                   |
| Integer              | Simple   | Text field                                                                             | integer                                                                                                    | No                  |                                   |
| JSON                 | Simple   | [ACE editor](https://ace.c9.io/)                                                       | free defined mapping                                                                                       | No                  |                                   |
| JsonMenu             | JSON     | Simple structure saved as a string in a JSON                                           | text                                                                                                       |                     |                                   |
| JsonMenuLink         | JSON     | Link to a JsonMenu node                                                                | text                                                                                                       |                     |                                   |
| JsonMenuNestedEditor | JSON     | Complex structure (with many subforms) saved as a string in a JSON                     | text                                                                                                       | Many (JSON encoded) |                                   |
| JsonMenuNestedLink   | JSON     | Link to a JsonMenuNestedEditor node                                                    | text                                                                                                       |                     |                                   |
| Multiplexed          | Compound | Render a form per value (form are selected via tab) can be integrated with a Tab field | Prefixed all subfields (not nested) i.e. fr.title                                                          | Many                |                                   |
| Nested               | Compound | Similar to a container; organise children fields                                       | nested mapping                                                                                             | Many                |                                   |
| Number               | Simple   | Text field                                                                             | double                                                                                                     | No                  |                                   |
| Ouuid                | Simple   |                                                                                        |                                                                                                            | No                  | Use the `_id` in a Computed field |
| Password             | Simple   | Password field                                                                         | text or keyword (the password is hashed)                                                                   | No                  |                                   |
| Radio                | Simple   |                                                                                        |                                                                                                            | No                  | Use a Choice field                |
| Select               | Simple   |                                                                                        |                                                                                                            | No                  | Use a Choice field                |
| Subfield             | Mapping  | [N/A]                                                                                  | Defined a submapping to the field. I.e. `title.raw`                                                        | No                  |                                   |
| Tabs                 | Layout   | Allow to organize subforms in different tabs                                           | [N/A]                                                                                                      | Many (Containers)   |                                   |
| TextString           | Simple   | Text field                                                                             |                                                                                                            | No                  |                                   |
| Textarea             | Simple   | Textarea field                                                                         | text                                                                                                       | No                  |                                   |
| Time                 | Simple   | Time picker                                                                            |                                                                                                            | No                  |                                   |
| VersionTag           | Simple   |                                                                                        |                                                                                                            | No                  |                                   |
| Wysiwyg              | Simple   | [CK Editor](https://ckeditor.com/                                                      | text                                                                                                       | No                  |                                   |

 ## Postprocessing

It's a powerful way to validate, provide each form's DataField. It can be configured with a Twig template in the  field's extra options tab.

Context of the postprocessing template:
 - `_id`: Document's OUUID (string) or `null` if the documents hasn't been finalized yet
 - `migration`: boolean set to `true` in the context of a migration (like in the context of the `ems:contenttype:migrate` or `ems:contenttype:recompute` command)
 - `finalize`: boolean set to `false` in the context of an autosave, otherwize set to `true`
 - `rootObject`: The associate array of the document's RAW data as it was extracted from the Revision's form
 - `_source`: The associate array of the current field with all its siblings (refers to data of the current DataField structure)
 - `_type`: content type's name (string)
 - `index`: Elasticsearch's alias of the content type's default environment
 - `alias`: Elasticsearch's alias of the content type's default environment
 - `path`: Dot path to the current DataField (i.e. `'fr.title'`)
 - `form`: Symfony Form object of the current DataField

