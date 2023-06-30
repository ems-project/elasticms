#!/usr/bin/make -f

include .env
export $(grep -v '^#' .env | xargs)

ELK_VERSION ?= elk7
ENVIRONMENT ?= local
DOCKER_USER ?= $UID
PWD			?= $(shell pwd)

DEMO_DIR	?= /opt/src
RUN_ADMIN	?= docker compose exec -u ${DOCKER_USER}:0 admin-${ENVIRONMENT} ems-demo
RUN_WEB		?= docker compose exec -u ${DOCKER_USER}:0 web-${ENVIRONMENT} preview
RUN_PSQL	?= docker compose exec -u ${DOCKER_USER}:0 -e PGUSER=postgres -e PGPASSWORD=adminpg -T postgres psql
RUN_NPM		?= docker run -u ${DOCKER_USER} --rm -it -v ${DEMO_DIR}:/opt/src --workdir /opt/src elasticms/base-php:8.1-cli-dev npm

.DEFAULT_GOAL := help
.PHONY: help npm

help: # Show help for each of the Makefile recipes.
	@echo "EMS DEMO"
	@echo "---------------------------"
	@echo "ELK_VERSION: ${ELK_VERSION}"
	@echo "DOCKER_USER: ${DOCKER_USER}"
	@echo "ADMIN:		http://local.ems-demo-admin.localhost"
	@echo "ADMIN DEV:	http://local.ems-demo-admin-dev.localhost"
	@echo "WEB PREVIEW:	http://local.preview-ems-demo-web.localhost"
	@echo "WEB LIVE:	http://local.live-ems-demo-web.localhost"
	@echo "MINIO:		http://minio.localhost"
	@echo "KIBANA:		http://kibana.localhost"
	@echo "MAIL:		http://mailhog.localhost"
	@echo "---------------------------"
	@echo ""
	@echo "Usage: make [target]"
	@echo "Targets:"
	@grep -E '(^[a-zA-Z0-9_-]+:.*?##.*$$)|(^##)' Makefile | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

## —— Demo —————————————————————————————————————————————————————————————————————————————————————————————————————————————
init: ## init
	@$(MAKE) -s npm-install
	@$(MAKE) -s npm-prod
	@$(MAKE) -s db-setup
	@$(MAKE) -s _init-create-users
	@$(MAKE) -s web-login
	@$(MAKE) -s web-restore-configs
	@$(MAKE) -s admin-activate
	@$(MAKE) -s admin-rebuild
	@$(MAKE) -s _init-create-managed-aliases
	@$(MAKE) -s web-health-check
	@$(MAKE) -s _init-switch_default-env
	@$(MAKE) -s web-login
	@$(MAKE) -s web-upload-folder-assets
	@$(MAKE) -s web-local-upload
	@$(MAKE) -s web-local-push
	@$(MAKE) -s web-health-check
	@$(MAKE) -s web-restore-documents
	@$(MAKE) -s admin-align

npm/%:
	@$(RUN_NPM) $*
npm-install: ## npm install
	@$(MAKE) npm/install
npm-prod: ## npm run prod
	@$(MAKE) npm/"run prod"
npm-watch: ## npm run watch
	@$(MAKE) npm/"run watch"
npm-dev: ## npm run dev
	@$(MAKE) npm/"run dev"

## —— Admin ————————————————————————————————————————————————————————————————————————————————————————————————————————————
admin-rebuild: ## rebuild all environments
	@$(RUN_ADMIN) ems:environment:rebuild --all --no-debug
admin-activate: ## activate content types
	@$(RUN_ADMIN) ems:contenttype:activate --all --force --no-debug
admin-align: ## align preview with live
	@$(RUN_ADMIN) ems:environment:align preview live --force --no-debug

## —— Web ——————————————————————————————————————————————————————————————————————————————————————————————————————————————
web-login: ## web login (ems & emsch)
	@$(RUN_WEB) emsch:local:login demo demo
	@$(RUN_WEB) ems:admin:login --username=demo --password=demo
web-local-status: ## web local status
	@$(RUN_WEB) emsch:local:status
web-local-push: ## web local push
	@$(RUN_WEB) emsch:local:push --force
web-local-pull: ## web local pull
	@$(RUN_WEB) emsch:local:pull
web-local-upload: ## web local upload (bundle.zip)
    ifeq (${DEMO_DIR},/opt/src)
		@$(RUN_WEB) emsch:local:upload --filename=/opt/src/local/skeleton/template/asset_hash.twig
    else
		@$(RUN_WEB) emsch:local:upload ../../demo/dist/ --filename=${DEMO_DIR}/skeleton/template/asset_hash.twig
    endif
web-backup-configs:
    ifeq (${DEMO_DIR},/opt/src)
		@$(RUN_WEB) ems:admin:backup --configs --export
    else
		@$(RUN_WEB) ems:admin:backup --configs-folder=${DEMO_DIR}/configs/admin --configs --export
    endif
web-backup-documents:
    ifeq (${DEMO_DIR},/opt/src)
		@$(RUN_WEB) ems:admin:backup --configs --export
    else
		@$(RUN_WEB) ems:admin:backup --documents-folder=${DEMO_DIR}/configs/document --documents --export
    endif
web-restore-configs:
    ifeq (${DEMO_DIR},/opt/src)
		@$(RUN_WEB) ems:admin:restore --configs --force
    else
		@$(RUN_WEB) ems:admin:restore --configs-folder=${DEMO_DIR}/configs/admin --configs --force
    endif
web-restore-documents:
    ifeq (${DEMO_DIR},/opt/src)
		@$(RUN_WEB) ems:admin:restore --documents --force
    else
		@$(RUN_WEB) ems:admin:restore --documents-folder=${DEMO_DIR}/configs/document --documents --force
    endif
web-upload-folder-assets: ## web upload folder assets
    ifeq (${DEMO_DIR},/opt/src)
		@$(RUN_WEB) emsch:local:folder-upload /opt/src/admin/assets
    else
		@$(RUN_WEB) emsch:local:folder-upload ${DEMO_DIR}/configs/admin/assets
    endif
web-health-check:
	@$(RUN_WEB) emsch:health-check -g --no-debug

## —— Database —————————————————————————————————————————————————————————————————————————————————————————————————————————
db-setup: ## setup fresh demo (drops database)
	@$(MAKE) -s _db-drop
	@$(MAKE) -s _db-create
	@$(MAKE) -s db-migrate
db-migrate: ## run doctrine migrations
	@$(RUN_ADMIN) doctrine:migrations:migrate --no-interaction
_db-drop:
	@$(RUN_PSQL) -c "DROP DATABASE IF EXISTS ${DB_NAME};"
	@$(RUN_PSQL) -c "DROP USER IF EXISTS ${DB_USER};"
_db-create:
	@$(RUN_PSQL) -c "CREATE USER ${DB_USER} WITH ENCRYPTED PASSWORD '${DB_PASSWORD}';"
	@$(RUN_PSQL) -c "CREATE DATABASE ${DB_NAME} WITH OWNER ${DB_USER};"
	@$(RUN_PSQL) -c "GRANT ALL PRIVILEGES ON DATABASE ${DB_NAME} TO ${DB_USER};"
	@$(RUN_PSQL) -d ${DB_NAME} -c "ALTER SCHEMA public OWNER TO ${DB_USER};"
	@$(RUN_PSQL) -d ${DB_NAME} -c "ALTER SCHEMA public RENAME TO ${DB_SCHEMA}"
	@$(RUN_PSQL) -d ${DB_NAME} -c "ALTER USER ${DB_USER} SET search_path TO ${DB_SCHEMA};"
	@$(RUN_PSQL) -d ${DB_NAME} -c "GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA ${DB_SCHEMA} TO ${DB_USER};"

_init-create-managed-aliases:
	@$(RUN_ADMIN) ems:managed-alias:add-environment ma_preview preview
	@$(RUN_ADMIN) ems:managed-alias:add-environment ma_preview default
	@$(RUN_ADMIN) ems:managed-alias:add-environment ma_live live
	@$(RUN_ADMIN) ems:managed-alias:add-environment ma_live default
_init-create-users:
	@$(RUN_ADMIN) emsco:user:create author author@example.com author
	@$(RUN_ADMIN) emsco:user:promote author ROLE_AUTHOR
	@$(RUN_ADMIN) emsco:user:promote author ROLE_COPY_PASTE
	@$(RUN_ADMIN) emsco:user:create publisher publisher@example.com publisher
	@$(RUN_ADMIN) emsco:user:promote publisher ROLE_PUBLISHER
	@$(RUN_ADMIN) emsco:user:promote publisher ROLE_COPY_PASTE
	@$(RUN_ADMIN) emsco:user:promote publisher ROLE_ALLOW_ALIGN
	@$(RUN_ADMIN) emsco:user:create webmaster webmaster@example.com webmaster
	@$(RUN_ADMIN) emsco:user:promote webmaster ROLE_WEBMASTER
	@$(RUN_ADMIN) emsco:user:promote webmaster ROLE_COPY_PASTE
	@$(RUN_ADMIN) emsco:user:promote webmaster ROLE_ALLOW_ALIGN
	@$(RUN_ADMIN) emsco:user:promote webmaster ROLE_FORM_CRM
	@$(RUN_ADMIN) emsco:user:promote webmaster ROLE_TASK_MANAGER
	@$(RUN_ADMIN) emsco:user:create demo demo@example.com demo --super-admin
	@$(RUN_ADMIN) emsco:user:promote demo ROLE_API
	@$(RUN_ADMIN) emsco:user:promote demo ROLE_COPY_PASTE
	@$(RUN_ADMIN) emsco:user:promote demo ROLE_ALLOW_ALIGN
	@$(RUN_ADMIN) emsco:user:promote demo ROLE_FORM_CRM
	@$(RUN_ADMIN) emsco:user:promote demo ROLE_TASK_MANAGER
_init-switch_default-env:
	@$(RUN_ADMIN) emsco:contenttype:switch-default-env media_file default