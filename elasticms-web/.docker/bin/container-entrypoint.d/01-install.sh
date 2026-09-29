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

# Expands ${NAME} references to the environment in an instance configuration,
# stdin to stdout. It replaces envsubst, which also expanded a bare $NAME and has
# no escape: a secret holding a $ -- "pa$word" -- lost what followed it, silently.
# Only the braced form is a reference now; any other $ is kept as written. $${
# writes a literal ${ for the rare value that needs one. An unset NAME expands to
# an empty string, as it did with envsubst.
expand_references() {
    python3 -c '
import os, re, sys
sys.stdout.write(re.sub(
    r"\$\$\{|\$\{([A-Za-z_][A-Za-z0-9_]*)\}",
    lambda m: "${" if m.group(1) is None else os.environ.get(m.group(1), ""),
    sys.stdin.read()))
'
}

if [ ! -z "$AWS_S3_CONFIG_BUCKET_NAME" ]; then

    export AWS_S3_CONFIG_BUCKET_NAME=${AWS_S3_CONFIG_BUCKET_NAME#s3://}

    list=(`aws s3 ls ${AWS_S3_CONFIG_BUCKET_NAME%/}/ ${AWS_CLI_EXTRA_ARGS} | awk '{print $4}'`)

    for config in ${list[@]};
    do
        name=${config%.*}
        log "INFO" "+ Install s3://${AWS_S3_CONFIG_BUCKET_NAME%/}/$config to ${APP_CONFIG_DIR}/$name"
        aws s3 cp s3://${AWS_S3_CONFIG_BUCKET_NAME%/}/$config ${AWS_CLI_EXTRA_ARGS} - | expand_references > ${APP_CONFIG_DIR}/$name
    done

elif [ "$(ls -A /opt/config/elasticms 2>/dev/null)" ]; then

    for file in /opt/config/elasticms/*; do
        name=$(basename "$file" .${file##*.})
        log "INFO" "+ Install $file to ${APP_CONFIG_DIR}/$name"
        expand_references < $file > ${APP_CONFIG_DIR}/$name
    done

else

    log "INFO" "+ Install default to ${APP_CONFIG_DIR}/default"

    # One NAME='value' line per exported variable: the format every reader of an
    # instance file expects. It is sourced as shell (elasticms.d/01-core.sh, the
    # sbin scripts) and parsed line by line with the surrounding quotes stripped
    # (10-php.sh, nginx.d). `env | envsubst` wrote the values bare, so a value
    # holding a space -- the image's own NGINX_STATIC_LOCATION_CACHE_CONTROL
    # default, "public, max-age=2592000" -- ran as a command when sourced and the
    # boot failed with exit 127. ${NAME} references in a value are still expanded.
    : > ${APP_CONFIG_DIR}/default

    # The whole environment went into this file, and the sbin wrappers -- the
    # console, jobs, messenger -- source it: every console process got base-php's
    # php.ini and pool settings and the AWS credentials back, after base-php had
    # removed them from the application's environment. Leave out what base-php
    # cleans (CLEANUP_VAR_LIST), except the settings this image declares itself
    # (entrypoint.d), which the wrappers read at runtime (CLI_PHP_MEMORY_LIMIT).
    IMAGE_VARS=" $(sed -nE 's/^[[:space:]]*([A-Za-z_][A-Za-z0-9_]*)_WCMTECH_DEFAULT=.*/\1/p' /opt/bin/container-entrypoint.d/entrypoint.d/*.sh | tr '\n' ' ')"

    while IFS= read -r -d '' VAR; do

        NAME="${VAR%%=*}"
        VALUE="$(printf '%s' "${VAR#*=}" | expand_references)"

        [[ "${NAME}" =~ ^[A-Za-z_][A-Za-z0-9_]*$ ]] || continue
        [[ ":${CLEANUP_VAR_LIST:-}:" == *":${NAME}:"* && "${IMAGE_VARS}" != *" ${NAME} "* ]] && continue

        if [[ "${VALUE}" != *$'\n'* && "${VALUE}" != *\'* ]]; then
            printf "%s='%s'\n" "${NAME}" "${VALUE}" >> ${APP_CONFIG_DIR}/default
        elif [[ "${VALUE}" != *[$'\n'\"\$\`\\]* ]]; then
            printf '%s="%s"\n' "${NAME}" "${VALUE}" >> ${APP_CONFIG_DIR}/default
        else
            log "WARN" "! ${NAME} cannot be written unambiguously to the default instance file; skipped. Set it in a configuration file instead."
        fi

    done < <(env -0)

fi
