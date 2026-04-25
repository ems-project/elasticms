#!/usr/bin/env bash
set -eo pipefail

log "INFO" " ███████╗██╗      █████╗ ███████╗████████╗██╗ ██████╗███╗   ███╗███████╗ "
log "INFO" " ██╔════╝██║     ██╔══██╗██╔════╝╚══██╔══╝██║██╔════╝████╗ ████║██╔════╝ "
log "INFO" " █████╗  ██║     ███████║███████╗   ██║   ██║██║     ██╔████╔██║███████╗ "
log "INFO" " ██╔══╝  ██║     ██╔══██║╚════██║   ██║   ██║██║     ██║╚██╔╝██║╚════██║ "
log "INFO" " ███████╗███████╗██║  ██║███████║   ██║   ██║╚██████╗██║ ╚═╝ ██║███████║ "
log "INFO" " ╚══════╝╚══════╝╚═╝  ╚═╝╚══════╝   ╚═╝   ╚═╝ ╚═════╝╚═╝     ╚═╝╚══════╝ "
log "INFO" "                                                                         "
log "INFO" "                    ( ElasticMS WebSite Image )                          "
log "INFO" "                                                                         "
log "INFO" "- Install ElasticMS WebSite Configuration files"

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

elif [ "$(ls -A /opt/config/elasticms)" ]; then

    for file in /opt/config/elasticms/*; do
        name=$(basename "$file" .${file##*.})
        log "INFO" "+ Install $file to ${APP_CONFIG_DIR}/$name"
        envsubst < $file > ${APP_CONFIG_DIR}/$name
    done

else

    log "INFO" "+ Install default to ${APP_CONFIG_DIR}/default"
    env | envsubst > ${APP_CONFIG_DIR}/default

fi
