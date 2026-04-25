#!/usr/bin/env bash

VALUE=$(
  awk -F= '
    /^[[:space:]]*#/ { next }
    $1=="NGINX_ENVIRONMENTS"{
      sub(/^[^=]+= */, "")
      print
    }
  ' "${ELASTICMS_INSTANCE_CONFIG_FILE}"
)

VALUE="${VALUE#\'}"
VALUE="${VALUE%\'}"
VALUE="${VALUE#\"}"
VALUE="${VALUE%\"}"

printf '%s' "$VALUE" \
| jq -e 'if type=="array" then . else empty end' \
> "${NGINX_ENVIRONMENTS_TEMP_JSON_FILE}" 2>/dev/null \
|| echo '[]' > "$NGINX_ENVIRONMENTS_TEMP_JSON_FILE"

gomplate -f /opt/config/nginx/conf.d/app.assets.conf.gtpl \
         -d "environments=file://${NGINX_ENVIRONMENTS_TEMP_JSON_FILE}?type=application/json" \
         -o "/opt/etc/nginx/conf.d/${ELASTICMS_INSTANCE_NAME}.assets.conf"

gomplate -f /opt/config/nginx/conf.d/include.statics.conf.gtpl \
         -o "/opt/etc/nginx/conf.d/${ELASTICMS_INSTANCE_NAME}.statics.conf"
