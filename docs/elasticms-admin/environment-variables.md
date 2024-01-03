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

### LOG_OUTPUT

Default `php://stdout` for local development you can change to `%kernel.logs_dir%/%kernel.environment%.log`

### Behind a Load Balancer or a Reverse Proxy

```dotenv
TRUSTED_PROXIES=127.0.0.1,127.0.0.2
TRUSTED_HOSTS=localhost,example.com
HTTP_CUSTOM_FORWARDED_PROTO=HTTP_X_COMPANY_FORWARDED_PROTO#Default value HTTP_X_FORWARDED_PROTO
HTTP_CUSTOM_FORWARDED_PORT=HTTP_X_COMPANY_FORWARDED_PORT#Default value HTTP_X_FORWARDED_PORT
HTTP_CUSTOM_FORWARDED_FOR=HTTP_X_COMPANY_FORWARDED_FOR#Default value HTTP_X_FORWARDED_FOR
HTTP_CUSTOM_FORWARDED_HOST=HTTP_X_COMPANY_FORWARDED_HOST#Default value HTTP_X_FORWARDED_HOST
```

If the reverse proxy's IP change all the time:

```dotenv
TRUSTED_PROXIES=127.0.0.1,REMOTE_ADDR
TRUSTED_HOSTS=localhost,example.com
HTTP_CUSTOM_FORWARDED_PROTO=HTTP_X_COMPANY_FORWARDED_PROTO#Default value HTTP_X_FORWARDED_PROTO
HTTP_CUSTOM_FORWARDED_PORT=HTTP_X_COMPANY_FORWARDED_PORT#Default value HTTP_X_FORWARDED_PORT
HTTP_CUSTOM_FORWARDED_FOR=HTTP_X_COMPANY_FORWARDED_FOR#Default value HTTP_CUSTOM_FORWARDED_FOR
HTTP_CUSTOM_FORWARDED_HOST=HTTP_X_COMPANY_FORWARDED_HOST#Default value HTTP_CUSTOM_FORWARDED_HOST
```

## Swift Mailer

### MAILER_URL
Configure [Swift Mailer](https://symfony.com/doc/current/email.html#configuration)

### EMSCH_SEARCH_LIMIT

Specify the maximum number of expected document for template, translation and route content types. Default value `1000`

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

## Elasticms Core Bundle variables
 
### EMSCO_TIKA_SERVER

Url of a Tika server (string).
 - Default value: empty string
 - Example `EMSCO_TIKA_SERVER=http://tika:9998`
 
### EMSCO_TIKA_DOWNLOAD_URL

Url or path to an Apache Tika jar file (string).
 - Default value: http://apache.belnet.be/tika/tika-app-1.22.jar
 - Example `EMSCO_TIKA_DOWNLOAD_URL=http://apache.belnet.be/tika/tika-app-1.22.jar'`
 
### Activate document signature

 - `EMSCO_PUBLIC_KEY` (mandatory): Path to a public key. The public key file needs to be in OpenSSH's format. It should look something like: `ssh-rsa AAAAB3NzaC1yc2EAAA....NX6sqSnHA8= rsa-key-20121110`
 - `EMSCO_PRIVATE_KEY` (mandatory): Path to a private key. 
 
### EMSCO_INSTANCE_ID

Used to prefix all elasticsearch indexes and aliases managed by the current instance of elasticms.  
 - Example `EMSCO_INSTANCE_ID=demo_v1_`
 
### EMSCO_NAME

HTML used as title in the interface
 - Default `<i class="ems-icon-ball"></i> elastic<b>ms</b>`
 - Example `EMSCO_NAME='<i class="ems-icon-ball"></i> My elasticms'`
 
### EMSCO_SHORTNAME

HTML used as short title in the interface
 - Default `<i class="ems-icon-ball"></i><span class="sr-only">elasticms</span>`
 - Example `EMSCO_SHORTNAME='My-ems'`
 
### EMSCO_SINGLE_TYPE_INDEX

If set to `true` an index will be created for each managed content types. You should avoid turning on this option as it will be deprecated soon. 
 - Default `false`
 - Example `EMSCO_SINGLE_TYPE_INDEX=false`
 
### EMSCO_PAGING_SIZE

Define the number of result per page in the search pages.
 - Default `20`
 
### EMSCO_THEME_COLOR

The color of the theme.
 * Default `blue`
 * Possible values:
   * `blue`
   * `indigo`
   * `purple`
   * `pink`
   * `red`
   * `orange`
   * `yellow`
   * `green`
   * `teal`
   * `cyan`
   * `white`
   * `gray`
   * `black`
   * `lightblue`
   * `navy`
   * `fuchsia`
   * `maroon`
   * `lime`
   * `olive`
 
### EMSCO_DATE_TIME_FORMAT

Format used to display dates in the interface. See [PHP datetime format](https://www.php.net/manual/en/datetime.format.php) for available options.
 - Default `j/m/Y \a\t G:i`
 
### EMSCO_DATEPICKER_FORMAT

Format used to display date in date pickers (See [Java Simple Date Format](https://docs.oracle.com/javase/7/docs/api/java/text/SimpleDateFormat.html).
 - Default `dd/mm/yyyy`
 
### EMSCO_DATEPICKER_WEEKSTART

First day of the week in date pickers. `0` for Sunday, `1` for Monday, ...  and `6` for Saterday.
 - Default `1`

 
### EMSCO_DATEPICKER_DAYSOFWEEK_HIGHLIGHTED

Highlights some days of the week in date pickers. `0` for Sunday, `1` for Monday, ...  and `6` for Saterday.
 - Default `[0, 6]`

 
### From information for email sent 
Default values:
```dotenv
EMSCO_FROM_EMAIL_ADDRESS='elasticms@example.com'
EMSCO_FROM_EMAIL_NAME='elasticms'
```
 
### EMSCO_ALLOW_USER_REGISTRATION
If set to `true` user can perform a self registration
 - Default `false`
 
### EMSCO_ASSET_CONFIG
Used to defined multiple asset processor config.
 - Default `[]`
 
Example:
```dotenv
EMSCO_ASSET_CONFIG='{"preview":{"_config_type":"image","_width":300,"_height":200,"_resize":"fill","_radius":0,"_radius_geometry":["topleft","bottomright"]}}'
```

### EMSCO_PRE_GENERATED_OUUIDS

If set to `true` new documents will have a OUUID generated before calling post-processors at first document's finalization.
If set to `false` new documents will have a OUUID generated by elasticsearch during first indexation. So OUUIDs are not available for post-processors at first document's finalization.
  - Default `false`
 
### EMSCO_CIRCLES_OBJECT

Name of the content type used a circle.
 - Default empty string (not defined)
 - Example `EMSCO_CIRCLES_OBJECT=circle`

### EMSCO_TRIGGER_JOB_FROM_WEB

If set to `false` job initiated from the interface are executed on the spot. Use the `ems:job:run` command to run pending jobs. It's recommended to schedule an `ems:job:run` command and turn off this option.
 - Default value `true`
 - Example `EMSCO_TRIGGER_JOB_FROM_WEB=false`

### EMSCO_LOG_LEVEL
Define the [level of logs](https://github.com/Seldaek/monolog/blob/main/doc/01-usage.md#log-levels) that will be saved in the elasticsearch ems_internal_logger_alias daily index. Default value: `INFO`.

```dotenv
EMSCO_LOG_LEVEL=NOTICE
``` 
 
### EMSCO_LOG_BY_PASS
Define if the elastica logger must be turned off. Possible values are:
 - `true`: the elastica logger is turn off
 - `false`: the elastic logger is active

```dotenv
EMSCO_LOG_BY_PASS=false
``` 
 
### EMSCO_DEFAULT_BULK_SIZE
Define the default bulk size for commands such as the `ems:environment:rebuild` command. Default value: `500`.

```dotenv
EMSCO_DEFAULT_BULK_SIZE=500
``` 
  
### EMS_BACKEND_URL
Define the url use by the user to access elasticms (in order to generate links in emails).

```dotenv
EMS_BACKEND_URL='http://admin.elasticms.local'
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

### EMSCH_LOCAL_PATH

Overwrite the destination of the local files, by default `emsch:local:*` commands will search in `local/%environment_alias%` folder.

Example for locally loading the demo inside local elasticms-admin.
```.dotenv
EMSCH_LOCAL_PATH='../demo/skeleton'
```

## Elasticms Common Bundle variables

### EMS_LOG_LEVEL

Define the log level (integer) where logs will be saved in DB:
- Default value: EMS_LOG_LEVEL='250'

Possible values:
- DEBUG (100): detailed debug information
- INFO (200) : interesting events. Examples: User logs in, SQL logs
- NOTICE (250): normal but significant events
- WARNING (300): exceptional occurrences that are not errors. Examples: Use of deprecated APIs, poor use of an API, undesirable things that are not necessarily wrong
- ERROR (400): runtime errors that do not require immediate action but should typically be logged and monitored
- CRITICAL (500): critical conditions. Example: Application component unavailable, unexpected exception
- ALERT (550): action must be taken immediately. Example: Entire website down, database unavailable, etc. This should trigger phone call or SMS alerts and wake you up
- EMERGENCY (600): emergency: system is unusable

### EMS_ELASTICSEARCH_CONNECTION_POOL

Define the [elasticsearch sniffing strategy](https://www.elastic.co/guide/en/elasticsearch/client/php-api/7.17/connection_pool.html:
- Default value: EMS_ELASTICSEARCH_CONNECTION_POOL='Elasticsearch\\ConnectionPool\\SimpleConnectionPool' if the EMS_ELASTICSEARCH_HOSTS contains one and only one host configuration; in order to avoid sniffing requests on a cluster that is more likely behind a reverse proxy. Else it contains EMS_ELASTICSEARCH_CONNECTION_POOL='Elasticsearch\\ConnectionPool\\SniffingConnectionPool'. 
- Possible values:
    - EMS_ELASTICSEARCH_CONNECTION_POOL='Elasticsearch\\ConnectionPool\\SimpleConnectionPool'
    - EMS_ELASTICSEARCH_CONNECTION_POOL='Elasticsearch\\ConnectionPool\\SniffingConnectionPool'
    - EMS_ELASTICSEARCH_CONNECTION_POOL='Elasticsearch\\ConnectionPool\\StaticConnectionPool'
    - EMS_ELASTICSEARCH_CONNECTION_POOL='Elasticsearch\\ConnectionPool\\StaticNoPingConnectionPool'

### EMS_ELASTICSEARCH_HOSTS

Define the elasticsearch cluster as an array (JSON encoded) of hosts:
- Default value: EMS_ELASTICSEARCH_HOSTS='["http://localhost:9200"]'

If needed, this variable can also contain an [elastica servers array](https://elastica-docs.readthedocs.io/en/latest/client.html#client-configurations):

```dotenv
EMS_ELASTICSEARCH_HOSTS='[{"transport":"Https","host":"elastic:fewl13@localhost","port":9200,"curl":{"64":false}}]'
```
In this example the cluster contains only one host accessible via HTTPS on the port 9200. But with the CURL option `"64": false` the client doesn't check the validity of the host certificate

```dotenv
EMS_ELASTICSEARCH_HOSTS='[{"transport":"Https","host":"elastic:fewl13@localhost","port":9200,"curl":{"10065":"/opt/local/cacert.pem"}}]'
```
Here the client uses the `/opt/local/cacert.pem` to validate the server certificate.


```dotenv
EMS_ELASTICSEARCH_HOSTS='[{"transport":"Https","host":"localhost","port":9200,"headers":{"Authorization":"Basic ZWxhc3RpYzpmZXdsMTM="},"curl":{"64":false}}]'
```
Another example with an extra HTTP header.

[All PHP CURL integer identifier can be found on GitHub](https://github.com/JetBrains/phpstorm-stubs/blob/master/curl/curl_d.php). More info on [PHP.net](https://www.php.net/manual/en/function.curl-setopt.php).

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

### EMS_STORE_DATA_SERVICES

Define (JSON format) the store data services, in the priority order. See the [Stora Data documentation](../recipes/store-data.md) for more details. Default value `[{"type":"db"}]`

### EMS_EXCLUDED_CONTENT_TYPES

Define (JSON format) a list of content type names to exclude from admin backup/restore commands. Example: `["route","template","template_ems","label"]`. Default value `[]`

## Elasticms Form Bundle variables

### EMSF_HASHCASH_DIFFICULTY
Define the [hashcash difficuty](https://github.com/ems-project/EMSFormBundle/blob/master/doc/config.md#hashcash-difficulty) for the form bundle. Set to `16384` by default.


### EMSF_ENDPOINTS
Define the [endpoints](https://github.com/ems-project/EMSFormBundle/blob/master/doc/config.md#endpoints) for the form bundle. Set to `[]` by default.


### EMSF_LOAD_FROMJSON
Define the [load form JSON](https://github.com/ems-project/EMSFormBundle/blob/master/doc/config.md#load-from-json) for the form bundle. Set to `true` by default.


### EMSF_CACHEABLE
Define the [cacheable](https://github.com/ems-project/EMSFormBundle/blob/master/doc/config.md#cacheable) for the form bundle. Set to `true` by default.

### EMSF_TYPE
Define the [type](https://github.com/ems-project/EMSFormBundle/blob/master/doc/config.md#type) for the form bundle. Set to `form_instance` by default.

## Elasticms Submission Bundle variables

### EMSS_CONNECTIONS
Define the [connections](https://github.com/ems-project/EMSSubmissionBundle/blob/master/src/Resources/doc/index.md#connections-) for the submission bundle. Set to `[]` by default.

### EMSS_DEFAULT_TIMEOUT
Define the [default timeout](https://github.com/ems-project/EMSSubmissionBundle/blob/master/src/Resources/doc/index.md#default-timeout) for the submission bundle. Set to `10` by default.


### EMSCO_CUSTOM_USER_OPTIONS_FORM
If sets and refers to a Form entity, the corresponding form will be included in the User profile form (/user/edit/1).
It's usefully in order to add extra parameters or attributes to a specific instance of elasticms. I.e. adding users working hours.


## Deprecated variables

## Since version 1.14.3
 - ELASTICSEARCH_CLUSTER : See [EMS_ELASTICSEARCH_HOSTS](#ems_elasticsearch_hosts)
 - ELASTICSEARCH_VERSION : This variable doesn't have any replacement, the value is directly get form the elasticsearch cluster itself.
 - S3_BUCKET : See [EMS_STORAGES](#EMS_STORAGES)
 - S3_CREDENTIALS : See [EMS_STORAGES](#EMS_STORAGES)
 - STORAGE_FOLDER : See [EMS_STORAGES](#EMS_STORAGES)
 - EMS_SFTP_SERVEUR : See [EMS_STORAGES](#EMS_STORAGES)
 - EMS_SFTP_PATH : See [EMS_STORAGES](#EMS_STORAGES)
 - EMS_SFTP_USER : See [EMS_STORAGES](#EMS_STORAGES)
 - EMS_EMS_REMOTE_SERVER : See [EMS_STORAGES](#EMS_STORAGES)
 - EMS_EMS_REMOTE_AUTHKEY : See [EMS_STORAGES](#EMS_STORAGES)
 - EMS_SAVE_ASSETS_IN_DB : See [EMS_STORAGES](#EMS_STORAGES)
 - TIKA_SERVER : See [EMSCO_TIKA_SERVER](#EMSCO_TIKA_SERVER)
 - TIKA_DOWNLOAD_URL : See [EMSCO_TIKA_DOWNLOAD_URL](#EMSCO_TIKA_DOWNLOAD_URL)
 - EMS_PRIVATE_KEY : See [EMSCO_PRIVATE_KEY](#Activate document signature)
 - EMS_PUBLIC_KEY : See [EMSCO_PUBLIC_KEY](#Activate document signature)
 - EMS_INSTANCE_ID : See [EMSCO_INSTANCE_ID](#EMSCO_INSTANCE_ID)
 - EMS_NAME : See [EMSCO_NAME](#EMSCO_NAME)
 - EMS_SHORTNAME : See [EMSCO_SHORTNAME](#EMSCO_SHORTNAME)
 - EMS_SINGLE_TYPE_INDEX : Not replaced
 - EMSCO_SINGLE_TYPE_INDEX : Not replaced
 - EMS_PAGING_SIZE : See [EMSCO_PAGING_SIZE](#EMSCO_PAGING_SIZE)
 - EMS_THEME_COLOR : See [EMSCO_THEME_COLOR](#EMSCO_THEME_COLOR)
 - EMS_DATE_TIME_FORMAT : See [EMSCO_DATE_TIME_FORMAT](#EMSCO_DATE_TIME_FORMAT)
 - EMS_DATEPICKER_FORMAT : See [EMSCO_DATEPICKER_FORMAT](#EMSCO_DATEPICKER_FORMAT)
 - EMS_DATEPICKER_WEEKSTART : See [EMSCO_DATEPICKER_WEEKSTART](#EMSCO_DATEPICKER_WEEKSTART)
 - EMS_DATEPICKER_DAYSOFWEEK_HIGHLIGHTED : See [EMSCO_DATEPICKER_DAYSOFWEEK_HIGHLIGHTED](#EMSCO_DATEPICKER_DAYSOFWEEK_HIGHLIGHTED)
 - EMS_FROM_EMAIL_ADDRESS : See [EMSCO_FROM_EMAIL_ADDRESS](#From information for email sent)
 - EMS_FROM_EMAIL_NAME : See [EMSCO_FROM_EMAIL_NAME](#From information for email sent)
 - EMS_ALLOW_USER_REGISTRATION : See [EMSCO_ALLOW_USER_REGISTRATION](#EMSCO_ALLOW_USER_REGISTRATION)
 - EMS_ASSET_CONFIG : See [EMSCO_ASSET_CONFIG](#EMSCO_ASSET_CONFIG)
 - EMS_CIRCLES_OBJECT : See [EMSCO_CIRCLES_OBJECT](#EMSCO_ALLOW_USER_REGISTRATION)
 - EMS_UPLOAD_FOLDER : Not replaced
 - DATABASE_URL : See [Doctrine](#Doctrine variables))
 - EMSCO_ASSET_CONFIG_TYPE : Not replaced
 - EMSCO_ASSET_CONFIG_INDEX : Not replaced

