#!/usr/bin/env bash

log "INFO" "+ Running Doctrine database migration (sync-metadata-storage) for [ ${ELASTICMS_INSTANCE_NAME} ] CMS Domain ..."

if [[ "$DB_DRIVER" =~ ^.*pgsql$ ]]; then
    if [[ "$DB_USER" =~ ^.*_(chg)$ ]]; then
        log "INFO" "+ Startup DBCR() ..."
        psql postgresql://${DB_USER}:$(urlencode.py $DB_PASSWORD)@${DB_HOST//,/:${DB_PORT},}:${DB_PORT}/${DB_NAME}?connect_timeout=${DB_CONNECTION_TIMEOUT:-30} -c 'select * from start_dbcr();'
    fi
fi

${APP_BIN_DIR}/${ELASTICMS_INSTANCE_NAME} doctrine:migrations:sync-metadata-storage --no-interaction --env=${APP_ENV}

if [ $? -ne 0 ]; then
    log "ERROR" "! Something doesn't work with doctrine sync metadata  !"
fi

log "INFO" "+ Running Doctrine database migration for [ ${ELASTICMS_INSTANCE_NAME} ] CMS Domain ..."

${APP_BIN_DIR}/${ELASTICMS_INSTANCE_NAME} doctrine:migrations:migrate --no-interaction --env=${APP_ENV}

if [ $? -ne 0 ]; then
    log "ERROR" "! Something doesn't work with Doctrine database migration !"
fi

if [[ "$DB_DRIVER" =~ ^.*pgsql$ ]]; then
    if [[ "$DB_USER" =~ ^.*_(chg)$ ]]; then
        log "INFO" "+ Stop DBCR() ..."
        psql postgresql://${DB_USER}:$(urlencode.py $DB_PASSWORD)@${DB_HOST//,/:${DB_PORT},}:${DB_PORT}/${DB_NAME}?connect_timeout=${DB_CONNECTION_TIMEOUT:-30} -c 'select * from stop_dbcr();'
    fi
fi
