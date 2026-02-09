#!/usr/bin/env bash

cat "${ELASTICMS_INSTANCE_CONFIG_FILE}" \
| sed '/^\s*$/d' \
| grep  -v '^#' \
| sed "s/\([a-zA-Z0-9_]*\)\=\(.*\)/fastcgi_param \1 \2;/g" \
>> "/opt/etc/nginx/conf.d/${ELASTICMS_INSTANCE_NAME}.fastcgi_params"
