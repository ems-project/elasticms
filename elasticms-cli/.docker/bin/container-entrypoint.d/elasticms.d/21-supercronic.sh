#!/usr/bin/env bash

OUTDIR="/opt/etc/crontabs"

for dir in $OUTDIR; do
    mkdir -p "$dir"
done

log "INFO" "Configure Supercronic"

if [[ -f /opt/etc/crontabs/elasticms ]]
then

    if rm /opt/etc/crontabs/elasticms; then
        apply-template /opt/config/elasticms/elasticms.crontab.tmpl /opt/etc/crontabs/elasticms
    else
        log "WARN" "- Supercronic crontab file exists and will be used"
    fi

else

    log "INFO" "- Writing Supercronic crontab file"
    apply-template /opt/config/elasticms/elasticms.crontab.tmpl /opt/etc/crontabs/elasticms

fi

if [[ -f /opt/etc/supervisor.d/supercronic.ini ]]
then

    if rm /opt/etc/supervisor.d/supercronic.ini; then
        apply-template /opt/config/supervisor.d/supercronic.ini.tmpl /opt/etc/supervisor.d/supercronic.ini
    else
        log "WARN" "- Supervisor config file exists and will be used"
    fi

else

    log "INFO" "- Writing Supervisor config file for Supercronic usage"
    apply-template /opt/config/supervisor.d/supercronic.ini.tmpl /opt/etc/supervisor.d/supercronic.ini

fi

true
