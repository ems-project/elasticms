include conf.d/{{ $.Env.ELASTICMS_INSTANCE_NAME }}.security-headers.conf;

expires {{ $.Env.NGINX_BUNDLES_LOCATION_EXPIRES }};
access_log {{ $.Env.NGINX_BUNDLES_LOCATION_ACCESS_LOG }};
add_header Cache-Control "{{ $.Env.NGINX_BUNDLES_LOCATION_CACHE_CONTROL }}" always;

{{- if ne $.Env.NGINX_DEBUG_ENABLED "false" }}
add_header X-Debug-Nginx-Uri "$debug_nginx_uri" always;
add_header X-Debug-Nginx-Symfony-Location "$debug_nginx_location" always;
{{- end }}