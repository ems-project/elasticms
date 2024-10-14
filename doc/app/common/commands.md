# Commands

## Batch

Run command(s) defined in twig template.

- The template must output a valid json list of commands.
- If the template contains a block named ```execute```, only this block will be rendered.

```
Usage:
  ems:batch <template>

Arguments:
  template              template name, path or twig code
  
Options:
      --context=CONTEXT  context passed to twig
```

```bash
# define template twig namespace
php bin/console ems:batch "@EMSCH/template_ems/batch.json.twig"
# define template by path
php bin/console ems:batch ../demo/skeleton/template_ems/batch.json.twig
# define template in command
php bin/console ems:batch '["ems:version", "ems:health-check]'
```
```twig
{# example batch.json.twig #}
{% block execute %}
  {{ ["ems:version", "ems:health-check"]|json_encode|raw }}
{% endblock %}
```

Provide context from command to twig template

```bash
# add context
php bin/console ems:batch "@EMSCH/template_ems/batch_context.json.twig" --context='{"envName":"live"}'
```
```twig
{# example batch.json.twig #}
{% block execute %}
  {{ ["ems:environment:rebuild #{envName}"]|json_encode|raw }}
{% endblock %}
```

## Clear logs

Remove stored logs from the database.

```
Description:
  Clear doctrine logs

Usage:
  ems:logs:clear [options]

Options:
      --before[=BEFORE]    CLear logs older than the strtotime (-1day, -5min, now) [default: "-1week"]
      --channel[=CHANNEL]  Define channels default [app] [default: ["app"]] (multiple values allowed)
```

Example

Remove all logs created before now for the channels `app` and `core`

```bash
php bin/console ems:logs:clear --before=now --channel=app --channel=core
```

## Status

This command give a basic status of the elasticsearch cluster and for the different storage services:

```
ems:status
```

This command has 3 option:

- `--silent`: if turned on the command only shows errors and warnings
- `--wait-for-status=green`: the command will wait that the elasticsearch status is green (useful when you chain commands)
- `--timeout=30s`: If no response form the elasticsearch cluster after the timeout and the status will be considered as red

## Curl

This command allows you to save request to a file. Usage:

```
ems:curl /public/view/54 /opt/samples/test.pdf --save --base-url=http://demo-admin-dev.localhost
```

In this example the request `/public/view/54` will be saved to the file `/opt/samples/test.pdf`. With the `--save` option the file will be uploaded to the storages services. And the `--base-url=http://demo-admin-dev.localhost` option will generate an url to the user. Is the `base-url` option is defined the file will be saved even if the `--save` is not specified.   

## Admin

### Backup

The command downloads the configuration (JSON files for content types, environments, ...) and documents (JSON files) for all managed content types.

Be cautious that the document are downloaded from the elasticsearch's default indexes. So ensure that your elasticsearch's indexes are well synchronized. Only the last finalized revision will be archived.

```bash
Usage:
  ems:admin:backup [options]

Options:
      --export                               Backup elasticMS's configs in JSON files (dry run by default)
      --export-folder[=EXPORT-FOLDER]        Global export folder (can be overwritten per type of exports)
      --configs-folder[=CONFIGS-FOLDER]      Export configs folder
      --documents-folder[=DOCUMENTS-FOLDER]  Export documents folder
```

The environment variable [`EMS_EXCLUDED_CONTENT_TYPES`](parameters.md#ems_excluded_content_types) can be used in order to exclude documents from a list content types.

### Command

Allow to rum command on a remote elasticMS. You need to be logged in first with the command `ems:admin:login`:

```bash
Usage:
  ems:admin:command <remote-command>

Arguments:
  remote-command        Command to remote execute
```

Example:

```bash
php bin/console ems:admin:command 'ems:env:rebuild preview'
```
