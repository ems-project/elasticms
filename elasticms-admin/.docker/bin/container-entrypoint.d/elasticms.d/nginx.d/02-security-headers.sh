#!/usr/bin/env bash

gomplate -f /opt/config/nginx/conf.d/security-headers.conf.gtpl \
         -o "/opt/etc/nginx/conf.d/${ELASTICMS_INSTANCE_NAME}.security-headers.conf"
