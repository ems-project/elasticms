# Available environment variables

The environment variables have been grouped by bundles and for the Symfony framework itself.

## Symfony variables

### APP_ENV

[Possible values](https://symfony.com/doc/current/configuration.html#selecting-the-active-environment): `dev`, `prod`, `test`
- Example `APP_ENV=dev`

### APP_SECRET

A secret seed.
- Example `APP_SECRET=7b19a4a6e37b9303e4f6bca1dc6691ed`

## Doctrine variables

### DATABASE_URL

Format described at https://www.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/configuration.html#connecting-using-a-url

IMPORTANT: You MUST configure your server version, either here or in config/packages/doctrine.yaml

Examples: 
- `DATABASE_URL="sqlite:///%kernel.project_dir%/var/data.db"`
- `DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/db_name?serverVersion=5.7&charset=utf8mb4"`
- `DATABASE_URL="postgresql://symfony:ChangeMe@127.0.0.1:5432/app?serverVersion=13&charset=utf8"`


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

Redis host for the common cache service. Default `localhost`.

### EMS_REDIS_PORT

Redis port for the common cache service. Default `6379`.
