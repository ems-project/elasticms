# syntax=docker/dockerfile:1.15
FROM docker.io/smalswebtech/base-php:8.5-cli AS base

WORKDIR /app

#
# CA bundle
#

# The image's own trust store plus an optional custom CA (the ca_bundle build
# secret), mounted for the steps that download so a build works behind a
# TLS-inspecting corporate proxy. It is never copied into an image: the corporate
# CA does not end up in a published layer.
FROM base AS ca-bundle

USER root

# CA_BUNDLE_SHA is the checksum of the custom CA, passed by the caller. A secret
# mount does not take part in the build cache key, so without this an image built
# once without the CA -- or with a different one -- silently reuses a stale
# ca-bundle layer and the CA never reaches the bundle. Referencing the ARG in the
# RUN ties this layer's cache to the CA's content: a changed or newly added CA
# rebuilds it, and an empty value (no CA) keeps a stable cache.
ARG CA_BUNDLE_SHA=""
RUN --mount=type=secret,id=ca_bundle,required=false \
    : "${CA_BUNDLE_SHA}" ; \
    cp /etc/ssl/certs/ca-certificates.crt /ca-bundle.pem ; \
    if [ -f /run/secrets/ca_bundle ]; then cat /run/secrets/ca_bundle >> /ca-bundle.pem ; fi

#
# BUILD (mdw)
#

FROM docker.io/smalswebtech/base-php:8.5-cli-dev AS mdw-builder

ARG TIKA_VERSION_ARG

USER root

ENV TIKA_VERSION=${TIKA_VERSION_ARG:-2.9.2} 

ENV NEAREST_TIKA_URL="https://www.apache.org/dyn/closer.cgi/tika/${TIKA_VERSION}/tika-app-${TIKA_VERSION}.jar?filename=tika/${TIKA_VERSION}/tika-app-${TIKA_VERSION}.jar&action=download"
ENV ARCHIVE_TIKA_URL="https://archive.apache.org/dist/tika/${TIKA_VERSION}/tika-app-${TIKA_VERSION}.jar"
ENV DEFAULT_TIKA_ASC_URL="https://downloads.apache.org/tika/${TIKA_VERSION}/tika-app-${TIKA_VERSION}.jar.asc"
ENV ARCHIVE_TIKA_ASC_URL="https://archive.apache.org/dist/tika/${TIKA_VERSION}/tika-app-${TIKA_VERSION}.jar.asc"

RUN --mount=type=bind,from=ca-bundle,source=/ca-bundle.pem,target=/etc/ssl/certs/ca-certificates.crt \
    mkdir -p /rootfs/opt/bin ; \
    apk add --update gnupg wget ; \
    wget -t 10 --max-redirect 1 --retry-connrefused -qO- https://downloads.apache.org/tika/KEYS | gpg --import ; \
    wget -t 10 --max-redirect 1 --retry-connrefused $NEAREST_TIKA_URL -O /tmp/tika-app-${TIKA_VERSION}.jar || rm /tmp/tika-app-${TIKA_VERSION}.jar ; \
    sh -c "[ -f /tmp/tika-app-${TIKA_VERSION}.jar ]" || wget $ARCHIVE_TIKA_URL -O /tmp/tika-app-${TIKA_VERSION}.jar || rm /tmp/tika-app-${TIKA_VERSION}.jar ; \
    sh -c "[ -f /tmp/tika-app-${TIKA_VERSION}.jar ]" || exit 1 ; \
    wget -t 10 --max-redirect 1 --retry-connrefused $DEFAULT_TIKA_ASC_URL -O /tmp/tika-app-${TIKA_VERSION}.jar.asc  || rm /tmp/tika-app-${TIKA_VERSION}.jar.asc ; \
    sh -c "[ -f /tmp/tika-app-${TIKA_VERSION}.jar.asc ]" || wget $ARCHIVE_TIKA_ASC_URL -O /tmp/tika-app-${TIKA_VERSION}.jar.asc || rm /tmp/tika-app-${TIKA_VERSION}.jar.asc ; \
    sh -c "[ -f /tmp/tika-app-${TIKA_VERSION}.jar.asc ]" || exit 1 ; \
    gpg --verify /tmp/tika-app-${TIKA_VERSION}.jar.asc /tmp/tika-app-${TIKA_VERSION}.jar ; \
    cp /tmp/tika-app-${TIKA_VERSION}.jar /rootfs/opt/bin/tika-app.jar

#
# BUILD (app)
#

FROM docker.io/smalswebtech/base-php:8.5-cli-dev AS app-builder

USER root

COPY . /tmpfs

RUN mv /tmpfs/.docker /bootstrap \
    && mv /tmpfs /app/src/elasticms

#
# PRD
#

FROM base AS prd

ARG PHP_EXT_SQLSRV_VERSION_ARG

ENV NODE_ENV=production

ENV APP_DISABLE_DOTENV=true \
    PHP_OPENTELEMETRY_ENABLED=true \
    PHP_BYPASS_INI_DEFAULT_VALUES=true \
    PUPPETEER_EXECUTABLE_PATH=/usr/bin/chromium-browser \
    PUPPETEER_SKIP_CHROMIUM_DOWNLOAD=true \
    TMPDIR=/app/tmp 

#ENV PHP_EXT_INSTALL_CUSTOM="sqlsrv-${PHP_EXT_SQLSRV_VERSION_ARG} pdo_sqlsrv-${PHP_EXT_SQLSRV_VERSION_ARG} pdo_odbc"
ENV PHP_EXT_INSTALL_CUSTOM="pdo_odbc"

ENV PHP_PDO_SQLSRV_ENABLED=false \
    PHP_SQLSRV_ENABLED=false

USER root

COPY --from=app-builder --chmod=775 --chown=1001:0 /app /app
COPY --from=app-builder --chmod=775 --chown=1001:0 /bootstrap /opt
COPY --from=mdw-builder --chmod=775 --chown=1001:0 /rootfs/opt /opt

# The CA bundle is mounted beside the trust store, not over it, and handed to
# apk, curl and wget through SSL_CERT_FILE / CURL_CA_BUNDLE for this step only.
# Over it, installing openjdk17-jre ran the java-cacerts hook, whose
# `trust extract` read the mounted bundle and baked the corporate CA into
# /etc/ssl/certs/java/cacerts of the published image.
RUN --mount=type=bind,from=ca-bundle,source=/ca-bundle.pem,target=/run/ca-bundle.pem \
    export SSL_CERT_FILE=/run/ca-bundle.pem CURL_CA_BUNDLE=/run/ca-bundle.pem && \
    install-php-extensions ${PHP_EXT_INSTALL_CUSTOM} && \
    apk add --update --no-cache chromium \
                                nss \
                                freetype \
                                harfbuzz \
                                ca-certificates \
                                ttf-freefont \
                                openjdk17-jre \
                                tesseract-ocr \
                                msttcorefonts-installer \
                                ttf-dejavu \
                                fontconfig \
                                supervisor \
                                supercronic && \
    update-ms-fonts && \
    fc-cache -f -v && \
    \
    mkdir -p /home/default/Downloads && \
    chown -R 1001:0 /home/default/Downloads && \
    chmod -R ug+rw /home/default/Downloads 

USER 1001

ENTRYPOINT ["dumb-init","--","/opt/bin/container-entrypoint"]

CMD ["/bin/sh", "-ec", "while :; do echo '.'; sleep 5 ; done"]

#
# DEV
#

FROM docker.io/smalswebtech/base-php:8.5-cli-dev AS dev

ARG PHP_EXT_SQLSRV_VERSION_ARG

ENV NODE_ENV=development

ENV APP_DISABLE_DOTENV=true \
    PHP_OPENTELEMETRY_ENABLED=true \
    PHP_BYPASS_INI_DEFAULT_VALUES=true \
    PUPPETEER_EXECUTABLE_PATH=/usr/bin/chromium-browser \
    PUPPETEER_SKIP_CHROMIUM_DOWNLOAD=true \
    TMPDIR=/app/tmp 

#ENV PHP_EXT_INSTALL_CUSTOM="sqlsrv-${PHP_EXT_SQLSRV_VERSION_ARG} pdo_sqlsrv-${PHP_EXT_SQLSRV_VERSION_ARG} pdo_odbc"
ENV PHP_EXT_INSTALL_CUSTOM="pdo_odbc"

ENV PHP_PDO_SQLSRV_ENABLED=false \
    PHP_SQLSRV_ENABLED=false

USER root

COPY --from=app-builder --chmod=775 --chown=1001:0 /app /app
COPY --from=app-builder --chmod=775 --chown=1001:0 /bootstrap /opt
COPY --from=mdw-builder --chmod=775 --chown=1001:0 /rootfs/opt /opt

# The CA bundle is mounted beside the trust store, not over it, and handed to
# apk, curl and wget through SSL_CERT_FILE / CURL_CA_BUNDLE for this step only.
# Over it, installing openjdk17-jre ran the java-cacerts hook, whose
# `trust extract` read the mounted bundle and baked the corporate CA into
# /etc/ssl/certs/java/cacerts of the published image.
RUN --mount=type=bind,from=ca-bundle,source=/ca-bundle.pem,target=/run/ca-bundle.pem \
    export SSL_CERT_FILE=/run/ca-bundle.pem CURL_CA_BUNDLE=/run/ca-bundle.pem && \
    install-php-extensions ${PHP_EXT_INSTALL_CUSTOM} && \
    apk add --update --no-cache chromium \
                                nss \
                                freetype \
                                harfbuzz \
                                ca-certificates \
                                ttf-freefont \
                                openjdk17-jre \
                                tesseract-ocr \
                                msttcorefonts-installer \
                                ttf-dejavu \
                                fontconfig \
                                supervisor \
                                supercronic && \
    update-ms-fonts && \
    fc-cache -f -v && \
    \
    mkdir -p /home/default/Downloads && \
    chown -R 1001:0 /home/default/Downloads && \
    chmod -R ug+rw /home/default/Downloads 

USER 1001

ENTRYPOINT ["dumb-init","--","/opt/bin/container-entrypoint"]

CMD ["/bin/sh", "-ec", "while :; do echo '.'; sleep 5 ; done"]
