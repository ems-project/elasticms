# Available environment variables

The environment variables have been grouped by bundles and for the Symfony framework itself.

## Symfony variables

### APP_ENV

[Possible values](https://symfony.com/doc/current/configuration.html#selecting-the-active-environment): `dev`, `prod`, `redis`, `dev`, `test`
 - Example `APP_ENV=dev`
 
But there is 2 more possible values, specific to elasticms:

 - `db` : It's equivalent to a `prod` environment, but PHP sessions are persisted in the RDBMS (does not work with SQLite databases).
 - `redis` : It's equivalent to a `prod` environment, but PHP sessions are saved in a Redis server.

### APP_SECRET

A secret seed.
 - Example `APP_SECRET=7b19a4a6e37b9303e4f6bca1dc6691ed`

### Behind a Load Balancer or a Reverse Proxy

```dotenv
TRUSTED_PROXIES=127.0.0.1,127.0.0.2
TRUSTED_HOSTS=localhost,example.com
HTTP_CUSTOM_FORWARDED_PROTO=HTTP_X_COMPANY_FORWARDED_PROTO #Default value HTTP_X_FORWARDED_PROTO
HTTP_CUSTOM_FORWARDED_PORT=HTTP_X_COMPANY_FORWARDED_PORT #Default value HTTP_X_FORWARDED_PORT
HTTP_CUSTOM_FORWARDED_FOR=HTTP_X_COMPANY_FORWARDED_FOR #Default value HTTP_X_FORWARDED_FOR
HTTP_CUSTOM_FORWARDED_HOST=HTTP_X_COMPANY_FORWARDED_HOST #Default value HTTP_X_FORWARDED_HOST
```

If the reverse proxy's IP change all the time:

```dotenv
TRUSTED_PROXIES=127.0.0.1,REMOTE_ADDR
TRUSTED_HOSTS=localhost,example.com
HTTP_CUSTOM_FORWARDED_PROTO=HTTP_X_COMPANY_FORWARDED_PROTO #Default value HTTP_X_FORWARDED_PROTO
HTTP_CUSTOM_FORWARDED_PORT=HTTP_X_COMPANY_FORWARDED_PORT #Default value HTTP_X_FORWARDED_PORT
HTTP_CUSTOM_FORWARDED_FOR=HTTP_X_COMPANY_FORWARDED_FOR #Default value HTTP_CUSTOM_FORWARDED_FOR
HTTP_CUSTOM_FORWARDED_HOST=HTTP_X_COMPANY_FORWARDED_HOST #Default value HTTP_CUSTOM_FORWARDED_HOST
```

## Swift Mailer

### MAILER_URL
Configure [Swift Mailer](https://symfony.com/doc/current/email.html#configuration)


## Doctrine variables

Default values (sqlite): 
```dotenv
DB_DRIVER='sqlite'
DB_USER='user'
DB_PASSWORD='user'
DB_PORT='1234'
DB_NAME='app.db'
```

### DB_HOST

DB's host. 
 - Default value: `127.0.0.1`
 - Example: `DB_DRIVER='db-server.tl'`
 
### DB_DRIVER

Driver (Type of the DB server). Accepted values are `mysql`, `pgsql` and `sqlite`
 - Default value: `mysql`
 - Example: `DB_DRIVER='pgsql'`
  
### DB_USER

 - Default value `user`
 - Example: `DB_USER='demo'`
  
### DB_PASSWORD

 - Default value `user`
 - Example: `DB_PASSWORD='password'`
  
### DB_PORT

For information the default mysql/mariadb port is 3306 and 5432 for Postgres
 - Default value `3306`
 - Example: `DB_PORT='5432'`
  
### DB_NAME

 - Default value `elasticms`
 - Example: `DB_NAME='demo'`
  
### DB_SCHEMA

This variable is not used by Doctrine but by the dump script with postgres in the docker image of elasticms. 
 - Default value: not defined
 - Example: `DB_SCEMA='schema_demo_adm'`
 
### DB_CONNECTION_TIMEOUT

Usefull when connecting to a string of multiple hosts. To reduce timeout when checking a second host if the first host fails.
The minimum value is 2 https://pracucci.com/php-pdo-pgsql-connection-timeout.html
 - Default value `30`
 - Example: `DB_CONNECTION_TIMEOUT=30`


## Redis
Should be defined only if Redis is defined as session manager.
```dotenv
REDIS_HOST=localhost
REDIS_PORT=6379
```

## Elasticms Client Helper Bundle variables

### EMSCH_LOCALES

List of available locales supported by the client/channels i.e.: `EMSCH_LOCALES=["en","fr","nl"]`

### EMSCH_INSTANCE_ID

Define the list of project's index prefixes, separated by a `|` i.e. `='demo_pgsql_v1_'`, By default it sets to the EMSCO_INSTANCE_ID value.

### EMSCH_TRANSLATION_TYPE

Define the translation content type name. Default value `label` i.e. `EMSCH_TRANSLATION_TYPE='label'`

### EMSCH_ROUTE_TYPE

Define the route content type name. Default value `route` i.e. `EMSCH_ROUTE_TYPE='route'`

### EMSCH_TEMPLATES

Define the template content type structure. Default value `{"template": {"name": "name","code": "body"}}` i.e. `EMSCH_TEMPLATES='{"template": {"name": "label","code": "body"}}'`

### EMSCH_ASSET_LOCAL_FOLDER

Specify a local folder (in the public folder) where to locate `emsch` assets. This is useful in development mode as the zip containing the assets is ignored.
Example base template.
```twig
<link rel="stylesheet" href="{{ asset('css/app.css', 'emsch') }}">
```

### EMSCH_SEARCH_LIMIT

Specify the maximum number of expected document for template, translation and route content types. Default value `1000`

## Elasticms Common Bundle variables

### EMS_ELASTICSEARCH_HOSTS

Define the elasticsearch cluster as an array (JSON encoded) of hosts:
- Default value: EMS_ELASTICSEARCH_HOSTS='["http://localhost:9200"]'

### EMS_STORAGES

Used to define storage services. Elasticms supports [multiple types of storage services](https://github.com/ems-project/EMSCommonBundle/blob/master/src/Resources/doc/storages.md). 
- Default value: `EMS_STORAGES='[{"type":"fs","path":".\/var\/assets"},{"type":"s3","credentials":[],"bucket":""},{"type":"db","activate":false},{"type":"http","base-url":"","auth-key":""},{"type":"sftp","host":"","path":"","username":"","public-key-file":"","private-key-file":""}]'`
- Example: `EMS_STORAGES='[{"type":"fs","path":"./var/assets"},{"type":"fs","path":"/var/lib/elasticms"}]'`

### EMS_HASH_ALGO

Refers to the [PHP hash_algos](https://www.php.net/manual/fr/function.hash-algos.php) function. Specify the algorithms to used in order to hash and identify files. It's also used to hash the document indexed in elasticsearch.
- Default value: EMS_HASH_ALGO='sha1'

### EMS_BACKEND_URL

Define backend elasticms url. CommonBundle provides a CoreApi instance.

### EMS_BACKEND_API_KEY

Define backend authentication token. The commonBundle coreApi instance becomes authenticated.

### EMS_CACHE

Define the ems cache type. Default value `file_system`. 
Allowed values: `file_system`, `apc` and `redis`. 

### EMS_CACHE_PREFIX

Unique required value per project, otherwise wipe storage will clear all cached values. 

### EMS_REDIS_HOST

Use a different redis host for the common cache service. Default REDIS_HOST env variable.

### EMS_REDIS_PORT

Use a different redis port for the common cache service. Default REDIS_PORT env variable.

### EMS_METRIC_ENABLED

Default value `false`, if true `/metrics` is added to the routes.

### EMS_METRIC_HOST

Default value empty, symfony route host pattern for allow hosting on /metrics

### EMS_METRIC_PORT

Default value null, if defined will check the SERVER_PORT and throw 404 if not matching

### EMS_WEBALIZE_REMOVABLE_REGEX

Can fine tune the ems_weblize twig filter by adjusting the regex used to remove some characters. Default value `/([^a-zA-Z0-9\_\|\ \-\.])|(\.$)/`

### EMS_WEBALIZE_DASHABLE_REGEX

Can fine tune the ems_weblize twig filter by adjusting the regex used to replace some characters by a dash `-`. Default value `/([^a-zA-Z0-9\_\|\ \-\.])|(\.$)/`

## Elasticms Form Bundle variables

### EMSF_HASHCASH_DIFFICULTY
Define the [hashcash difficuty](https://github.com/ems-project/EMSFormBundle/blob/master/doc/config.md#hashcash-difficulty) for the form bundle. Set to `16384` by default.


### EMSF_ENDPOINTS
Define the [endpoints](https://github.com/ems-project/EMSFormBundle/blob/master/doc/config.md#endpoints) for the form bundle. Set to `[]` by default.


### EMSF_LOAD_FROMJSON
Define the [load form JSON](https://github.com/ems-project/EMSFormBundle/blob/master/doc/config.md#load-from-json) for the form bundle. Set to `false` by default.


### EMSF_CACHEABLE
Define the [cacheable](https://github.com/ems-project/EMSFormBundle/blob/master/doc/config.md#cacheable) for the form bundle. Set to `true` by default.

### EMSF_TYPE
Define the [type](https://github.com/ems-project/EMSFormBundle/blob/master/doc/config.md#type) for the form bundle. Set to `form_instance` by default.

## Elasticms Submission Bundle variables

### EMSS_CONNECTIONS
Define the [connections](https://github.com/ems-project/EMSSubmissionBundle/blob/master/src/Resources/doc/index.md#connections-) for the submission bundle. Set to `[]` by default.

### EMSS_DEFAULT_TIMEOUT
Define the [default timeout](https://github.com/ems-project/EMSSubmissionBundle/blob/master/src/Resources/doc/index.md#default-timeout) for the submission bundle. Set to `10` by default.
