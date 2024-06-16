# Synchronize a directory structure and publish into an S3 bucket

First of all you must configure a [`directory`](directory.json) content type in an elasticms admin.

Each document of that `directory` content type can contains a file structure.

## Initialize a document

Just click on `New Directory`and fill the Identifier field. `Demo` in this example. Not need to fill the `Structure` fields.

## Push a local folder to ElasticMS

The following command is available in all ElacticMS applications (Admin, Web and CLI) but required that your are logged in first (`ems:admin:login`).

```shell
php bin/console ems:file-structure:push demo ../../demo --term-field=identifier
```

## Update a local folder from ElasticMS


```shell
php bin/console ems:file-structure:pull demo ../../demo --term-field=identifier
```

## Publish a file structure to a S3 bucket

There is another command that allows you to publish a `directory` document, for a revision publish in some  environment into a S3 bucket.

```shell
php bin/console ems:file-structure:publish demo website_preview website --term-field=identifier --s3-credential='{"version":"2006-03-01","credentials":{"key":"accesskey","secret":"secretkey"},"region":"us-east-1","endpoint":"http://localhost:9000","use_path_style_endpoint":true}'
```

## Publish your bucket as website

There is many way to do so. Please check this a [`docker-compose.yaml`](docker-compose.yaml) to give a try.
