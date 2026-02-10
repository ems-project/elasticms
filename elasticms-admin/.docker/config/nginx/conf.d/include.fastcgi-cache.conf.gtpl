fastcgi_cache        {{ .Env.NGINX_FASTCGI_CACHE_NAME }};
fastcgi_cache_key    {{ .Env.NGINX_FASTCGI_CACHE_KEY }};
fastcgi_cache_valid  {{ .Env.NGINX_FASTCGI_CACHE_VALID_OK_CODE }} {{ .Env.NGINX_FASTCGI_CACHE_VALID_OK_DURATION }};
fastcgi_cache_valid  {{ .Env.NGINX_FASTCGI_CACHE_VALID_NOK_CODE }} {{ .Env.NGINX_FASTCGI_CACHE_VALID_NOK_DURATION }};

fastcgi_cache_bypass {{ .Env.NGINX_FASTCGI_NO_CACHE_METHOD_MAP_VAR_NAME }} {{ .Env.NGINX_FASTCGI_NO_CACHE_COOKIE_MAP_VAR_NAME }};
fastcgi_no_cache     {{ .Env.NGINX_FASTCGI_NO_CACHE_METHOD_MAP_VAR_NAME }} {{ .Env.NGINX_FASTCGI_NO_CACHE_COOKIE_MAP_VAR_NAME }};

add_header X-Cache $upstream_cache_status always;