#!/usr/bin/make -f

.DEFAULT_GOAL := help
.PHONY: help

help: ## Show help for each of the Makefile recipes.
	@grep -E '(^\S*:.*?##.*$$)|(^##)' Makefile | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

# —— Environment ——————————————————————————————————————————————————————————————————————————————————————————————————————

CURRENT_UID                 := $(shell id -u)
CURRENT_GID                 := $(shell id -g)

CURRENT_USERNAME            := $(shell id -u -n)
CURRENT_HOMEDIR             := $${HOME}
CURRENT_DIR                 := $(shell pwd)

BUILDER_WORKING_DIR          = /app/src/elasticms
BUILDER_DOCKER_IMAGE_NAME   ?= docker.io/smalswebtech/base-php:8.5-cli-dev

COMPOSER_INSTALL_CMDLINE     = "composer -vvv install --no-interaction --no-suggest --no-scripts -o"
COMPOSER_UPDATE_CMDLINE      = "composer -vvv update --no-interaction --no-suggest --no-scripts -o"
COMPOSER_DIAGNOSE_CMDLINE    = "composer -vvv diagnose --no-interaction --no-scripts"
COMPOSER_SELF_UPDATE_CMDLINE = "composer -vvv self-update --no-interaction"

NPM_CONFIG_CMDLINE           = "npm config set cafile=/etc/ssl/certs/ca-certificates.crt && npm config set strict-ssl=false"
NPM_INSTALL_CMDLINE          = "npm install --prefix /app/src/elasticms/vendor/elasticms/admin-ui-bundle/assets"
NPM_RUN_BUILD_CMDLINE        = "npm --prefix /app/src/elasticms/vendor/elasticms/admin-ui-bundle/assets run build"

DOCKER_IMAGE_NAME           ?= docker.io/elasticms/admin

DOCKER_PLATFORM             ?= linux/amd64
DOCKER_BUILDER              ?= default
DOCKER_OUTPUT               ?= type=image

# —— ElasticMS build ——————————————————————————————————————————————————————————————————————————————————————————————————

build-app: ## Build ElasticMS Symfony application
	@$(MAKE) composer-diagnose
	@$(MAKE) composer-install
	@$(MAKE) npm-configure
	@$(MAKE) npm-install
	@$(MAKE) npm-build
	rm -rf ./vendor/elasticms/admin-ui-bundle/assets/node_modules

build-image: ## Build ElasticMS Docker images
	@$(MAKE) build-app
	@$(MAKE) docker-build/prd
	@$(MAKE) docker-build/dev

bake-image: ## bake-image DOCKER_PLATFORM="linux/amd64,linux/arm64" DOCKER_BUILDER="cloud-remote" DOCKER_OUTPUT="type=registry" DOCKER_IMAGE_NAME="elasticms/admin"
	@$(MAKE) build-app
	@$(MAKE) docker-bake/prd
	@$(MAKE) docker-bake/dev

# —— Composer —————————————————————————————————————————————————————————————————————————————————————————————————————————

composer-diagnose:
	@echo "\n-- Running Composer diagnose --\n"
	@docker run \
		--env PHP_BYPASS_INI_DEFAULT_VALUES=true \
		--env HOME=${CURRENT_HOMEDIR} \
		--env USER=${CURRENT_USERNAME} \
		--env COMPOSER_HOME=${CURRENT_HOMEDIR}/.composer \
		--env COMPOSER_ALLOW_SUPERUSER=1 \
		--env COMPOSER_PROCESS_TIMEOUT=900 \
		--env COMPOSER_MEMORY_LIMIT=-1 \
		--user root \
		--volume /etc/ssl/certs/ca-certificates.crt:/etc/ssl/certs/ca-certificates.crt \
		--volume ${CURRENT_HOMEDIR}:${CURRENT_HOMEDIR}:rw \
		--rm \
		${BUILDER_DOCKER_IMAGE_NAME} \
		bash -c ${COMPOSER_DIAGNOSE_CMDLINE}

composer-update:
	@echo "\n-- Running Composer update --\n"
	@docker run \
		--env PHP_BYPASS_INI_DEFAULT_VALUES=true \
		--env PHP_OPENTELEMETRY_ENABLED=true \
		--env HOME=${CURRENT_HOMEDIR} \
		--env USER=${CURRENT_USERNAME} \
		--env COMPOSER_ALLOW_SUPERUSER=1 \
		--env COMPOSER_PROCESS_TIMEOUT=900 \
		--env COMPOSER_MEMORY_LIMIT=-1 \
		--user ${CURRENT_UID}:${CURRENT_GID} \
		--volume /etc/ssl/certs/ca-certificates.crt:/etc/ssl/certs/ca-certificates.crt \
		--volume ${CURRENT_HOMEDIR}:${CURRENT_HOMEDIR}:rw \
		--volume ${CURRENT_DIR}:${BUILDER_WORKING_DIR}:rw \
		--workdir ${BUILDER_WORKING_DIR} \
		--rm \
		${BUILDER_DOCKER_IMAGE_NAME} \
		bash -c ${COMPOSER_UPDATE_CMDLINE}

composer-install:
	@echo "\n-- Running Composer install --\n"
	@docker run \
		--env PHP_BYPASS_INI_DEFAULT_VALUES=true \
		--env PHP_OPENTELEMETRY_ENABLED=true \
		--env HOME=${CURRENT_HOMEDIR} \
		--env USER=${CURRENT_USERNAME} \
		--env COMPOSER_ALLOW_SUPERUSER=1 \
		--env COMPOSER_PROCESS_TIMEOUT=900 \
		--env COMPOSER_MEMORY_LIMIT=-1 \
		--user ${CURRENT_UID}:${CURRENT_GID} \
		--volume /etc/ssl/certs/ca-certificates.crt:/etc/ssl/certs/ca-certificates.crt \
		--volume ${CURRENT_HOMEDIR}:${CURRENT_HOMEDIR}:rw \
		--volume ${CURRENT_DIR}:${BUILDER_WORKING_DIR}:rw \
		--workdir ${BUILDER_WORKING_DIR} \
		--rm \
		${BUILDER_DOCKER_IMAGE_NAME} \
		bash -c ${COMPOSER_INSTALL_CMDLINE}

composer-selfupdate:
	@echo "\n-- Running Composer Self-Update --\n"
	@docker run \
		--env PHP_BYPASS_INI_DEFAULT_VALUES=true \
		--env HOME=${CURRENT_HOMEDIR} \
		--env USER=${CURRENT_USERNAME} \
		--env COMPOSER_HOME=${CURRENT_HOMEDIR}/.composer \
		--env COMPOSER_ALLOW_SUPERUSER=1 \
		--env COMPOSER_PROCESS_TIMEOUT=900 \
		--env COMPOSER_MEMORY_LIMIT=-1 \
		--user ${CURRENT_UID}:0 \
		--volume /etc/ssl/certs/ca-certificates.crt:/etc/ssl/certs/ca-certificates.crt \
		--volume ${CURRENT_HOMEDIR}:${CURRENT_HOMEDIR}:rw \
		--rm \
		${BUILDER_DOCKER_IMAGE_NAME} \
		bash -c ${COMPOSER_SELF_UPDATE_CMDLINE}

# —— NPM ——————————————————————————————————————————————————————————————————————————————————————————————————————————————

npm-configure:
	@echo "\n-- Running NPM config --\n"
	@docker run \
		--env PHP_BYPASS_INI_DEFAULT_VALUES=true \
		--env HOME=${CURRENT_HOMEDIR} \
		--env USER=${CURRENT_USERNAME} \
		--user ${CURRENT_UID}:${CURRENT_GID} \
		--volume /etc/ssl/certs/ca-certificates.crt:/etc/ssl/certs/ca-certificates.crt \
		--volume ${CURRENT_HOMEDIR}:${CURRENT_HOMEDIR}:rw \
		--volume ${CURRENT_DIR}:${BUILDER_WORKING_DIR}:rw \
		--workdir ${BUILDER_WORKING_DIR} \
		--rm \
		${BUILDER_DOCKER_IMAGE_NAME} \
		bash -c ${NPM_CONFIG_CMDLINE}

npm-install:
	@echo "\n-- Running NPM install --\n"
	@docker run \
		--env PHP_BYPASS_INI_DEFAULT_VALUES=true \
		--env HOME=${CURRENT_HOMEDIR} \
		--env USER=${CURRENT_USERNAME} \
		--user ${CURRENT_UID}:${CURRENT_GID} \
		--volume /etc/ssl/certs/ca-certificates.crt:/etc/ssl/certs/ca-certificates.crt \
		--volume ${CURRENT_HOMEDIR}:${CURRENT_HOMEDIR}:rw \
		--volume ${CURRENT_DIR}:${BUILDER_WORKING_DIR}:rw \
		--workdir ${BUILDER_WORKING_DIR} \
		--rm \
		${BUILDER_DOCKER_IMAGE_NAME} \
		bash -c ${NPM_INSTALL_CMDLINE}

npm-build:
	@echo "\n-- Running NPM run build --\n"
	@docker run \
		--env PHP_BYPASS_INI_DEFAULT_VALUES=true \
		--env HOME=${CURRENT_HOMEDIR} \
		--env USER=${CURRENT_USERNAME} \
		--user ${CURRENT_UID}:${CURRENT_GID} \
		--volume /etc/ssl/certs/ca-certificates.crt:/etc/ssl/certs/ca-certificates.crt \
		--volume ${CURRENT_HOMEDIR}:${CURRENT_HOMEDIR}:rw \
		--volume ${CURRENT_DIR}:${BUILDER_WORKING_DIR}:rw \
		--workdir ${BUILDER_WORKING_DIR} \
		--rm \
		${BUILDER_DOCKER_IMAGE_NAME} \
		bash -c ${NPM_RUN_BUILD_CMDLINE}

# —— Docker build —————————————————————————————————————————————————————————————————————————————————————————————————————

docker-build/%: ## docker-build/(prd|dev)
	@echo "\n-- Running Docker buildx build --\n"
	@docker buildx build --progress=plain --no-cache \
		--target ${*} \
		--tag ${DOCKER_IMAGE_NAME} .

docker-bake/%: ## docker-bake/(prd|dev) DOCKER_PLATFORM="linux/amd64,linux/arm64" DOCKER_BUILDER="cloud-remote" DOCKER_OUTPUT="type=registry" DOCKER_IMAGE_NAME="elasticms/admin"
	@echo "\n-- Running Docker bake --\n"
	@docker bake --progress=plain --no-cache \
		--set *.platform=${DOCKER_PLATFORM} \
		--set *.output=${DOCKER_OUTPUT} \
		--builder ${DOCKER_BUILDER} \
		${*}