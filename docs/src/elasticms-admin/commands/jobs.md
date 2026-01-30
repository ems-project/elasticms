# Job

## Overview

The **Job system** in ElasticMS provides a way to execute Symfony console commands through various
mechanisms:

- **Local execution** (within the CMS backend process)
- **Worker execution** (a remote worker periodically fetching available jobs from the admin)
- **Runner execution** (an isolated runtime, e.g., a Docker container or OpenShift job initialized
  on demand)

Each job is represented by a **Doctrine entity** that stores:

- The command name and arguments
- Execution context (initiator, starting date, etc.)
- The execution **output**

Before execution, the owner of the job **resolves placeholders** in the job’s command string — this
allows dynamic configuration using environment variables, secret files, or encoded data.

This is where the `RuntimeEnvPlaceholderResolver` comes into play.

---

## Purpose of the Resolver

The `RuntimeEnvPlaceholderResolver` provides **runtime replacement** of Symfony-style environment
expressions directly in strings.

It supports placeholders such as:

```text
%env(string:MY_ENV_VAR)%
%env(base64:API_KEY_BASE64)%
%env(json:CONFIG_JSON)%
%env(file:SECRET_PATH)%
```

and can chain multiple processors:

```text
%env(trim:base64:API_CREDENTIALS)%
```

---

## Typical Use Case

When a **Job** entity defines a command like:

```text
ems:index:sync %env(string:API_SOURCE_ENDPOINT)% %env(string:TARGET_ALIAS_BASEURL)% --source-headers='{"Authorization":"Bearer %env(string:SOURCE_AUTHKEY)%"}' --target-headers='{"Authorization":"Basic %env(string:TARGET_BASIC_KEY)%"}'
```

Before executing it, the owner of the job should resolve the placeholders using the
`RuntimeEnvPlaceholderResolver`.  
This allows the same Job definition to run consistently in different environments (local dev,
Docker, OpenShift, etc.) without hardcoding sensitive values.

---

## Supported Processors

| Processor      | Description                                                                   | Example                          |
| -------------- | ----------------------------------------------------------------------------- | -------------------------------- |
| `string`       | Casts the value to string                                                     | `%env(string:API_KEY)%`          |
| `int`          | Casts to integer                                                              | `%env(int:MAX_RETRIES)%`         |
| `float`        | Casts to float                                                                | `%env(float:THRESHOLD)%`         |
| `bool`         | Converts to boolean (`true`, `1`, `yes`, `on`)                                | `%env(bool:DEBUG_ENABLED)%`      |
| `trim`         | Trims whitespace                                                              | `%env(trim:API_KEY)%`            |
| `super_trim`   | Trims whitespace and replaces all contiguous whitespaces with a single space. | `%env(super_trim:API_KEY)%`      |
| `base64`       | Decodes Base64 data                                                           | `%env(base64:API_TOKEN)%`        |
| `base64encode` | Base64 encode data                                                            | `%env(base64encode:API_TOKEN)%`  |
| `json`         | Parses JSON and re-encodes to string                                          | `%env(json:CONFIG_JSON)%`        |
| `json_encode`  | JSON encodes                                                                  | `%env(json_encode:CONFIG_JSON)%` |
| `file`         | Reads contents of a file (path from env var)                                  | `%env(file:SECRET_PATH)%`        |
| `urlencode`    | URL-encodes the value                                                         | `%env(urlencode:QUERY)%`         |
| `urldecode`    | URL-decodes the value                                                         | `%env(urldecode:QUERY)%`         |

You can chain processors to compose transformations:

```text
%env(string:base64:MY_KEY)%
%env(trim:file:API_TOKEN_PATH)%
```

---

## Security Considerations

- **Secrets stay externalized** — values are only injected at runtime, not stored in the database or
  configuration files.
- Avoid logging resolved command strings containing sensitive values.
- When running in containers or OpenShift, ensure required environment variables or secret mounts
  are available at runtime.

---

## Future Extensions

Possible enhancements for future integration:

- **`key:` processor** to extract specific keys from JSON env vars
  (`%env(key:db.password:CONFIG_JSON)%`)
- **`vault:` processor** to fetch secrets from an external vault (HashiCorp, Azure Key Vault, etc.)
- **Caching** resolved values to avoid repeated I/O operations for file or JSON env vars.
