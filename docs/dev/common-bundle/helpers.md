# Helpers

### Log

The Common bundle provides a [Log](https://github.com/ems-project/elasticms/tree/4.x/EMS/common-bundle/src/Entity/Log.php) entity for saving logs in the database.

You can enable the log handler with the following code:
```yaml
monolog:
    handlers:
        doctrine:
            type: service
            id: ems_common.monolog.doctrine
            channels: [app,core,audit]
```

The log handler will only store log records with a level higher then the env variable **EMS_LOG_LEVEL**.
Possible levels: 

* 100 (DEBUG)
* 200 (INFO)
* 250 (NOTICE)
* 300 (WARNING)
* 400 (ERROR)
* 500 (CRITICAL)
* 550 (ALERT)
* 600 (EMERGENCY)

