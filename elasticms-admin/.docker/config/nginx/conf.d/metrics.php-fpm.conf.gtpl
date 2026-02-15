location = /{{ $.Env.ELASTICMS_INSTANCE_NAME }}-status {

    access_log off;

    include conf.d/default.metrics-permissions.conf;

    include fastcgi_params;

    fastcgi_param SCRIPT_FILENAME /{{ $.Env.ELASTICMS_INSTANCE_NAME }}-status;
    fastcgi_param QUERY_STRING $args;

    fastcgi_pass unix:/app/var/run/php-fpm/{{ $.Env.ELASTICMS_INSTANCE_NAME }}.php-fpm.sock;

}

location = /{{ $.Env.ELASTICMS_INSTANCE_NAME }}-ping {

    access_log off;

    include conf.d/default.metrics-permissions.conf;

    include fastcgi_params;

    fastcgi_param SCRIPT_FILENAME /{{ $.Env.ELASTICMS_INSTANCE_NAME }}-ping;
    fastcgi_param QUERY_STRING $args;

    fastcgi_pass unix:/app/var/run/php-fpm/{{ $.Env.ELASTICMS_INSTANCE_NAME }}.php-fpm.sock;

}