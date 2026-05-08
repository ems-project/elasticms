# Available environment variables

The environment variables have been grouped by bundles and for the Symfony framework itself.

## Symfony variables

### APP_ENV

[Possible values](https://symfony.com/doc/current/configuration.html#selecting-the-active-environment):
`dev`, `prod`, `test`

- Example `APP_ENV=dev`

### APP_SECRET

A secret seed.

- Example `APP_SECRET=7b19a4a6e37b9303e4f6bca1dc6691ed`

### HTTP_CLIENT_MAX_CONNECTIONS

Define the max connections to the API client, when using async calls. By default it is set to `4`.

## Doctrine variables

Default values (sqlite):

```dotenv
DB_DRIVER='pgsql'
DB_USER='user'
DB_PASSWORD='pass'
DB_PORT='5432'
DB_NAME='elasticms'
```

### DB_HOST

DB's host.

- Default value: `127.0.0.1`
- Example: `DB_DRIVER='db-server.tl'`

### DB_DRIVER

Driver (Type of the DB server). Accepted values are `mysql`, `pgsql` and `sqlite`

- Default value: `pgsql`
- Example: `DB_DRIVER='pgsql'`

### DB_USER

- Default value `user`
- Example: `DB_USER='demo'`

### DB_PASSWORD

- Default value `pass`
- Example: `DB_PASSWORD='password'`

### DB_PORT

For information the default mysql/mariadb port is 3306 and 5432 for Postgres

- Default value `5432`
- Example: `DB_PORT='5432'`

### DB_NAME

- Default value `elasticms`
- Example: `DB_NAME='demo'`

### DB_SCHEMA

This variable is not used by Doctrine but by the dump script with postgres in the docker image of
elasticms.

- Default value: not defined
- Example: `DB_SCEMA='schema_demo_adm'`

## Elasticms Common Bundle variables

### EMS_ELASTICSEARCH_HOSTS

Define the elasticsearch cluster as an array (JSON encoded) of hosts:

- Default value: EMS_ELASTICSEARCH_HOSTS='["http://localhost:9200"]'

If needed, this variable can also contain an Elastica config array:

```dotenv
EMS_ELASTICSEARCH_HOSTS='{"hosts":["http://localhost:9201"],"username":"elastic","password":"changeme","transport_config":{"http_client_options":{"verify_peer":false}}}'
```

### EMS_STORAGES

Used to define storage services. Elasticms supports
[multiple types of storage services](https://github.com/ems-project/EMSCommonBundle/blob/master/src/Resources/doc/storages.md).

- Default value:
  `EMS_STORAGES='[{"type":"fs","path":".\/var\/assets"},{"type":"s3","credentials":[],"bucket":""},{"type":"db","activate":false},{"type":"http","base-url":"","auth-key":""},{"type":"sftp","host":"","path":"","username":"","public-key-file":"","private-key-file":""}]'`
- Example:
  `EMS_STORAGES='[{"type":"fs","path":"./var/assets"},{"type":"fs","path":"/var/lib/elasticms"}]'`

### EMS_RUNNERS

Used to define ruuner services. See [runners](../dev/common-bundle/runners.md) for more details.

- Default value: `EMS_RUNNERS='[]'`
- Example:
  `EMS_RUNNERS='[{"type":"openshift","tag":"toto","base-url":"https://api.my-paas.tld:6443/","auth-key":"sha256~my-priVAteAuthorization_kEy","namespace":"my-namesapce","image":"busybox"}]'`

### EMS_HASH_ALGO

Refers to the [PHP hash_algos](https://www.php.net/manual/fr/function.hash-algos.php) function.
Specify the algorithms to used in order to hash and identify files. It's also used to hash the
document indexed in elasticsearch.

- Default value: EMS_HASH_ALGO='sha1'

### EMS_BACKEND_URL

Define backend elasticms url. CommonBundle provides a CoreApi instance.

### EMS_BACKEND_API_KEY

Define backend authentication token. The commonBundle coreApi instance becomes authenticated.

### EMS_CORE_API_HEADERS

Define extra headers for the API client, helpful for adding cookie header for xdebug.

```dotenv
EMS_CORE_API_HEADERS='{"Cookie":"XDEBUG_SESSION=PHPSTORM"}'
```

### EMS_BACKEND_API_TIMEOUT

Adjust the API client's timeout. By default is set to `30` seconds, if you API request may take
longueur (e.g. during migration) you can increase the timeout :

### EMS_BACKEND_API_VERIFY

Define the `verify_host` and `verify_peer` for the api client, by default it is set to `true`.

### EMS_CACHE

Define the ems cache type. Default value `file_system`. Allowed values: `file_system`, `apc` and
`redis`.

### EMS_CACHE_PREFIX

Unique required value per project, otherwise wipe storage will clear all cached values.

### EMS_REDIS_HOST

Redis host for the common cache service. Default `localhost`.

### EMS_REDIS_PORT

Redis port for the common cache service. Default `6379`.

### EMS_STORE_DATA_SERVICES

Define (JSON format) the store data services, in the priority order. See the
[Stora Data documentation](../recipes/store-data.md) for more details. By default, the store data
functionalities are disabled.

### EMS_KEY_STORE

A JSON-formatted environment variable that stores API tokens or other credentials as key/value
pairs. Used to centralize and securely manage secrets (e.g. API keys) needed across the application.
Each key can be accessed by name via the `KeyStore` service.

### EMS_EXCLUDED_CONTENT_TYPES

Define (JSON format) a list of content type names to exclude from admin backup/restore commands.
Example: `["route","template","template_ems","label"]`. Default value `[]`

### EMS_SLUG_SYMBOL_MAP

Specify replacement strings, per locale to symbols. E.g. if you want to replace the symbol `@` by
the string `at` in your slug in English and French : `{"en":{"@":"at"},"fr":{"@":"at"}}`. Default
value `~`
([rely on the default Symfony configuration](https://github.com/symfony/string/blob/f5832521b998b0bec40bee688ad5de98d4cf111b/Slugger/AsciiSlugger.php#L59C42-L61C6))

### EMS_ELASTICSEARCH_PROXY_API

Bollean variable, if specified to `true` all elasticsearch query will be delegated to the admin api.
And then you'll need to login on an admin first via the `ems:admin:login` command. By default, this
variable is set to `false`.

## CLI variables

### EMSCLI_TIKA_PATH

Path to the Tika JAR. Default `/opt/bin/tika.jar`. If your are using a elasticMS CLI, a Tika jar is
included. From version 5.6.
