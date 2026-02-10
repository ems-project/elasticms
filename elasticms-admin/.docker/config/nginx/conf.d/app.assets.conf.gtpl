{{- range (datasource "aliases") }}
{{- $a := strings.TrimSuffix "/" . | strings.TrimPrefix "/" }}

location ^~ /{{ $a }}/bundles/ {
    alias {{ $.Env.NGINX_PUBLIC_DIR }}/bundles/;

    include conf.d/{{ $.Env.ELASTICMS_INSTANCE_NAME }}.security-headers.conf;

    expires {{ $.Env.NGINX_BUNDLES_LOCATION_EXPIRES }};
    access_log {{ $.Env.NGINX_BUNDLES_LOCATION_ACCESS_LOG }};
    add_header Cache-Control "{{ $.Env.NGINX_BUNDLES_LOCATION_CACHE_CONTROL }}" always;

{{- if ne $.Env.DEBUG "false" }}
    add_header X-Debug-Nginx-Location "/{{ $a }}/bundles/" always;
{{- end }}

    try_files $uri =404;
}

{{- end }}

location ^~ /bundles/ {
    include conf.d/{{ .Env.ELASTICMS_INSTANCE_NAME }}.security-headers.conf;

    expires {{ .Env.NGINX_BUNDLES_LOCATION_EXPIRES }};
    access_log {{ .Env.NGINX_BUNDLES_LOCATION_ACCESS_LOG }};
    add_header Cache-Control "{{ .Env.NGINX_BUNDLES_LOCATION_CACHE_CONTROL }}" always;

{{- if ne .Env.DEBUG "false" }}
    add_header X-Debug-Nginx-Location "/bundles/" always;
{{- end }}

    try_files $uri =404;
}
