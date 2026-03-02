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

{{ range $key, $value := ds "variables" }}
{{- if ne $value "" }}
{{- $safe_value := $value | printf "%s" | strings.ReplaceAll "\"" "\\\"" }}
env[{{ $key }}] = "{{ $safe_value }}"
{{- end }}
{{- end }}
