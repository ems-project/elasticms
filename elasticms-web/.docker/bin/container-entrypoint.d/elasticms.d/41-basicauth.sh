#!/usr/bin/env bash

# Basic auth for one website instance, with base-php's nginx settings:
# NGINX_BASIC_AUTH_ENABLED, NGINX_BASIC_AUTH_USERNAME, NGINX_BASIC_AUTH_PASSWORD,
# set in the instance file or in the container environment. Each instance gets
# its own password file, so two websites of one container can have different
# credentials; the vhost template points at it.
#
# The Apache image protected a path with APACHE_PROTECTED_URL and HTPASSWD_*.
# Nothing reads them any more, so a configuration still carrying them would
# serve its website unprotected: refuse to start instead, and say what to use.

if [[ -n ${APACHE_PROTECTED_URL:-} ]]; then
    log "ERROR" "! APACHE_PROTECTED_URL is no longer supported: the website runs on nginx."
    log "ERROR" "! Protect [ ${ELASTICMS_INSTANCE_NAME} ] with NGINX_BASIC_AUTH_ENABLED=true, NGINX_BASIC_AUTH_USERNAME and NGINX_BASIC_AUTH_PASSWORD."
    exit 1
fi

if [[ ${NGINX_BASIC_AUTH_ENABLED,,} == true ]]; then

    log "INFO" "+ Configure Basic Authentication for [ ${ELASTICMS_INSTANCE_NAME} ]."

    # base-php's default password is public; a website protected with it is not.
    if [[ -z ${NGINX_BASIC_AUTH_USERNAME:-} || -z ${NGINX_BASIC_AUTH_PASSWORD:-} || ${NGINX_BASIC_AUTH_PASSWORD} == "${NGINX_BASIC_AUTH_PASSWORD_WCMTECH_DEFAULT:-pa55w0rd}" ]]; then
        log "ERROR" "! [ ${ELASTICMS_INSTANCE_NAME} ] enables basic auth without its own NGINX_BASIC_AUTH_USERNAME and NGINX_BASIC_AUTH_PASSWORD."
        exit 1
    fi

    mkdir -p "$(dirname "${ELASTICMS_BASIC_AUTH_FILE}")"

    # The password goes through stdin, not the command line, where ps shows it.
    printf '%s\n' "${NGINX_BASIC_AUTH_PASSWORD}" |
        htpasswd -iBc "${ELASTICMS_BASIC_AUTH_FILE}" "${NGINX_BASIC_AUTH_USERNAME}"
    chmod 600 "${ELASTICMS_BASIC_AUTH_FILE}"

fi
