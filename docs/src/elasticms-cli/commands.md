# EMS (CommonBundle) Commands

## Admin

### Backup

The command downloads the configuration (JSON files for content types, environments, ...) and
documents (JSON files) for all managed content types.

Be cautious that the document are downloaded from the elasticsearch's default indexes. So ensure
that your elasticsearch's indexes are well synchronized. Only the last finalized revision will be
archived.

```bash
Usage:
  ems:admin:backup [options]

Options:
      --export                               Backup elasticMS's configs in JSON files (dry run by default)
      --export-folder[=EXPORT-FOLDER]        Global export folder (can be overwritten per type of exports)
      --configs-folder[=CONFIGS-FOLDER]      Export configs folder
      --documents-folder[=DOCUMENTS-FOLDER]  Export documents folder
```

The environment variable [`EMS_EXCLUDED_CONTENT_TYPES`](parameters.md#ems_excluded_content_types)
can be used in order to exclude documents from a list content types.

### Command

Allow to rum command on a remote elasticMS. You need to be logged in first with the command
`ems:admin:login`:

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

## Import

### File

With this command you can upload a folder to a content-type. If the merge options is set to `false`,
the rawData will be replaced. It will send async index requests, the responses are parsed when the
flush-size is reached (default 100).

```bash
Description:
  Import an Excel file or a CSV file, one document per row.

Usage:
  emscli:import:file [options] [--] <file> <content-type>
  emscli:file-reader:import

Arguments:
  file                             File path (xlsx or csv)
  content-type                     Content type target

Options:
      --limit=LIMIT                Limit the rows
      --config=CONFIG              Config(s) json, file path or hash (multiple values allowed)
      --dry-run                    Just do a dry run
      --merge=MERGE                Perform a merge or replace [default: true]
      --flush-size=FLUSH-SIZE      Flush size for the queue [default: 100]
      --chunk-size=CHUNK-SIZE      Chunk size for processing rows [default: 100]
      --scroll-size=SCROLL-SIZE    Search scroll size [default: 100]
      --lazy                       Lazy index will only call post-processing on source element
      --digest-field=DIGEST-FIELD  Only index not digested rows
```

### Database

With this command you can upload a database table to a content-type. If the merge options is set to
`false`, the rawData will be replaced. It will send async index requests, the responses are parsed
when the flush-size is reached (default 100).

```bash
Description:
  Import an database table, one document per row.

Usage:
  emscli:import:database [options] [--] <table> <content-type>

Arguments:
  table                            Database table name.
  content-type                     Content type target

Options:
      --config=CONFIG              Config(s) json, file path or hash (multiple values allowed)
      --dry-run                    Just do a dry run
      --merge=MERGE                Perform a merge or replace [default: true]
      --flush-size=FLUSH-SIZE      Flush size for the queue [default: 100]
      --chunk-size=CHUNK-SIZE      Chunk size for processing rows [default: 100]
      --scroll-size=SCROLL-SIZE    Search scroll size [default: 100]
      --lazy                       Lazy index will only call post-processing on source element
      --digest-field=DIGEST-FIELD  Only index not digested rows
```

### Config

You pass multiple config options as string, file path or hash. They will be merged together.

```bash
php bin/console emscli:file-reader:import pages.csv page \
--config='{"generate_hash": true}' \
--config='e509a485f786583351cb81911f49ed6a78e28262' \
--config='./var/files/config.json'
```

- `delimiter`: ?string (default=null)
    - Define the csv delimiter
- `default_data`: array (default=[])
    - Data array will be merged with row data
- `query`: array (default=null)
    - Base query for searching the existing documents, use for delete missing documents and
      alignment.
- `delete_missing_documents`: bool (default=false)
    - The command will delete content type document that are missing in the import file
- `lowercase_headers`: bool (default=false)
    - The keys in the row array will be converted in lowercase
- `encoding`: ?string (default=null)
    - Define the input file encoding
- `exclude_rows`: int[] (default=[])
    - Pass an array of row positions to exclude (0 is first row, -1 is last row)
- `exclude_expression`: ?string (default=null)
    - Exclude rows based on and expression (context row array and string ouuid)
    - example skip rows where the ID column is empty: `empty(trim(row[\"id\"]))`
- `generate_hash`: bool (default=false)
    - Use the OUUID column and the content type name in order to generate a "better" ouuid
- `ouuid_expression`: ?string (default="row['ouuid']")
    - Expression language apply to excel rows in order to identify the document by its ouuid. If
      equal to null new document will be created
- `ouuid_version_expression`: ?string (default=null)
    - Expression language apply to excel rows for generating a version uuid saved in `_version_uuid`
- `ouuid_prefix`: ?string (default=null)
    - The ouuid will prefix with this value before hashing
- `align_environments`: array (default[])
    - Example `[{'source':'preview','target':'live'},{'source':'preview','target':'default'}]`, if
      query is defined this will be used as search-query for the alignment.
- `mime_type`: string or null (default null)
    - For the mime_type of the import file

### Example

I.e.:
`ems:import:file --config='{"ouuid_expression":null}' /home/dockerce/documents/promo/features.xlsx feature`

During the import an associate array containing the Excel row is available in the source
`_sync_metadata`.

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
