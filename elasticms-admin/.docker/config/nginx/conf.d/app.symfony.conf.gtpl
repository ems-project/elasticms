{{- range (datasource "aliases") }}
{{- $a := strings.TrimSuffix "/" . | strings.TrimPrefix "/" }}

location ~ /{{ $a }}/ {
    alias {{ $.Env.NGINX_PUBLIC_DIR }}/;

{{- if ne $.Env.DEBUG "false" }}
    set $debug_nginx_location "/{{ $a }}/";
{{- end }}

    include conf.d/{{ $.Env.ELASTICMS_INSTANCE_NAME }}.security-headers.conf;

    try_files $uri $uri/ /{{ $a }}/index.php$is_args$args;
}

{{- end }}

location / {

{{- if ne .Env.DEBUG "false" }}
    set $debug_nginx_location "/";
{{- end }}

    include conf.d/{{ .Env.ELASTICMS_INSTANCE_NAME }}.security-headers.conf;

    try_files $uri $uri/ /index.php$is_args$args;
}
