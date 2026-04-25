#!/usr/bin/env bash

if [[ -n ${APACHE_PROTECTED_URL} ]]; then

    log "INFO" "+ Configure Basic Authentification on [ ${APACHE_PROTECTED_URL} ]."

    if ! [ -w ${HTPASSWD_FILE} ]; then

        htpasswd -bc ${HTPASSWD_FILE} ${HTPASSWD_USERNAME} ${HTPASSWD_PASSWORD}

        if [ $? -ne 0 ]; then
          log "ERROR" "! Something was wrong when we create .htpasswd file !"
        fi

    else
        log "WARN" "! .htpasswd file already exist.  We use it to protect !"
    fi

fi
