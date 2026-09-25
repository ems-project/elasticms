#!/usr/bin/env bash

if [[ -n ${APACHE_PROTECTED_URL} ]]; then

    log "INFO" "+ Configure Basic Authentification on [ ${APACHE_PROTECTED_URL} ]."

    if ! [ -w ${HTPASSWD_FILE} ]; then

        # Fatal: starting without it would serve the protected URL openly.
        htpasswd -bc ${HTPASSWD_FILE} ${HTPASSWD_USERNAME} ${HTPASSWD_PASSWORD} || {
            ELASTICMS_HTPASSWD_STATUS=$?
            log "ERROR" "! Something was wrong when we create .htpasswd file !"
            exit "$ELASTICMS_HTPASSWD_STATUS"
        }

    else
        log "WARN" "! .htpasswd file already exist.  We use it to protect !"
    fi

fi
