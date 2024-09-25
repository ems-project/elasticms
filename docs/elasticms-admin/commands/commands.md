# Commands

<!-- TOC -->
* [Commands](#commands)
  * [EMSCO (CoreBundle)](#emsco-corebundle)
    * [Asset](#asset)
      * [Refresh file fields](#refresh-file-fields)
    * [Content Type](#content-type)
      * [Content Type switch default environment](#content-type-switch-default-environment)
      * [Content Type transform](#content-type-transform)
    * [Environment](#environment)
      * [Environment align](#environment-align)
      * [Environment unpublish](#environment-unpublish)
    * [Release](#release)
      * [Release create](#release-create)
      * [Release publish](#release-publish)
    * [Revision](#revision)
      * [Revision archive](#revision-archive)
      * [Revision copy](#revision-copy)
      * [Revision delete](#revision-delete)
      * [Revision discard](#revision-discard)
      * [Revision task create](#revision-task-create)
      * [Revision task notification mail](#revision-task-notification-mail)
    * [User](#user)
      * [User activate](#user-activate)
      * [User change](#user-change)
      * [User create](#user-create)
      * [User deactivate](#user-deactivate)
      * [User demote](#user-demote)
      * [User promote](#user-promote)
      * [User update option](#user-update-option)
    * [XLIFF](#xliff-)
      * [XLIFF extract](#xliff-extract)
      * [XLIFF update](#xliff-update)
<!-- TOC -->

## EMSCO (CoreBundle)

### Asset

#### Refresh file fields

This command ensure that all file fields, for all revisions, are using the last asset's fields:

 * _hash
 * _size
 * _algo
 * _type
 * _name

That will have to be launch at least once between August 2024 and the release 7.x. 
By then the fields `filename`, `filesize`, `sha1` and `mimetype` are deprecated.

This command regenerate resized images in order to avoid too much memory consumption on image generation.
So you might consider to launch this commend if you adjust the `EMSCO_IMAGE_MAX_SIZE` environment variable.

**Cautions**

This command will mark all revision as updated by the `SYSTEM_REFRESH_FILE_FIELDS` user in the admin UI.

```bash
Usage:
  emsco:asset:refresh-file-fields

```

### Content Type

#### Content Type switch default environment

Switch the default environment for a given content type.
Each revision published in the default environment will be marked as published in the provided environment.
Each revision published in the provided environment will be mark as published in the default environment.
The content type's environment by default will be set to the provided environment. 

**Cautions**

* This command should never be run in a production environment without a good backup.
* Affected environments must be rebuilded just after. As many content types might be switched, the command doesn't automatically rebuilding them. After this command affected indexes will be inconsistent.



```bash
Usage:
  emsco:contenttype:switch-default-env <contentType> <target-environment>

Arguments:
  contentType           ContentType
  target-environment    Target environment

```

#### Content Type transform

Apply defined field transformers in the migration mapping.

`@todo add documentation content transformers`

```bash
Usage:
  emsco:contenttype:transform [options] [--] <content-type>

Arguments:
  content-type                         ContentType name

Options:
      --scroll-size=SCROLL-SIZE        Size of the elasticsearch scroll request
      --scroll-timeout=SCROLL-TIMEOUT  Time to migrate "scrollSize" items i.e. 30s or 2m
      --search-query[=SEARCH-QUERY]    Query used to find elasticsearch records to transform [default: "{}"]
      --dry-run                        Dry run
      --user=USER                      Lock user [default: "SYSTEM_CONTENT_TRANSFORM"]
```

### Environment

#### Environment align

Align an environment from another one

```bash
Usage:
  emsco:environment:align [options] [--] <source> <target>

Arguments:
  source                               Environment source name
  target                               Environment target name

Options:
      --snapshot                       If set, the target environment will be tagged as a snapshot after the alignment
      --publication-template           If set, the environment publication template will be used
      --force                          If set, the task will be performed (protection)
      --scroll-size=SCROLL-SIZE        Size of the elasticsearch scroll request
      --scroll-timeout=SCROLL-TIMEOUT  Time to migrate "scrollSize" items i.e. 30s or 2m
      --search-query[=SEARCH-QUERY]    Query used to find elasticsearch records to import [default: "{}"]
      --user=USER                      Lock user [default: "SYSTEM_ALIGN"]
      --dry-run                        Dry run
```

#### Environment unpublish

Unpublish revision from an environment

You cannot unpublish:
- revisions from their default environment, you should use '**emsco:revision:archive**' for this.
- revisions with only one environment. This can happen when the revision is archived in the default environment.


```bash
Usage:
  emsco:environment:unpublish [options] [--] <environment>

Arguments:
  environment                          Environment name

Options:
      --force                          If set, the task will be performed (protection)
      --scroll-size=SCROLL-SIZE        Size of the elasticsearch scroll request
      --scroll-timeout=SCROLL-TIMEOUT  Time to migrate "scrollSize" items i.e. 30s or 2m
      --search-query[=SEARCH-QUERY]    Query used to find elasticsearch records to import [default: "{}"]
      --user=USER                      Lock user [default: "SYSTEM_ALIGN"]
      --dry-run                        Dry run
```

### Release

#### Release create

Add documents for a given contenttype in a release

```bash
Usage:
  emsco:release:create [options] [--] <contentType> <target>

Arguments:
  contentType           ContentType
  target                Target managed alias name

Options:
      --query[=QUERY]   ES query [default: "{}"]
```

#### Release publish

Publish scheduled releases

```bash
Usage:
  emsco:release:publish
```

### Revision

#### Revision archive

```bash
Usage:
  emsco:revision:archive [options] [--] <content-type>
  
Arguments:
  content-type                           ContentType name

Options:
      --modified-before=MODIFIED-BEFORE  Y-m-dTH:i:s (2019-07-15T11:38:16)
      --scroll-size=SCROLL-SIZE          Size of the elasticsearch scroll request
      --scroll-timeout=SCROLL-TIMEOUT    Time to migrate "scrollSize" items i.e. 30s or 2m
      --search-query[=SEARCH-QUERY]      Query used to find elasticsearch records to import [default: "{}"]
```

#### Revision copy

Copy revisions from search query

The json from `merge-raw-data` will be merged on the copied revisions.

```bash
Usage:
  emsco:revision:copy [options] [--] <environment> <search-query> [<merge-raw-data>]
  
Arguments:
  environment                          environment name
  search-query                         search query
  merge-raw-data                       json merge raw data

Options:
      --scroll-size=SCROLL-SIZE        Size of the elasticsearch scroll request
      --scroll-timeout=SCROLL-TIMEOUT  Timeout "scrollSize" items i.e. 30s or 2m
```

#### Revision delete

Delete all/oldest revisions for content type(s).

In `oldest` mode, only not published revisions will be removed and keeping revisions between publications.

> This a hard delete, no rollback possible.

```bash
emsco:revision:delete asset page # Removing all revisions for asset and page contentType
emsco:revision:delete asset page --mode=oldest # Removing oldest revisions for asset and page contentType
emsco:revision:delete all # Removing all revisions
emsco:revision:delete all --mode=oldest # Removing all oldest revisions
```

It's also possible to delete revision by passing a query. In this case the provided elasticsearch query is run all all OUUIDs are collected.
Base on those OUUIDs all revisions in the database and all documents in all managed indexes are deleted.

```bash
 php bin/console ems:rev:dele --mode=by-query --query='{"index":"ems_default","body":{"query":{"bool":{"must":[{"term":{"host":{"value":"domain.tld","boost":1}}},{"terms":{"_contenttype":["audit"]}}]}}}}'
```


```bash
Usage:
  emsco:revision:delete [options] [--] [<content-types>...]
  ems:contenttype:delete

Arguments:
  content-types         contentType names or "all"

Options:
      --mode=MODE       mode for deletion [all,oldest,by-query] [default: "all"]
      --query[=QUERY]   query to use in by-query mode
```

#### Revision discard

Discard drafts for content types

```bash
Usage:
  emsco:revision:discard-draft [options] [--] [<content-types>...]
  
Arguments:
  content-types         ContentType names

Options:
      --force           Also discard drafts with auto-saved content
      --older=OLDER     Discard revision that are older than this  (time format) [default: "-5minutes"]
```

#### Revision task create

Create revision task based on ES query

The command will not create tasks:
* if tasks are not enabled `@todo task documentation`
* if the revision has a current task or planned tasks

```bash
Usage:
  emsco:revision:task:create [options] [--] <environment>

Arguments:
  environment                          

Options:
      --task=TASK                      {\"title\":\"title\",\"assignee\":\"username\",\"description\":\"optional\"}
      --field-assignee=FIELD-ASSIGNEE  assignee field in es document
      --requester=REQUESTER            requester
      --field-deadline=FIELD-DEADLINE  deadline field in es document
      --not-published=NOT-PUBLISHED    only for revisions not published in this environment
      --scroll-size=SCROLL-SIZE        Size of the elasticsearch scroll request
      --scroll-timeout=SCROLL-TIMEOUT  Time to migrate "scrollSize" items i.e. 30s or 2m
      --search-query[=SEARCH-QUERY]    Query used to find elasticsearch records to import [default: "{}"]
```

#### Revision task notification mail

Send a notification mail to assignees, creators and task managers.

Creates a list of all active tasks, ordered by the deadline. 
You can define the deadline start and end with the deadline options.

Loops over all tasks and checks:
- If the task is in progress, add to the list of tasks for the assignee
- If the task is completed, add to the list of tasks for the creator
- If include-task-managers is true, add to the list of tasks for the manager

For each receiver (assignee, creator, manager), we check if email notification is turn on.
By default, we only send a list of the 10 first result (can be increase with limit option).

```bash
Usage:
  emsco:revision:task:notification-mail [options]

Options:
      --subject=SUBJECT                Set mail subject [default: "notification tasks"]
      --deadline-start=DEADLINE-START  Start deadline from now "-1 days"
      --deadline-end=DEADLINE-END      End deadline from now "+1 days"
      --include-task-managers          Include task admins/managers
      --limit=LIMIT                    limit the results inside mail [default: 10]
```

### User

#### User activate

Activate a user

```bash
Usage:
  emsco:user:activate <username>

Arguments:
  username              The username
```

#### User change

Change the password of a user

```bash
Usage:
  emsco:user:change-password <username> <password>

Arguments:
  username              The username
  password              The password
```

#### User create

Create a user

```bash
Usage:
  emsco:user:create [options] [--] <username> <email> <password>

Arguments:
  username              The username
  email                 The email
  password              The password
```

#### User deactivate

Deactivate a user

```bash
Usage:
  emsco:user:deactivate <username>

Arguments:
  username              The username
```

#### User demote

Demote a user by removing a role

```bash
Usage:
  emsco:user:demote [options] [--] <username> [<role>]

Arguments:
  username              The username
  role                  The role

Options:
      --super           Instead specifying role, use this to quickly add the super administrator role
```

#### User promote

Promotes a user by adding a role

```bash
Usage:
  emsco:user:promote [options] [--] <username> [<role>]

Arguments:
  username              The username
  role                  The role

Options:
      --super           Instead specifying role, use this to quickly add the super administrator role
```

#### User update option

```
Description:
  Update a user option.

Usage:
  emsco:user:update-option [options] [--] <option> <value>

Arguments:
  option                simplified_ui|allowed_configure_wysiwyg|custom_options
  value                 value for updating

Options:
      --email[=EMAIL]   use wildcard % (%@example.dev)

Help:
  The emsco:user:update-option command changes an option of a user(s):

    Enable "simplified_ui" for all users
    php bin/console emsco:user:update-option simplified_ui true

    Enable "allowed_configure_wysiwyg" for all users
    php bin/console emsco:user:update-option allowed_configure_wysiwyg true

    Set country "Belgium" for all users with a .be email address
    php bin/console emsco:user:update-option custom_options '{"country":"Belgium"}' --email='%.be'
```

### XLIFF 

The core supports XLIFF exports and imports to have some content translated by a translation office.

> **LIMITATIONS**
> 
> At this point elasticms only supports XLIFF translation in separated documents. In other words a document is associated to one and only one language. Those documents needs:
> 
> - A keyword field to identify the document's locale i.e. a `locale` field contains values like `'fr'`, `'en'`
> - A keyword field to link documents that are translation of each other. It can be a `menu_uid` referring to a JSON Menu entry or a data link
> 
> So the couple of those two fields must be unique by environment.
> 
> A support where fields such as `title_fr` and `title_nl` are in the same document is feasible but is not yet supported


#### XLIFF extract

This command generates an XML in a [XLIFF format 1.2](http://docs.oasis-open.org/xliff/xliff-core/xliff-core.html).

This command will
- extract the fields `description`, `title_short` and `title`
- for the document with the OUUID `db27a1da21b8d9c556abe67451007cd0ad80c54b` if it exists in the `next` environment
- The expected locale of this document should be `nl`
- It will try to identify a `de` document having the same `translation_id`  in the `latest`
  - The translatable fields of this document will be used as default target value
- It will check if something has changed for the current revision of the document, for the translatable fields, of the revision
  - in the `latest` environment with the same OUUID
  - If nothing changed, and if a target is defined it will mark the target's state as `final`

```
emsco:xliff:extract next '{"query":{"bool":{"must":[{"term":{"_id":{"value":"db27a1da21b8d9c556abe67451007cd0ad80c54b"}}},{"terms":{"_contenttype":["page","template"]}}]}}}' nl de description title_short title --base-url=http://example.localhost --target-environment=latest
```

```bash
Usage:
  emsco:xliff:extract [options] [--] <source-environment> <search-query> <source-locale> <target-locale> [<fields>...]

Arguments:
  source-environment                             Environment with the source documents
  search-query                                   Query used to find elasticsearch records to extract from the source environment
  source-locale                                  Source locale
  target-locale                                  Target locale
  fields                                         List of content type\s fields to extract. Use the pattern %locale% if required. Use the `.` to separate nested fields from their parent. Use `json:` `id_key:` and/or `base64;` to decode a field. You can also use `*` as wild char and `|` to list children fields  E.g. `%locale%.json:id_key:content.object.title|content` or `[%locale%][json:id_key:content][object][title|content]`

Options:
      --bulk-size=BULK-SIZE                      Size of the elasticsearch scroll request [default: 500]
      --target-environment[=TARGET-ENVIRONMENT]  Environment with the target documents
      --xliff-version[=XLIFF-VERSION]            XLIFF format version: 1.2 2.0 [default: "1.2"]
      --basename[=BASENAME]                      XLIFF export file basename [default: "ems-extract.xlf"]
      --base-url[=BASE-URL]                      Base url, in order to generate a download link to the XLIFF file
      --locale-field[=LOCALE-FIELD]              Field containing the locale
      --encoding[=ENCODING]                      Encoding used to generate the XLIFF file [default: "UTF-8"]
      --translation-field[=TRANSLATION-FIELD]    Field containing the translation field
      --with-baseline                            The baseline has been checked and can be used to flag field as final
      --mail-subject[=MAIL-SUBJECT]              Mail subject [default: "A new XLIFF has been generated"]
      --mail-to[=MAIL-TO]                        A comma seperated list of emails where to send the XLIFF
      --mail-cc[=MAIL-CC]                        A comma seperated list of emails where to send, in carbon copy, the XLIFF
      --mail-reply-to[=MAIL-REPLY-TO]            A comma seperated list of emails where to reply
```

#### XLIFF update

If a `publish-to` is specified in the options, the command will check if something as changed in source fields between the default environment and the `publish-to` one. If nothing changed and if the target fields are defined those target's sate will be marked as `'final'`

This command will:
- Load the XLIFF file passed as argument
- Each source document's revisions are identified in the XLIFF file. That exact revision will be used to generate a new revision for the target locale (defined in the XLIFF file)
- The target OUUID will be identified via an elasticsearch query looking for a single document
  - In the `latest` environment (as it's specified in the `publish-to` option, otherwise it will look in the default environment of the revision)
  - Having the same `translation_id` field value
  - Having the locale field value set to target locale
  - If not found a new document with a brand new OUUID will be generate
- As a `publish-to` environment is defined, translated revisions will be directly published in that environment
- As the archive option is set the translated revisions will be unpublished from there default environment and mark as archived
  - This option is available only if a `publish-to` environment is defined


 ```
emsco:xliff:update /tmp/ems-extract-BfHeoa.xlf --publish-to=latest --archive
```

```bash
Usage:
  emsco:xliff:update [options] [--] <xliff-file>

Arguments:
  xliff-file                                   Input XLIFF file

Options:
      --publish-to[=PUBLISH-TO]                If defined the revision will be published in the defined environment
      --archive                                If set another revision will be flagged as archived
      --locale-field[=LOCALE-FIELD]            Field containing the locale
      --translation-field[=TRANSLATION-FIELD]  Field containing the translation field
      --dry-run                                If set nothing is saved in the database
      --current-revision-only                  Translations will be updated only is the source revision is still a current revision
      --base-url[=BASE-URL]                    Base url, in order to generate a download link to the error report
```
