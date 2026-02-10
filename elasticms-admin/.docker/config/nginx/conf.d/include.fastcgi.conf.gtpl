fastcgi_pass unix:/app/var/run/php-fpm/php-fpm.sock;
fastcgi_split_path_info ^(.+\.php)(/.*)$;

include fastcgi_params;
include conf.d/{{ .Env.ELASTICMS_INSTANCE_NAME }}.fastcgi_params;

fastcgi_param SCRIPT_NAME {{ .Env.SYMFONY_SCRIPT_NAME_NGINX_VARIABLE_NAME }};
fastcgi_param SCRIPT_FILENAME {{ $.Env.NGINX_PUBLIC_DIR }}/index.php;

fastcgi_param DOCUMENT_ROOT $realpath_root;
fastcgi_param HTTP_AUTHORIZATION $http_authorization;

{{- if ne .Env.DEBUG "false" }}
add_header X-Debug-FastCGI-Script-Filename "{{ $.Env.NGINX_PUBLIC_DIR }}/index.php" always;
add_header X-Debug-FastCGI-Script-Document-Root "$realpath_root" always;
add_header X-Debug-FastCGI-Script-Name "{{ .Env.SYMFONY_SCRIPT_NAME_NGINX_VARIABLE_NAME }}" always;
{{- end }}

{{- if ne .Env.NGINX_FASTCGI_CACHE_ENABLED "false" }}
include /opt/etc/nginx/conf.d/fastcgi-cache.conf;
{{- end }}