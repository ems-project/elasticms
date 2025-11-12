# Media file

## Synchronize command

With this command you can upload a folder to a media-file content-type:

```
Usage:
  emscli:media-library:sync [options] [--] <folder>

Arguments:
  folder                                               Folder path

Options:
      --content-type[=CONTENT-TYPE]                    Media Library content type (default: media_file) [default: "media_file"]
      --folder-field[=FOLDER-FIELD]                    Media Library folder field (default: media_folder) [default: "media_folder"]
      --path-field[=PATH-FIELD]                        Media Library path field (default: media_path) [default: "media_path"]
      --file-field[=FILE-FIELD]                        Media Library file field (default: media_file) [default: "media_file"]
      --dry-run                                        Just do a dry run
      --excel-file[=EXCEL-FILE]                        Path to an excel file containing meta data
      --excel-sheet-name[=EXCEL-SHEET-NAME]            Excel sheet name (the active sheet will be used if not defined)
      --locate-row-expression[=LOCATE-ROW-EXPRESSION]  Expression language apply to excel rows in order to identify the file by its filename [default: "row['filename']"]
```

I.e.: `ems:media:sync media-file`

It's also possible to join metadata by specify a excel filepath. That excel file must have a sheet with a header row and a way to identify the file with its relative filepath via a Symfony Expression language.

I.e. with the following sheet:

| Folder  | Meta_1             | Meta 2 | File           |
|---------|:-------------------|--------|----------------|
| folder  | HAAA               | foobar | IMG_5008.JPG   |

And the command  `ems:media:sync media-file --excel-file=meta.xlsx --locate-row-expression="'/'~row['Folder']~'/'~row['File']"`

If the  folder `./media-file` contains a `folder/IMG_5008.JPG` file the admin will receive the following raw data:

```json
{
  "_contenttype": "media_file",
  "_sync_metadata": {
    "File": "IMG_5008.JPG",
    "Folder": "folder",
    "Meta 2": "foobar",
    "Meta_1": "HAAA"
  },
  "media_file": {
    "filename": "IMG_5008.JPG",
    "filesize": 67192,
    "mimetype": "image/jpeg",
    "sha1": "4ac644df5e36f239a4e877aa866e7ec5442573f7"
  },
  "media_folder": "/folder/",
  "media_path": "/folder/IMG_5008.JPG"
}
```

It's easy in the content type to use those data in field's post process:

```twig
{% if _source._sync_metadata.Meta_1 is defined %}
    {{ _source._sync_metadata.Meta_1|json_encode|raw }}
{% endif %}
```

Remarks about the `locate-row-expression`:

 - The expression output result must start by a '/'
 - It's case-sensitive
