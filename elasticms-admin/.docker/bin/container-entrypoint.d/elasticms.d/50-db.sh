#!/usr/bin/env bash

# A failing step here stops the container: the admin cannot serve with a
# database it cannot reach or a schema that is not migrated, and a container
# that exits is restarted by the orchestrator until the database is back.
# The error is logged first, so the cause is not left to the stack trace.

dbcr() {
    if [[ "$DB_DRIVER" =~ ^.*pgsql$ ]] && [[ "$DB_USER" =~ ^.*_(chg)$ ]]; then
        log "INFO" "+ ${1^} DBCR() ..."
        psql postgresql://${DB_USER}:$(urlencode.py $DB_PASSWORD)@${DB_HOST//,/:${DB_PORT},}:${DB_PORT}/${DB_NAME}?connect_timeout=${DB_CONNECTION_TIMEOUT:-30} -c "select * from ${1}_dbcr();"
    fi
}

log "INFO" "+ Running Doctrine database migration (sync-metadata-storage) for [ ${ELASTICMS_INSTANCE_NAME} ] CMS Domain ..."

dbcr start

${APP_BIN_DIR}/${ELASTICMS_INSTANCE_NAME} doctrine:migrations:sync-metadata-storage --no-interaction --env=${APP_ENV} || {
    ELASTICMS_DB_STATUS=$?
    log "ERROR" "! Something doesn't work with doctrine sync metadata !"
    dbcr stop || true
    exit "$ELASTICMS_DB_STATUS"
}

log "INFO" "+ Running Doctrine database migration for [ ${ELASTICMS_INSTANCE_NAME} ] CMS Domain ..."

${APP_BIN_DIR}/${ELASTICMS_INSTANCE_NAME} doctrine:migrations:migrate --no-interaction --env=${APP_ENV} || {
    ELASTICMS_DB_STATUS=$?
    log "ERROR" "! Something doesn't work with Doctrine database migration !"
    dbcr stop || true
    exit "$ELASTICMS_DB_STATUS"
}

dbcr stop
