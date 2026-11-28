# Job API

The Job API creates, starts and monitors asynchronous command jobs in elasticMS Admin.

Every request must be authenticated with an API token. See the [Login API](./login.md) documentation
for token generation and validation.

In the examples below, the token is available in the `AUTH_TOKEN` environment variable:

```shell
export AUTH_TOKEN='nlpUnMR/W8bgSSclYXI2G0dP5REdp5yhvaXfMDV/he+XgQgI7pIRqkuNqsJRJzoYvYM='
```

## Endpoints

| Action                    | Endpoint                              | Usage                                        |
| ------------------------- | ------------------------------------- | -------------------------------------------- |
| Create a command job      | `POST /api/admin/job/create`          | Create a persisted job for a command.        |
| Get a job status          | `GET /api/admin/job/{job}/status`     | Poll the current state and output.           |
| Start a job               | `POST /api/admin/start-job/{job}`     | Start a job owned by the authenticated user. |
| Start the next tagged job | `POST /api/admin/next-job/{tag}`      | Let a worker claim and start the next job.   |
| Append output to a job    | `POST /api/admin/job-write/{job}`     | Stream command output back to Admin.         |
| Mark a job as completed   | `POST /api/admin/job-completed/{job}` | Finish a successful worker execution.        |
| Mark a job as failed      | `POST /api/admin/job-failed/{job}`    | Finish a failed worker execution.            |

## Create a command job

Use `POST /api/admin/job/create` to create a job for a command.

Request body:

```json
{
    "command": "ems:job:run"
}
```

Example:

```shell
curl -X POST \
     -H "X-Auth-Token: ${AUTH_TOKEN}" \
     -H 'Content-Type: application/json' \
     -H 'Accept: application/json' \
     http://localhost:8881/api/admin/job -d \
'{
  "class": "EMS\\CoreBundle\\Entity\\Job",
  "arguments": [],
  "properties": {
    "command": "ems:job:run"
  }
}' -w '\n'
```

Successful response:

```json
{
    "id": "43"
}
```

The `command` field is required. The created job belongs to the authenticated user.

## Get a job status

Use `GET /api/admin/job/{job}/status` to retrieve the current state of a job.

```shell
curl -X GET \
     -H "X-Auth-Token: ${AUTH_TOKEN}" \
     -H 'Accept: application/json' \
     http://localhost:8881/api/admin/job-status/43 -w '\n'
```

Successful response:

```json
{
    "id": "43",
    "created": "2026-05-16T07:16:04+00:00",
    "modified": "2026-05-16T07:16:04+00:00",
    "command": "ems:job:run",
    "user": "demo",
    "done": false,
    "output": null,
    "started": false,
    "status": ""
}
```

`created` and `modified` are returned in ISO 8601 format. `done` becomes `true` when the job is
completed or failed.

A job can also be tagged.

```shell
curl -X POST \
     -H "X-Auth-Token: ${AUTH_TOKEN}" \
     -H 'Content-Type: application/json' \
     -H 'Accept: application/json' \
     http://localhost:8881/api/admin/job -d \
'{
  "class": "EMS\\CoreBundle\\Entity\\Job",
  "arguments": [],
  "properties": {
    "command": "ems:job:run",
    "tag": "docker"
  }
}' -w '\n'
```

## Start a job

Use `POST /api/admin/start-job/{job}` to start a job owned by the authenticated user.

```shell
curl -X POST \
     -H "X-Auth-Token: ${AUTH_TOKEN}" \
     -H 'Accept: application/json' \
     http://localhost:8881/api/admin/start-job/43 -w '\n'
```

Successful synchronous response:

```json
{
    "message": "job started",
    "job_id": 43,
    "success": true,
    "acknowledged": true,
    "notice": ["The job 43 is done"]
}
```

If the job cannot be executed from the web request, or if the job has a tag, Admin schedules it for
a worker instead:

```json
{
    "message": "job is scheduled",
    "job_id": 45,
    "success": true,
    "acknowledged": true
}
```

The endpoint refuses to start a job created by another user.

## Worker flow

Tagged jobs are designed to be executed by an external worker.

The worker starts by claiming the next available job for a tag (the tag `docker`in the following
example) :

```shell
curl -X POST \
     -H "X-Auth-Token: ${AUTH_TOKEN}" \
     -H 'Accept: application/json' \
     http://localhost:8881/api/admin/next-job/docker -w '\n'
```

Successful response when a job is available:

```json
{
    "message": "job 45 flagged has started",
    "job_id": "45",
    "command": "ems:job:run",
    "output": null,
    "success": true,
    "acknowledged": true
}
```

When no job is available, the API returns:

```json
{
    "success": true,
    "message": "no next job",
    "acknowledged": true
}
```

To claim a specific tagged job, pass its identifier with the `job_id` query parameter:

```shell
curl -X POST \
     -H "X-Auth-Token: ${AUTH_TOKEN}" \
     -H 'Accept: application/json' \
     'http://localhost:8881/api/admin/next-job/docker?job_id=123' -w '\n'
```

The worker can then execute the returned command outside the web request.

## Write job output

Use `POST /api/admin/job-write/{job}` to append output while the worker is running.

Request body:

```json
{
    "message": "Processing file 10/42",
    "new-line": true
}
```

Example:

```shell
curl -X POST \
     -H "X-Auth-Token: ${AUTH_TOKEN}" \
     -H 'Content-Type: application/json' \
     -H 'Accept: application/json' \
     http://localhost:8881/api/admin/job-write/46 -d \
'{
  "message": "Processing file 10/42",
  "new-line": true
}' -w '\n'
```

Set `new-line` to `false` to append the message without adding a line break.

Successful response:

```json
{
    "success": true,
    "acknowledged": true
}
```

## Complete or fail a job

Use `POST /api/admin/job-completed/{job}` when the worker execution succeeds.

```shell
curl -X POST \
     -H "X-Auth-Token: ${AUTH_TOKEN}" \
     -H 'Accept: application/json' \
     http://localhost:8881/api/admin/job-completed/45 -w '\n'
```

Use `POST /api/admin/job-failed/{job}` when the worker execution fails.

Request body:

```json
{
    "message": "Command exited with status 1"
}
```

Example:

```shell
curl -X POST \
     -H "X-Auth-Token: ${AUTH_TOKEN}" \
     -H 'Content-Type: application/json' \
     -H 'Accept: application/json' \
     http://localhost:8881/api/admin/job-failed/46 -d \
'{
  "message": "Command exited with status 1"
}' -w '\n'
```

Both endpoints mark the job as done. The failed endpoint stores the error message in the job output
and sets the job status to failed.

## Error handling

| Symptom                | Check                                                                                |
| ---------------------- | ------------------------------------------------------------------------------------ |
| `401 Unauthorized`     | The `X-Auth-Token` header is missing or invalid.                                     |
| `403 Forbidden`        | The authenticated user is not allowed to start or manage the requested job.          |
| `400 Bad Request`      | The JSON body is invalid, `command` is missing, or the tagged job is invalid.        |
| `404 Not Found`        | The job identifier does not exist.                                                   |
| Job remains scheduled  | A worker must call `POST /api/admin/next-job/{tag}` and execute the command.         |
| No job returned by tag | Verify the tag, whether the job already started, and whether a scheduled job exists. |
