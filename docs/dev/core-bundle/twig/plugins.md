# Frontend plugins

You can use some tricks in your ElasticMS views, actions or dashboard to activate fontend plusgins

## Image lazy loading

This plugin avoid to directly load directly all images at page load.
But to load images only when they close to the viewport.
This can be useful when you display a lot of images on a long page.

To use it you need to:
 * Add a lazy class to the `IMAGE` tags
 * Specify a default image in the `src` attribute
 * Specify the image to load in the `data-original` attribute

In this example the user Gavatar image is lazy loaded:

```twig
<img src="{{ asset('images/anonymous.gif', 'emscore') }}" data-original="https://www.gravatar.com/avatar/{{ app.user.email|lower|ems_md5 }}?s=256" class="{{ class }} lazy" alt="{{ app.user.displayName }}" width="128" height="128">
```

## selectpicker

This is nice looking plugin for `SELECT` tags. Add a `selectpicker` class to the `SELECT` tag to activate it:

```twig
<select id="content_type_roles_view" name="content_type[roles][view]" data-live-search="data-live-search" class="selectpicker form-select">
  <option value="not-defined" data-icon="fa fa-user-circle">Not defined</option>
  <option value="ROLE_USER" data-icon="fa fa-user-circle">User</option>
  <option value="ROLE_AUTHOR" data-icon="fa fa-user-circle" selected="selected">Author</option>
  <option value="ROLE_FORM_CRM" data-icon="fa fa-user-circle">Form CRM</option>
  <option value="ROLE_TASK_MANAGER" data-icon="fa fa-user-circle">Task Manager</option>
  <option value="ROLE_REVIEWER" data-icon="fa fa-user-circle">Reviewer</option>
  <option value="ROLE_TRADUCTOR" data-icon="fa fa-user-circle">Traductor</option>
  <option value="ROLE_AUDITOR" data-icon="fa fa-user-circle">Auditor</option>
  <option value="ROLE_COPYWRITER" data-icon="fa fa-user-circle">Copywriter</option>
  <option value="ROLE_PUBLISHER" data-icon="fa fa-user-circle">Publisher</option>
  <option value="ROLE_WEBMASTER" data-icon="fa fa-user-circle">Webmaster</option>
  <option value="ROLE_ADMIN" data-icon="fa fa-user-circle">Administrator</option>
  <option value="ROLE_SUPER_ADMIN" data-icon="fa fa-user-circle">Super Admin</option>
  <option value="ROLE_API" data-icon="fa fa-user-circle">API</option>
</select>
```
A class icon can be defined in the `data-icon` attribute of the `OPTION` tag. Add a `select2` class to the `SELECT` tag to activate it:

## Select 2

Another nice looking plugin for `SELECT` tags. Add a `select2` class to the `SELECT` tag to activate it.

## Datatable

Activate a datatable on a `TABLE` tag by adding datatable options (JSON serialized) in a `data-datatable` attribute.

```twig
<table class="table table-condensed table-striped" data-datatable="{{ datatable.frontendOptions|json_encode|e('html_attr') }}">
  ...
</table>
```

## WYWIWYG editor

Activate a WYSIWYG editor on a `TEXTAREA` tag by adding a `ckeditor` class.

```twig
<textarea id="my-id" name="html_field" class="ckeditor form-control" rows="10"></textarea>
```
