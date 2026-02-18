[{{ $.Env.ELASTICMS_INSTANCE_NAME }}]
listen = /app/var/run/php-fpm/{{ $.Env.ELASTICMS_INSTANCE_NAME }}.php-fpm.sock
listen.mode = 0777
listen.allowed_clients = 127.0.0.1

pm = ondemand
pm.max_children = {{.Env.PHP_FPM_MAX_CHILDREN}}

pm.status_path = /{{ $.Env.ELASTICMS_INSTANCE_NAME }}-status
ping.path = /{{ $.Env.ELASTICMS_INSTANCE_NAME }}-ping

clear_env = no

{{- range $key, $value := ds "variables" }}
{{- if ne $value "" }}
{{- $safe_value := $value | printf "%s" | strings.ReplaceAll "\"" "\\\"" }}
env[{{ $key }}] = "{{ $safe_value }}"
{{- end }}
{{- end }}
