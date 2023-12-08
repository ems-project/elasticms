# File reader

## Import command

With this command you can upload a folder to a media-file content-type:

```
Usage:
  emscli:file-reader:import [options] [--] <file> <content-type>

Arguments:
  file                                       File path (xlsx or csv)
  content-type                               Content type target

Options:
      --dry-run                              Just do a dry run
      --generate-hash                        Use the OUUID column and the content type name in order to generate a "better" ouuid
      --delete-missing-document              The command will delete content type document that are missing in the import file
      --ouuid-expression[=OUUID-EXPRESSION]  Expression language apply to excel rows in order to identify the document by its ouuid. If equal to null new document will be created [default: "row['ouuid']"]
```

I.e.: `ems:file:impo --ouuid-expression=null /home/dockerce/documents/promo/features.xlsx feature`

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
