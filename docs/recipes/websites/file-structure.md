# File structure

## Push a local folder to ElasticMS or Storage

The following commands is available in all ElacticMS applications (Admin, Web and CLI).


```shell
php bin/console ems:file-structure:push ../../demo --admin
```
This command will upload all files present in the ../../demo folder an give you an hash as output.
That hash identify an ElasticMS Archive, it's a JSON containing the all files structure (with hash, filename, size and mimetype of all files).

The admin option requires api authentication, first run `ems:admin:login`.
Without the admin option, it will use the application storages defined in `EMS_STORAGES`.

## Update a local folder from ElasticMS

```shell
php bin/console ems:file-structure:pull d3bb0298fd9a69743333fb25dbe6cdefdc834ff2 ../../demo --admin
```
Update the folder ../../demo by the content of the ElasticMS archive identified by the hash `d3bb0298fd9a69743333fb25dbe6cdefdc834ff2`.

The admin option requires api authentication, first run `ems:admin:login`.
Without the admin option, it will use the application storages defined in `EMS_STORAGES`.

## Publish a file structure to a S3 bucket

There is another command that allows you to publish an ElasticMS archive into a S3 bucket.

```shell
php bin/console ems:file-structure:publish d3bb0298fd9a69743333fb25dbe6cdefdc834ff2 website --term-field=identifier --s3-credential='{"version":"2006-03-01","credentials":{"key":"accesskey","secret":"secretkey"},"region":"us-east-1","endpoint":"http://localhost:9000","use_path_style_endpoint":true}'
```

## Publish your bucket as website

There is many way to do so. Please check this a [`docker-compose.yaml`](docker-compose.yaml) to give a try.
