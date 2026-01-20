# syntax=docker/dockerfile:1.15
FROM docker.io/smalswebtech/base-php:8.4-cli-dev AS builder

ENV PHP_INI_SCAN_DIR="/usr/local/etc/php/conf.d" \
    APP_DISABLE_DOTENV=true \
    EMSCO_TIKA_SERVER=http://null \
    MAILER_URL=smtp://null

USER root

COPY . /tmpfs

RUN mv /tmpfs/.docker /bootstrap \
    && mv /tmpfs /app/src/elasticms \
    && COMPOSER_MEMORY_LIMIT=-1 composer -vvv install --no-interaction --no-suggest --no-scripts --working-dir /app/src/elasticms -o \
    && npm install --prefix /app/src/elasticms/vendor/elasticms/admin-ui-bundle/assets \
    && npm --prefix /app/src/elasticms/vendor/elasticms/admin-ui-bundle/assets run build \
    && rm -rf /app/src/elasticms/vendor/elasticms/admin-ui-bundle/assets/node_modules \
    \
    && php /app/src/elasticms/bin/console assets:install /app/src/elasticms/public --symlink --no-interaction --env=prod \
    && rm -rf /app/src/elasticms/var/*

#
# Prd
#

FROM docker.io/smalswebtech/base-php:8.4-apache AS prd

USER root

COPY --from=builder --chmod=775 --chown=1001:0 /bootstrap/ /opt/
COPY --from=builder --chmod=775 --chown=1001:0 /app/ /app/

ENV PHP_BYPASS_INI_DEFAULT_VALUES=true \
    APP_DISABLE_DOTENV=true \
    EMS_METRIC_PORT="9090"

USER 1001

EXPOSE 9090/tcp

HEALTHCHECK --start-period=1s --interval=1m --timeout=2s --retries=5 \
        CMD curl --fail --header "Host: default.localhost" http://localhost:9000/index.php || exit 1

#
# Dev
#

FROM docker.io/smalswebtech/base-php:8.4-apache-dev AS dev

USER root

COPY --from=builder --chmod=775 --chown=1001:0 /bootstrap/ /opt/
COPY --from=builder --chmod=775 --chown=1001:0 /app/ /app/

ENV PHP_BYPASS_INI_DEFAULT_VALUES=true \
    APP_DISABLE_DOTENV=true \
    EMS_METRIC_PORT="9090"

USER 1001

EXPOSE 9090/tcp

HEALTHCHECK --start-period=1s --interval=1m --timeout=2s --retries=5 \
        CMD curl --fail --header "Host: default.localhost" http://localhost:9000/index.php || exit 1