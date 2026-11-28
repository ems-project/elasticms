{{- range (ds "environments") }}
{{- $a := strings.TrimSuffix "/" .alias | strings.TrimPrefix "/" }}
{{- $e := strings.TrimSuffix "/" .env | strings.TrimPrefix "/" }}

{{- if ne $a "" }}

# The alias root without its trailing slash matched no location and answered
# 404 (/ems, /preview/demo). Send it to the alias.
location = /{{ $a }} {
    return 301 /{{ $a }}/$is_args$args;
}
{{- end }}

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
