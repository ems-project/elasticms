# Example of field's postprocessing

## For file field for image

This script limit the file's size to 3MB (helpful to keep elasticms-web containers small from a memory perspective). The script also ensure that the file is an image.

```twig
{%- if finalize and _source.image.sha1|default(false) and _source.image.filesize|default(0) > 3145728 %}
    {{ emsco_cant_be_finalized('The file cannot be bigger than 3 MB') }}
{% endif -%}
{%- if finalize and _source.image.sha1|default(false) and not (_source.image.mimetype|default('0') starts with 'image/') %}
    {{ emsco_cant_be_finalized('This field only accepts images') }}
{% endif -%}
```