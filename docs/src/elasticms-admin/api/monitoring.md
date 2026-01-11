# Monitoring

You can monitor an instance of elasticMS admin via the URL `/status`. This URL is declined in 3
formats:

- HTML: `/status`
- JSON: `/status.json`
- XML: `/status.xml`

If something goes wrong with the elasticsearch cluster an HTTP call will return a `500` or a `503`
HTTP return code.

## Extra parameters

By default, the `/status` URL does not return a `500` HTTP error if there is jobs in error or
message stuck in the queue. But, some limits may be defined to do so:

- `jobs_pending_limit`: if defined, the URL will return a `500` code if the number of pending jobs
  is reached
- `jobs_failed_limit`: if defined, the URL will return a `500` code if the number of failed jobs is
  reached
- `bus_queue_limit`: if defined, the URL will return a `500` code if the number of messages in the
  bus queue is reached
- `bus_failed_limit`: if defined, the URL will return a `500` code if the number of messages in the
  failed queue is reached
