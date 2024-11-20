# Commands

## Admin

### Backup

The command downloads the configuration (JSON files for content types,
environments, ...) and documents (JSON files) for all managed content types.

Be cautious that the document are downloaded from the elasticsearch's default
indexes. So ensure that your elasticsearch's indexes are well synchronized. Only
the last finalized revision will be archived.

The environment variable [
`EMS_EXCLUDED_CONTENT_TYPES`](parameters.md#ems_excluded_content_types) can be
used in order to exclude documents from a list content types.

[//]: auto-generated-command

```bash
ems:admin:backup [options]
```

**Options**

* ```--export```
  > Backup elasticMS's configs in JSON files (dry run by default)
* ```--export-folder```
  > Global export folder (can be overwritten per type of exports)
* ```--configs-folder```
  > Export configs folder
* ```--documents-folder```
  > Export documents folder
* ```--configs```
  > Export elasticMS's configs only
* ```--documents```
  > Export elasticMS's documents only

[//]: auto-generated-command

### Command

Allow to rum command on a remote elasticMS. You need to be logged in first with
the command `ems:admin:login`.

[//]: auto-generated-command

```bash
ems:admin:command <remote-command>
```

**Arguments**

* ```remote-command``` required
  > Command to remote execute

[//]: auto-generated-command

::: code-group

```bash [Example]
php bin/console ems:admin:command 'ems:env:rebuild preview'
```

:::

### Create

[//]: auto-generated-command

```bash
ems:admin:create [options] [--] <config-type> [<json-path>]
```

**Arguments**

* ```config-type``` required
  > Type of config to update
* ```json-path```
  > Path to the JSON file or JSON file name

**Options**

* ```--folder```
  > Export folder

[//]: auto-generated-command

### Delete

[//]: auto-generated-command

```bash
ems:admin:delete <config-type> <entity-name>
```

**Arguments**

* ```config-type``` required
  > Type of config to update
* ```entity-name``` required
  > Entity's name to update

[//]: auto-generated-command

### Get

[//]: auto-generated-command

```bash
ems:admin:get [options] [--] <config-type>
```

**Arguments**

* ```config-type``` required
  > Type of configs to get

**Options**

* ```--export```
  > Export configs in JSON files
* ```--folder```
  > Export folder

[//]: auto-generated-command

### Job

[//]: auto-generated-command

```bash
ems:admin:job <job-id>
```

**Arguments**

* ```job-id``` required
  > Job's ID or path to a json file or to a job admin's file name

[//]: auto-generated-command

### Login

[//]: auto-generated-command

```bash
ems:admin:login [options] [--] [<base-url>]
```

**Arguments**

* ```base-url```
  > Elasticms base url (default: EMS_BACKEND_URL)

**Options**

* ```-u``` ```--username```
  > username
* ```-p``` ```--password```
  > password

[//]: auto-generated-command

### Next-job

[//]: auto-generated-command

Contact the admin to check if a job is planned for the given tag and run it locally

```bash
ems:admin:next-job [options] [--] <tag>
```

**Arguments**

* ```tag``` required
  > Tag that identifies the scheduled jobs

**Options**

* ```--silent```
  > Dont echo outputs in the console (only in the admin job logs)

[//]: auto-generated-command

### Restore

[//]: auto-generated-command

```bash
ems:admin:restore [options]
```

**Options**

* ```--force```
  > Without this option changes will be only tracked
* ```--import-folder```
  > Global import folder (can be overwritten per type of exports)
* ```--configs-folder```
  > Import configs folder
* ```--documents-folder```
  > Import documents folder
* ```--configs```
  > Restore elasticMS's configs only
* ```--documents```
  > Restore elasticMS's documents only

[//]: auto-generated-command

### Update

[//]: auto-generated-command

```bash
ems:admin:update [options] [--] <config-type> <entity-name> [<json-path>]
```

**Arguments**

* ```config-type``` required
  > Type of config to update
* ```entity-name``` required
  > Entity's name to update
* ```json-path```
  > Path to the JSON file

**Options**

* ```--folder```
  > Export folder

[//]: auto-generated-command

## Batch

[//]: auto-generated-command

Run commands defined in twig

```bash
ems:batch [options] [--] <template>
```

**Arguments**

* ```template``` required
  > template name, path or twig code

**Options**

* ```--context```
  > context passed to twig

[//]: auto-generated-command

- The template must output a valid json list of commands.
- If the template contains a block named ```execute```, only this block will be
  rendered.

::: code-group

```bash [Example]
# define template twig namespace
ems:batch "@EMSCH/template_ems/batch.json.twig"

# define template by path
ems:batch ../demo/skeleton/template_ems/batch.json.twig

# define template in command
ems:batch '["ems:version", "ems:health-check]'

# pass context to twig template 
ems:batch "@EMSCH/template_ems/batch_context.json.twig" --context='{"envName":"live"}'
```

```twig [Template]
{% block execute %}
  {{ ["ems:environment:rebuild #{envName}"]|json_encode|raw }}
{% endblock %}
```

:::

## Clear

[//]: auto-generated-command

Clear doctrine logs

```bash
ems:logs:clear [options]
```

**Options**

* ```--before``` default: "-1week"
  > CLear logs older than the strtotime (-1day, -5min, now)
* ```--channel``` default: ["app"] multiple values allowed
  > Define channels default [app]

[//]: auto-generated-command

::: code-group

```bash [Example]
# Remove all logs created before now for the channels `app` and `core`
ems:logs:clear --before=now --channel=app --channel=core
```

:::

## Clear-cache

[//]: auto-generated-command

Clear storage services caches

```bash
ems:storage:clear-cache
```

[//]: auto-generated-command

## Collect

[//]: auto-generated-command

```bash
ems:metric:collect [options]
```

**Options**

* ```--clear```
  > clear metrics before collecting

[//]: auto-generated-command

## Curl

[//]: auto-generated-command

Curl an internal resource

```bash
ems:curl [options] [--] <url> <filename>
```

**Arguments**

* ```url``` required
  > Absolute url to the resource
* ```filename``` required
  > Filename where to save the ouput

**Options**

* ```--method``` default: "GET"
  > HTTP method (GET, POST)
* ```--base-url```
  > Base url, in order to generate a download link to the file
* ```--save```
  > Save the to the file storages

[//]: auto-generated-command

::: code-group

```bash [Example]
# This command allows you to save request to a file. 

# In this example the request `/public/view/54` will be saved to the file `/opt/samples/test.pdf`. 
# With the `--save` option the file will be uploaded to the storages services. 
# And the `--base-url=http://demo-admin-dev.localhost` option will generate an url to the user. 
# Is the `base-url` option is defined the file will be saved even if the `--save` is not specified.   

ems:curl /public/view/54 /opt/samples/test.pdf --save --base-url=http://demo-admin-dev.localhost
```

:::

## Document

### Download

[//]: auto-generated-command

```bash
ems:document:download [options] [--] <content-type>
```

**Arguments**

* ```content-type``` required
  > Content-type's name to download

**Options**

* ```--folder```
  > Export folder

[//]: auto-generated-command

### Upload

[//]: auto-generated-command

```bash
ems:document:upload [options] [--] <content-type>
```

**Arguments**

* ```content-type``` required
  > Content-type's name to update

**Options**

* ```--folder```
  > Folder to scan for JSON files
* ```--dump-file```
  > Will upload the specified elasticdump file instead of the JSON files in the folder
* ```--only-missing```
  > Only create missing documents

[//]: auto-generated-command

## File-structure

### Publish

[//]: auto-generated-command

Publish the file structure of an ElasticMS archive into a S3 bucket

```bash
ems:file-structure:publish [options] [--] <hash> <target>
```

**Arguments**

* ```hash``` required
  > Elasticsearch index
* ```target``` required
  > Target (S3 bucket)

**Options**

* ```--s3-credential```
  > S3 credential in a JSON format
* ```--force```
  > The target is synchronize even if the target looks already synchronized or if the target looks out of sync
* ```--admin```
  > Use admin api

[//]: auto-generated-command

### Pull

[//]: auto-generated-command

Pull an EMS archive into a local folder (and overwrite it)

```bash
ems:file-structure:pull [options] [--] <hash> <folder>
```

**Arguments**

* ```hash``` required
  > Hash of the ElasticMS Archive
* ```folder``` required
  > Target folder

**Options**

* ```--admin```
  > Pull from admin

[//]: auto-generated-command

### Push

[//]: auto-generated-command

Push an EMS Archive file structure into a EMS Admin storage services (via the API)

```bash
ems:file-structure:push [options] [--] <folder>
```

**Arguments**

* ```folder``` required
  > Source folder

**Options**

* ```--admin```
  > Push to admin

[//]: auto-generated-command

## Status

[//]: auto-generated-command

Returns the health status of the elasticsearch cluster and of the different storage services.

```bash
ems:status [options]
```

**Options**

* ```-ss``` ```--silent```
  > Shows only warning and error messages
* ```--wait-for-status```
  > One of green, yellow or red. Will wait (until the timeout provided) until the status of the cluster changes to the one provided or better, i.e. green > yellow > red.
* ```--timeout``` default: "10s"
  > Time units. Specifies the period of time to wait for a response. If no response is received before the timeout expires, the request will returns the status red.

[//]: auto-generated-command

## Version

[//]: auto-generated-command

```bash
ems:version [<short-name>]
```

**Arguments**

* ```short-name``` default: "common"
  > Package composer short name

[//]: auto-generated-command

