# EMS (CommonBundle) Commands

<!-- TOC -->

* [EMS Commands](#ems-commonbundle-commands)
    * [Admin](#admin)
        * [Backup](#backup)

<!-- TOC -->

### Admin

#### Backup

The command downloads the configuration (JSON files for content types, environments, ...) and documents (JSON files) for
all managed content types.

Be cautious that the document are downloaded from the elasticsearch's default indexes. So ensure that your
elasticsearch's indexes are well synchronized. Only the last finalized revision will be archived.

```bash
Usage:
  ems:admin:backup [options]

Options:
      --export                               Backup elasticMS's configs in JSON files (dry run by default)
      --export-folder[=EXPORT-FOLDER]        Global export folder (can be overwritten per type of exports)
      --configs-folder[=CONFIGS-FOLDER]      Export configs folder
      --documents-folder[=DOCUMENTS-FOLDER]  Export documents folder
```

The environment variable [`EMS_EXCLUDED_CONTENT_TYPES`](parameters.md#ems_excluded_content_types) can be used in order
to exclude documents from a list content types.

#### Command

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

## File Reader

### Import

With this command you can upload a folder to a content-type.

```bash
Description:
  Import an Excel file or a CSV file, one document per row

Usage:
  emscli:file-reader:import [options] [--] <file> <content-type>

Arguments:
  file                  File path (xlsx or csv)
  content-type          Content type target

Options:
      --config=CONFIG   Config(s) json, file path or hash (multiple values allowed)
      --dry-run         Just do a dry run
```

### Config

You pass multiple config options as string, file path or hash.
They will be merged together.

```bash
php bin/console emscli:file-reader:import pages.csv page \
--config='{"generateHash": true}' \
--config='e509a485f786583351cb81911f49ed6a78e28262' \
--config='./var/files/config.json'
```

* `generateHash`: Use the OUUID column and the content type name in order to generate a "better" ouuid
    * bool (default = false)
* `deleteMissingDocuments`: The command will delete content type document that are missing in the import file
    * bool (default = false)
* `ouuidExpression`: Expression language apply to excel rows in order to identify the document by its ouuid. If equal to
  null new document will be created
    * null or string (default "row['ouuid']")
* `encoding`: Define the input file encoding
    * null or string (default = null)

### Example

I.e.: `ems:file:impo --config='{"ouuidExpression":null}' /home/dockerce/documents/promo/features.xlsx feature`

During the import an associate array containing the Excel row is available in the source `_sync_metadata`.

Example of a field post processing to import a release data:

```twig
{% if finalize and rootObject._sync_metadata["release"] is defined %}
    {{ rootObject._sync_metadata["release"]|json_encode|raw }}
{% endif %}
```

Example to import data into a multiplexed title field:

```twig
{% if finalize and rootObject._sync_metadata["title_#{form.parent.name}"] is defined %}
    {{ rootObject._sync_metadata["title_#{form.parent.name}"]|json_encode|raw }}
{% endif %}
```