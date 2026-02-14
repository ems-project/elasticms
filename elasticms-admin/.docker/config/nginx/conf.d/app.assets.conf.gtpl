{{- range (datasource "aliases") }}
{{- $a := strings.TrimSuffix "/" . | strings.TrimPrefix "/" }}

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

location ^~ /{{ $a }}/bundles/ {

    alias /app/src/elasticms/public/bundles/;

    include conf.d/{{ $.Env.ELASTICMS_INSTANCE_NAME }}.statics.conf;

{{- if ne $.Env.DEBUG "false" }}
    set $debug_nginx_uri "$uri";
    set $debug_nginx_location "/{{ $a }}/bundles/";
{{- end }}

    try_files $uri /index.php$is_args$args;
}

{{- end }}

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

location ^~ /bundles/ {
    include conf.d/{{ $.Env.ELASTICMS_INSTANCE_NAME }}.statics.conf;

{{- if ne $.Env.DEBUG "false" }}
    set $debug_nginx_uri "$uri";
    set $debug_nginx_location "/bundles/";
{{- end }}

    try_files $uri /index.php$is_args$args;
}
