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

Redis host for the common cache service. Default `localhost`.

### EMS_REDIS_PORT

Redis port for the common cache service. Default `6379`.


### EMS_STORE_DATA_SERVICES

Define (JSON format) the store data services, in the priority order. See the [Stora Data documentation](../recipes/store-data.md) for more details. By default, the store data functionalities are disabled.

### EMS_EXCLUDED_CONTENT_TYPES

Define (JSON format) a list of content type names to exclude from admin backup/restore commands. Example: `["route","template","template_ems","label"]`. Default value `[]`

## CLI variables

### EMSCLI_TIKA_PATH

Path to the Tika JAR. Default `/opt/bin/tika.jar`. If your are using a elasticMS CLI, a Tika jar is included. From version 5.6.
