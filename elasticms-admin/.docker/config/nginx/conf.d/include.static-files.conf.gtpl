# Fixed files -- robots.txt, favicon.ico, the icons -- whose name does not change
# when they do: a shorter cache and no "immutable" (NGINX_STATIC_LOCATION_*), or
# a new robots.txt would not reach browsers and proxies for a year.
include conf.d/{{ $.Env.ELASTICMS_INSTANCE_NAME }}.security-headers.conf;

expires {{ $.Env.NGINX_STATIC_LOCATION_EXPIRES }};
access_log {{ $.Env.NGINX_STATIC_LOCATION_ACCESS_LOG }};
add_header Cache-Control "{{ $.Env.NGINX_STATIC_LOCATION_CACHE_CONTROL }}" always;

{{- if ne $.Env.NGINX_DEBUG_ENABLED "false" }}
add_header X-Debug-Nginx-Uri "$debug_nginx_uri" always;
add_header X-Debug-Nginx-Symfony-Location "$debug_nginx_location" always;
{{- end }}