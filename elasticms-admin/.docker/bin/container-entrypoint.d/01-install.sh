#!/usr/bin/env bash
set -eo pipefail

log "INFO" " ███████╗██╗      █████╗ ███████╗████████╗██╗ ██████╗███╗   ███╗███████╗ "
log "INFO" " ██╔════╝██║     ██╔══██╗██╔════╝╚══██╔══╝██║██╔════╝████╗ ████║██╔════╝ "
log "INFO" " █████╗  ██║     ███████║███████╗   ██║   ██║██║     ██╔████╔██║███████╗ "
log "INFO" " ██╔══╝  ██║     ██╔══██║╚════██║   ██║   ██║██║     ██║╚██╔╝██║╚════██║ "
log "INFO" " ███████╗███████╗██║  ██║███████║   ██║   ██║╚██████╗██║ ╚═╝ ██║███████║ "
log "INFO" " ╚══════╝╚══════╝╚═╝  ╚═╝╚══════╝   ╚═╝   ╚═╝ ╚═════╝╚═╝     ╚═╝╚══════╝ "
log "INFO" "                                                                         "
log "INFO" "                    ( ElasticMS Admin Image )                            "
log "INFO" "                                                                         "
log "INFO" "- Install ElasticMS Admin Configuration files"

mkdir -p ${APP_CONFIG_DIR}

if [ ! -z "$AWS_S3_CONFIG_BUCKET_NAME" ]; then

    export AWS_S3_CONFIG_BUCKET_NAME=${AWS_S3_CONFIG_BUCKET_NAME#s3://}

    list=(`aws s3 ls ${AWS_S3_CONFIG_BUCKET_NAME%/}/ ${AWS_CLI_EXTRA_ARGS} | awk '{print $4}'`)

    for config in ${list[@]};
    do
        name=${config%.*}
        log "INFO" "+ Install s3://${AWS_S3_CONFIG_BUCKET_NAME%/}/$config to ${APP_CONFIG_DIR}/$name"
        aws s3 cp s3://${AWS_S3_CONFIG_BUCKET_NAME%/}/$config ${AWS_CLI_EXTRA_ARGS} - | envsubst > ${APP_CONFIG_DIR}/$name
    done

elif [ "$(ls -A /opt/config/elasticms 2>/dev/null)" ]; then

    for file in /opt/config/elasticms/*; do
        name=$(basename "$file" .${file##*.})
        log "INFO" "+ Install $file to ${APP_CONFIG_DIR}/$name"
        envsubst < $file > ${APP_CONFIG_DIR}/$name
    done

else

    log "INFO" "+ Install default to ${APP_CONFIG_DIR}/default"

    # One NAME='value' line per exported variable: the format every reader of an
    # instance file expects. It is sourced as shell (elasticms.d/01-core.sh, the
    # sbin scripts) and parsed line by line with the surrounding quotes stripped
    # (10-php.sh, nginx.d). `env | envsubst` wrote the values bare, so a value
    # holding a space -- the image's own NGINX_STATIC_LOCATION_CACHE_CONTROL
    # default, "public, max-age=2592000" -- ran as a command when sourced and the
    # boot failed with exit 127. envsubst still expands each value, as before.
    : > ${APP_CONFIG_DIR}/default

    while IFS= read -r -d '' VAR; do

        NAME="${VAR%%=*}"
        VALUE="$(printf '%s' "${VAR#*=}" | envsubst)"

        [[ "${NAME}" =~ ^[A-Za-z_][A-Za-z0-9_]*$ ]] || continue

        if [[ "${VALUE}" != *$'\n'* && "${VALUE}" != *\'* ]]; then
            printf "%s='%s'\n" "${NAME}" "${VALUE}" >> ${APP_CONFIG_DIR}/default
        elif [[ "${VALUE}" != *[$'\n'\"\$\`\\]* ]]; then
            printf '%s="%s"\n' "${NAME}" "${VALUE}" >> ${APP_CONFIG_DIR}/default
        else
            log "WARN" "! ${NAME} cannot be written unambiguously to the default instance file; skipped. Set it in a configuration file instead."
        fi

    done < <(env -0)

fi
