fastcgi_pass unix:/app/var/run/php-fpm/{{ $.Env.ELASTICMS_INSTANCE_NAME }}.php-fpm.sock;
fastcgi_split_path_info ^(.+\.php)(/.*)$;

include fastcgi_params;

fastcgi_param SERVER_NAME $http_host;
fastcgi_param SCRIPT_NAME {{ $.Env.SYMFONY_SCRIPT_NAME_NGINX_VARIABLE_NAME }};
fastcgi_param SCRIPT_FILENAME {{ $.Env.NGINX_PUBLIC_DIR }}/index.php;

fastcgi_param DOCUMENT_ROOT $realpath_root;
fastcgi_param HTTP_AUTHORIZATION $http_authorization;

{{- if ne $.Env.DEBUG "false" }}
add_header X-Debug-FastCGI-Script-Filename "{{ $.Env.NGINX_PUBLIC_DIR }}/index.php" always;
add_header X-Debug-FastCGI-Script-Document-Root "$realpath_root" always;
add_header X-Debug-FastCGI-Script-Name "{{ $.Env.SYMFONY_SCRIPT_NAME_NGINX_VARIABLE_NAME }}" always;
{{- end }}

{{- if ne $.Env.NGINX_FASTCGI_CACHE_ENABLED "false" }}
fastcgi_cache        {{ $.Env.NGINX_FASTCGI_CACHE_NAME }};
fastcgi_cache_key    {{ $.Env.NGINX_FASTCGI_CACHE_KEY }};
fastcgi_cache_valid  {{ $.Env.NGINX_FASTCGI_CACHE_VALID_OK_CODE }} {{ $.Env.NGINX_FASTCGI_CACHE_VALID_OK_DURATION }};
fastcgi_cache_valid  {{ $.Env.NGINX_FASTCGI_CACHE_VALID_NOK_CODE }} {{ $.Env.NGINX_FASTCGI_CACHE_VALID_NOK_DURATION }};

fastcgi_cache_bypass {{ $.Env.NGINX_FASTCGI_NO_CACHE_METHOD_MAP_VAR_NAME }} {{ $.Env.NGINX_FASTCGI_NO_CACHE_COOKIE_MAP_VAR_NAME }};
fastcgi_no_cache     {{ $.Env.NGINX_FASTCGI_NO_CACHE_METHOD_MAP_VAR_NAME }} {{ $.Env.NGINX_FASTCGI_NO_CACHE_COOKIE_MAP_VAR_NAME }};

add_header X-Cache $upstream_cache_status always;
{{- end }}