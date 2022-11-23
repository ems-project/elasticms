# The EMS SubmissionBundle

## Handlers
In the backend a content type is defined to configure your submit procedure. 
The handler has access to the complete form data, and to the response of the previously defined submit handler. 
When multiple submit handlers are attached to a form instance, they are called in order of definition, and the result 
of the previous submission handler is passed to the next one.

Each handler need to have a **endpoint** field and **message** field. 
The endpoint typically contains connection information, while the message contains information 
that is derived from the submitted data.

Both fields are rendered by twig and the following information is available in twig

- **config** (EMS\FormBundle\FormConfig\FormConfig)    
- **data** (array of the submitted data)
- **formData** (EMS\FormBundle\Submission\FormData)
- **request** (EMS\FormBundle\Submission\HandleRequestInterface)
- **responses** (EMS\FormBundle\Submission\HandleResponseInterface[])

```twig
{# examples #}

{% set formName = config.name %}
{% set formLocale = config.locale %}
{% set formTranslationDomain = config.translationDomain %}
{% set fullName = data.firstName ~ ' ' ~ data.lastName  %}
{% set previousResponse = request.responses.0.response %}

{# all files defined on the form #}
{% set files = formData.allFiles|map(file => file.toArray) %}

{# all files defined on the form and there base64 encoded content #}
{% set files = formData.allFiles|map(v => v.toArray|merge({ 'base64': v.base64() }) ) %}
```

### Supported handlers

* [Email](handlers/email.md)
* [Http](handlers/http.md)
* [Pdf](handlers/pdf.md)
* [ServiceNow](handlers/serviceNow.md)
* [Sftp](handlers/sftp.md)
* [Soap](handlers/soap.md)
* [Zip](handlers/zip.md)

## Configuration
```yaml
#config/packages/ems_submission.yaml
ems_submission:
  default_timeout: '%env(int:EMSS_DEFAULT_TIMEOUT)%'
  connections: '%env(json:EMSS_CONNECTIONS)%'
```

### Default Timeout
Whenever a form is submitted using our handlers, we should limit the amount of time that is allowed for the request to succeed. The `default_timeout` requires a number that represents the allowed number of seconds before we timeout waiting for external feedback.

### Connections
To integrate with external services like ServiceNow we need credentials. Those are passed using the configuration of the bundle to prevent disclosure of the password in the ElasticMS backend and Elasticsearch cluster.
The 'connections' parameter allows to add one or more connection configurations as follows:
```yaml 
ems_submission:
  connections: '[{"connection": "service-now-instance-a", "user": "instance-a-username", "password": "instance-a-password"}, {"connection": "service-now-instance-b", "user": "instance-b-username", "password": "instance-b-password"}]'
```

Each configuration has a "connection", "user", and "password" entry.
* "connection" is used to identify the user/password combination from within a submission template
* "user" is the username needed to connect to the service (the name of this key is free of choice)
* "password" is the password needed to connect to the service (the name of this key is free of choice)
An infinite amount of keys can be added to this configuration, only the "connection" key is obligatory.

### Fetch credentials for your service.
An example endpoint configuration to integrate with ServiceNow has access to the user/pass of the "service-now-instance-a" using the [emss_connection](/doc/twig.md) filter:
```twig
{
    "host": "https://example.service-now.com/api/now/table/my_table_name",
    "username": "{{'service-now-instance-a%.%user'|emss_connection}}",
    "password": "{{'service-now-instance-a%.%password'|emss_connection}}"
}
```
