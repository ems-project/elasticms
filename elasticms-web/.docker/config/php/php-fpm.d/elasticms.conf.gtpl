[{{ $.Env.ELASTICMS_INSTANCE_NAME }}]
access.log = {{ $.Env.PHP_FPM_ACCESS_LOG }}
access.format = "{{ $.Env.PHP_FPM_ACCESS_FORMAT }}"

clear_env = {{ $.Env.PHP_FPM_CLEAR_ENV }}

listen = /app/var/run/php-fpm/{{ $.Env.ELASTICMS_INSTANCE_NAME }}.php-fpm.sock
listen.mode = {{ $.Env.PHP_FPM_LISTEN_MODE }}
listen.allowed_clients = {{ $.Env.PHP_FPM_LISTEN_ALLOWED_CLIENTS }}

pm = {{ $.Env.PHP_FPM_PM }}
pm.max_children = {{ $.Env.PHP_FPM_PM_MAX_CHILDREN }}
pm.process_idle_timeout = {{ $.Env.PHP_FPM_PM_PROCESS_IDLE_TIMEOUT }}
pm.max_requests = {{ $.Env.PHP_FPM_PM_MAX_REQUESTS }}

pm.status_path = /{{ $.Env.ELASTICMS_INSTANCE_NAME }}-status
ping.path = /{{ $.Env.ELASTICMS_INSTANCE_NAME }}-ping

catch_workers_output = {{ $.Env.PHP_FPM_CATCH_WORKERS_OUTPUT }}
decorate_workers_output = {{ $.Env.PHP_FPM_DECORATE_WORKERS_OUTPUT }}

; base-php's own pool bounds a request and logs the slow ones; these per-pool
; settings did not reach the instance pools. Without request_terminate_timeout
; a worker blocked on I/O -- which max_execution_time does not count -- was never
; given back. Same values as base-php's pool, so they follow the same settings;
; one slow log per instance, under /app/var/log so that it is rotated.
request_terminate_timeout = {{ $.Env.PHP_FPM_REQUEST_TERMINATE_TIMEOUT }}
request_terminate_timeout_track_finished = {{ $.Env.PHP_FPM_REQUEST_TERMINATE_TIMEOUT_TRACK_FINISHED }}
slowlog = /app/var/log/php-fpm-{{ $.Env.ELASTICMS_INSTANCE_NAME }}-slow.log
request_slowlog_timeout = {{ $.Env.PHP_FPM_REQUEST_SLOWLOG_TIMEOUT }}
request_slowlog_trace_depth = {{ $.Env.PHP_FPM_REQUEST_SLOWLOG_TRACE_DEPTH }}

{{- /*
  env[] values are double-quoted ini strings, and php-fpm's ini parser expands a
  ${NAME} inside them against its own environment: a value holding a literal
  "${" -- a password -- reached PHP rewritten. Escape the three characters that
  are special there, the backslash first.
*/}}
{{ range $key, $value := ds "variables" }}
{{- if ne $value "" }}
{{- $safe_value := $value | printf "%s" | strings.ReplaceAll "\\" "\\\\" | strings.ReplaceAll "\"" "\\\"" | strings.ReplaceAll "$" "\\$" }}
env[{{ $key }}] = "{{ $safe_value }}"
{{- end }}
{{- end }}
