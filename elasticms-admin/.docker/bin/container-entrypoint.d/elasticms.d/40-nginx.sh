#!/usr/bin/env bash

log "INFO" "| Configure ElasticMS Nginx VirtualHosts ..."

if [[ ! -z ${NGINX_ENABLED} ]] && [[ ${NGINX_ENABLED,,} = true ]]; then

    ALIASES_TEMP_JSON_FILE="${TMPDIR}/${ELASTICMS_INSTANCE_NAME}-aliases.json"
    SYMFONY_SCRIPT_NAME_NGINX_VARIABLE_NAME="\$${ELASTICMS_INSTANCE_NAME//-/_}_symfony_script_name"

    log "INFO" "+ Configure [ ${ELASTICMS_INSTANCE_NAME} ] VirtualHost for ElasticMS Admin on [ ${NGINX_SERVER_NAME} ]."

    for FILE in $(find /opt/bin/container-entrypoint.d/elasticms.d/nginx.d -iname \*.sh | sort)
    do
        SYMFONY_SCRIPT_NAME_NGINX_VARIABLE_NAME=${SYMFONY_SCRIPT_NAME_NGINX_VARIABLE_NAME} \
        source ${FILE}
    done

    rm "${ALIASES_TEMP_JSON_FILE}"

fi
