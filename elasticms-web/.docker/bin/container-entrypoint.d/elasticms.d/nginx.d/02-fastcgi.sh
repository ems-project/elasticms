#!/usr/bin/env bash

gomplate -f /opt/config/nginx/conf.d/include.fastcgi.conf.gtpl \
         -o "/opt/etc/nginx/conf.d/${ELASTICMS_INSTANCE_NAME}.fastcgi.conf"
