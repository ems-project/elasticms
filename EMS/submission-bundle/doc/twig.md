# Twig filters
## emss_connection

Fetch ems_submission.connection key/value information (usefull to hide credentials from DB/Elasticsearch):
The string passed to the filter should be in the format `'connection_name%.%key_name'` The separator `%.%` is chosen to allow for `.` in passwords (and user names). A user or password cannot contain the combination of charachters we use as separator!

### Examples
```twig
'service-now-instance-a%.%user'|emss_connection {# will be replaced with the user of the connection "service-now-instance-a" #}
'service-now-instance-a%.%password'|emss_connection {# will be replaced with the password of the connection "service-now-instance-a" #}

'service-now-instance-b%.%user'|emss_connection {# will be replaced with the username of the connection "service-now-instance-b" #}
'service-now-instance-b%.%password'|emss_connection {# will be replaced with the password of the connection "service-now-instance-b" #}
```
