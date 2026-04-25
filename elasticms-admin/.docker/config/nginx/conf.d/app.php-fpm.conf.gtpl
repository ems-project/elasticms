{{- range (datasource "aliases") }}
{{- $a := strings.TrimSuffix "/" . | strings.TrimPrefix "/" }}

location = /{{ $a }}/index.php {

    include conf.d/{{ $.Env.ELASTICMS_INSTANCE_NAME }}.fastcgi.conf;
    include conf.d/{{ $.Env.ELASTICMS_INSTANCE_NAME }}.security-headers.conf;

{{- if ne $.Env.NGINX_DEBUG_ENABLED "false" }}
    add_header X-Debug-Nginx-PHP-FPM-Location "/{{ $a }}/index.php" always;
    add_header X-Debug-Nginx-Symfony-Location "$debug_nginx_location" always;
    add_header X-Debug-Nginx-Uri "$debug_nginx_uri" always;
{{- end }}

    # Prevents URIs that include the front controller. This will 404:
    # http://example.com/index.php/some-path
    # Remove the internal directive to allow URIs like this
    internal;

}

{{- end }}

location = /index.php {

    include conf.d/{{ $.Env.ELASTICMS_INSTANCE_NAME }}.fastcgi.conf;
    include conf.d/{{ $.Env.ELASTICMS_INSTANCE_NAME }}.security-headers.conf;

{{- if ne .Env.NGINX_DEBUG_ENABLED "false" }}
    add_header X-Debug-Nginx-PHP-FPM-Location "/index.php" always;
    add_header X-Debug-Nginx-Symfony-Location "$debug_nginx_location" always;
    add_header X-Debug-Nginx-Uri "$debug_nginx_uri" always;
{{- end }}

    # Prevents URIs that include the front controller. This will 404:
    # http://example.com/index.php/some-path
    # Remove the internal directive to allow URIs like this
    internal;

}
