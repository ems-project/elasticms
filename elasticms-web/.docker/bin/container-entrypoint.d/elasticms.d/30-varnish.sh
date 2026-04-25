#!/usr/bin/env bash

log "INFO" "| Configure Varnish VCL file"

if [[ ! -z ${VARNISH_ENABLED} ]] && [[ ${VARNISH_ENABLED,,} = true ]]; then

    if [[ -f ${VARNISH_VCL_CONF} ]]; then

        log "INFO" "+ Varnish VCL file [ ${VARNISH_VCL_CONF} ] already exist.  Using this VCL with Varnish."

    else

        log "INFO" "+ Varnish VCL file [ ${VARNISH_VCL_CONF} ] not exist.  Generation of the VCL dynamically."

        gomplate -f /opt/config/varnish/default.vcl.gtpl \
                 -o ${VARNISH_VCL_CONF}

  fi

fi
