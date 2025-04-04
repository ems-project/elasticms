# Runners

Using the environment variable `EMS_RUNNERS`, you can define multiple runners of different kinds.  
A runner is used to execute a command asynchronously in another process, platform, environment, or stack.  
See below for the list of available types of runners.

The `EMS_RUNNERS` environment variable is a JSON array of configurations.  
Each configuration's parameters will depend on the type of runner you want to use, but they will all share the following parameters:

- `type`: identifies the kind of runner (e.g., `openshift`)
- `tag`: a name you provide to identify a specific runner configuration

This means you can define multiple runner configurations of the same type.

## OpenShift

This type of runner allows launching a runner via an OpenShift Job.  
To do this, you must specify the following configuration parameters:

- `type`: Must be set to `openshift` (required)
- `tag`: A name to identify this specific OpenShift runner configuration (required)
- `base-url`: The base URL of your OpenShift PaaS (required)
- `auth-key`: An authentication key (optional if `auth-key-file` is defined)
- `auth-key-file`: A path to a file containing an authentication key (optional if `auth-key` is defined)
- `namespace`: The OpenShift project in which to launch the job (required)
- `image`: The name of a Docker image (required)
- `image-tag`: The image tag. You can use the value `%ems_version%` to match the version tag of ElasticMS (optional)
- `ttl-seconds-after-finished`: Defines the time-to-live (in seconds) after the job has finished (optional) (default value `3600`)
