# Runners

Using the environment variable `EMS_RUNNERS`, you can define multiple runners of different kinds.  
A runner is used to execute a command asynchronously in another process, platform, environment, or stack.  
See below for the list of available types of runners.

The `EMS_RUNNERS` environment variable is a JSON array of configurations.  
Each configuration's parameters will depend on the type of runner you want to use, but they will all share the following parameters:

- `type`: identifies the kind of runner (e.g., `openshift` or `docker-remote`) (required)
- `tag`: a name you provide to identify a specific runner configuration (required)
- `worker-command`: use to specify worker command if a job with the same tag then the runner is created. Use the following replace token `%ems_job_id%` to specify the Job ID to run by the runner. E.g. `ems:admin:next-job tag %ems_job_id%` or `emsco:job:run %ems_job_id%` (optional)

This means you can define multiple runner configurations of the same type.

## OpenShift

This type of runner allows launching a runner via an OpenShift Job.  
To do this, you must specify the following configuration parameters:

- `type`: Must be set to `openshift` (required)
- `tag`: A name to identify this specific OpenShift runner configuration (required)
- `base-url`: The base URL of your OpenShift PaaS (required)
- `verify-ssl`: Boolean or string that describes the SSL certificate verification behavior of the requests (optional)
  - `true`: Use the system's CA bundle (this is the default setting)
  - `"/path/to/cert.pem"`: Use a custom SSL certificate on disk
  - `false`: Disable validation entirely (don't do this!)
- `auth-key`: An authentication key (optional if `auth-key-file` is defined)
- `auth-key-file`: A path to a file containing an authentication key (optional if `auth-key` is defined)
- `namespace`: The OpenShift project in which to launch the job (required)
- `image`: The name of a Docker image (required)
- `image-tag`: The image tag. You can use the value `%ems_version%` to match the version tag of ElasticMS (optional)
- `ttl-seconds-after-finished`: Defines the time-to-live (in seconds) after the job has finished (optional) (default value `3600`)
- `backoff-limit`:  Number of restarts allowed after a pod failure (default: `0`)
- `active-deadline-seconds`:  Abort the job if it is not completed within the time limit (in seconds) (default: `60`)
- `labels`:  Add OpenShift labels to the Job and the Pod
- `env`: Additional environment variables as an array of pair {name: string, value: string} (Default value: `[]`)

Example:

```json
[
  {
    "type": "openshift",
    "tag": "toto",
    "base-url": "https://api.paas.my-company.tld:6443/",
    "auth-key": "sha256~my-priVatE_aUthkEy",
    "namespace": "my-team-project",
    "image": "elasticms/cli",
    "image-tag": "%ems_version%",
    "labels": {
      "app": "my-app",
      "app.kubernetes.io/part-of": "my-app",
      "type": "elasticms-runner"
    },
    "env": [{
      "name": "foo",
      "value": "bar"
    }]
  }
]
```

## Docker Remote

This type of runner allows launching a runner via a remote Docker API.  
To do this, you must specify the following configuration parameters:

- `type`: Must be set to `docker-remote` (required)
- `tag`: A name to identify this specific Docker Remote runner configuration (required)
- `base-url`: The base URL to the Docker API (Default value: `http://localhost:2375`)
- `image`: The name of a Docker image (required)
- `image-tag`: The image tag. You can use the value `%ems_version%` to match the version tag of ElasticMS (Default value: `latest`)
- `env`: Additional environment variables as an array of string (Default value: `[]`)
- `socket-path`: Allow to use a local docker via the UNIX socket file 

Warning: this type of runner does not clean up the containers it creates.

Example:

```json
[
  {
    "type": "docker-remote",
    "tag": "docker",
    "image": "busybox",
    "env": [
      "foo=bar"
    ]
  }
]
```

Example with a UNIX socket file:

```json
[
  {
    "type": "docker-remote",
    "tag": "docker",
    "image": "busybox",
    "env": [
      "foo=bar"
    ],
    "base-url": "http://localhost",
    "socket-path": "/var/run/docker.sock"
  }
]
```
