## Get a choice's label from a choice value

If inside a response template you want to get a choice label:

```twig
{% set subject = config.elements.name_of_the_subject_field.choices.getLabel(data.name_of_the_subject_field) %}
```