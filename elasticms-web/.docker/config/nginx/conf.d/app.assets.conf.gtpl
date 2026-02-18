{{- range (ds "environments") }}
{{- $a := strings.TrimSuffix "/" .alias | strings.TrimPrefix "/" }}
{{- $e := strings.TrimSuffix "/" .env | strings.TrimPrefix "/" }}

location ^~ /{{ $a }}/apple-touch-icon.png {

    alias /app/src/elasticms/public/;

    include conf.d/{{ $.Env.ELASTICMS_INSTANCE_NAME }}.statics.conf;

{{- if ne $.Env.DEBUG "false" }}
    set $debug_nginx_uri "$uri";
    set $debug_nginx_location "/{{ $a }}/apple-touch-icon.png";
{{- end }}

    try_files /apple-touch-icon.png /index.php$is_args$args;
}

location ^~ /{{ $a }}/robots.txt {

    alias /app/src/elasticms/public/;

    include conf.d/{{ $.Env.ELASTICMS_INSTANCE_NAME }}.statics.conf;

{{- if ne $.Env.DEBUG "false" }}
    set $debug_nginx_uri "$uri";
    set $debug_nginx_location "/{{ $a }}/robots.txt";
{{- end }}

    try_files /robots.txt /index.php$is_args$args;
}

location ^~ /{{ $a }}/favicon.ico {

    alias /app/src/elasticms/public/;

    include conf.d/{{ $.Env.ELASTICMS_INSTANCE_NAME }}.statics.conf;

{{- if ne $.Env.DEBUG "false" }}
    set $debug_nginx_uri "$uri";
    set $debug_nginx_location "/{{ $a }}/favicon.ico";
{{- end }}

    try_files /favicon.ico /index.php$is_args$args;
}

location ~ ^/{{ $a }}/{{ $.Env.NGINX_CUSTOM_ASSETS_RC }}/ {
    alias /app/src/elasticms/public/$1/;

    include conf.d/{{ $.Env.ELASTICMS_INSTANCE_NAME }}.statics.conf;

{{- if ne $.Env.DEBUG "false" }}
    set $debug_nginx_uri "$uri";
    set $debug_nginx_location "/{{ $a }}/$1/";
{{- end }}

    try_files $uri /index.php$is_args$args;
}

location ^~ /{{ $a }}/bundles/emsch_assets {

    alias /app/src/elasticms/public/bundles/{{ $e }}/;

    include conf.d/{{ $.Env.ELASTICMS_INSTANCE_NAME }}.statics.conf;

{{- if ne $.Env.DEBUG "false" }}
    set $debug_nginx_uri "$uri";
    set $debug_nginx_location "/{{ $a }}/bundles/emsch_assets";
{{- end }}

    try_files $uri /index.php$is_args$args;
}

{{- end }}

#
# ROOT
#

location ^~ /apple-touch-icon.png {
    include conf.d/{{ $.Env.ELASTICMS_INSTANCE_NAME }}.statics.conf;

{{- if ne $.Env.DEBUG "false" }}
    set $debug_nginx_uri "$uri";
    set $debug_nginx_location "/apple-touch-icon.png";
{{- end }}

    try_files $uri /index.php$is_args$args;
}

location ^~ /robots.txt {
    include conf.d/{{ $.Env.ELASTICMS_INSTANCE_NAME }}.statics.conf;

{{- if ne $.Env.DEBUG "false" }}
    set $debug_nginx_uri "$uri";
    set $debug_nginx_location "/robots.txt";
{{- end }}

    try_files $uri /index.php$is_args$args;
}

location ^~ /favicon.ico {
    include conf.d/{{ $.Env.ELASTICMS_INSTANCE_NAME }}.statics.conf;

{{- if ne $.Env.DEBUG "false" }}
    set $debug_nginx_uri "$uri";
    set $debug_nginx_location "/favicon.ico";
{{- end }}

    try_files $uri /index.php$is_args$args;
}

location ~ ^/{{ $.Env.NGINX_CUSTOM_ASSETS_RC }}/ {
    include conf.d/{{ $.Env.ELASTICMS_INSTANCE_NAME }}.statics.conf;

{{- if ne $.Env.DEBUG "false" }}
    set $debug_nginx_uri "$uri";
    set $debug_nginx_location "/$1/";
{{- end }}

    try_files $uri /index.php$is_args$args;
}
