#!/usr/bin/env bash

# The target is the nginx public directory. This step used to name
# APACHE_ASSETS_DIR and APACHE_PUBLIC_DIR, which are empty in an nginx image: the
# log read "assets installation to  folder" and asset:install fell back to its
# own default, which happens to be the same directory.
log "INFO" "+ Running ElasticMS assets installation to ${NGINX_PUBLIC_DIR} for [ ${ELASTICMS_INSTANCE_NAME} ] WebSite Domain ..."

# Not fatal: the site still serves its pages, with the bundle assets missing.
if ! ${APP_BIN_DIR}/${ELASTICMS_INSTANCE_NAME} asset:install "${NGINX_PUBLIC_DIR}" --symlink --no-interaction --env=${APP_ENV}; then
    log "WARN" "! Something doesn't work with ElasticMS assets installation !"
fi
