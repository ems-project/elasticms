{{- range (ds "environments") }}
{{- $a := strings.TrimSuffix "/" .alias | strings.TrimPrefix "/" }}
{{- $e := strings.TrimSuffix "/" .env | strings.TrimPrefix "/" }}

location /{{ $a }}/ {
    alias {{ $.Env.NGINX_PUBLIC_DIR }}/;

{{- if ne $.Env.NGINX_DEBUG_ENABLED "false" }}
    set $debug_nginx_location "/{{ $a }}/";
    set $debug_nginx_uri "$uri";
    add_header X-Debug-Nginx-Uri "$debug_nginx_uri" always;
    add_header X-Debug-Nginx-Symfony-Location "$debug_nginx_location" always;
{{- end }}

    include conf.d/{{ $.Env.ELASTICMS_INSTANCE_NAME }}.security-headers.conf;

    try_files $uri $uri/ /index.php$is_args$args;
}

{{- end }}

location / {

{{- if ne $.Env.NGINX_DEBUG_ENABLED "false" }}
    set $debug_nginx_location "/";
    set $debug_nginx_uri "$uri";
    add_header X-Debug-Nginx-Uri "$debug_nginx_uri" always;
    add_header X-Debug-Nginx-Symfony-Location "$debug_nginx_location" always;
{{- end }}

    include conf.d/{{ $.Env.ELASTICMS_INSTANCE_NAME }}.security-headers.conf;

    try_files $uri $uri/ /index.php$is_args$args;
}
