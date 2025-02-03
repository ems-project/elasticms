#!/usr/bin/make -f

include .env

ELK_VERSION  ?= elk7
ENVIRONMENT  ?= local
DOCKER_USER  ?= $(shell id -u)
PWD					 = $(shell pwd)
RUN_ADMIN		 = docker compose exec admin-${ENVIRONMENT} ems-demo
RUN_WEB			 = docker compose exec -u ${DOCKER_USER} web-${ENVIRONMENT} preview
RUN_POSTGRES = docker compose exec -e PGUSER=postgres -e PGPASSWORD=adminpg -T postgres
RUN_NPM			 = docker run -u ${DOCKER_USER}:0 --rm -it -v ${PWD}:/opt/src --workdir /opt/src elasticms/base-php:8.1-cli-dev npm

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
start: ## start docker
	@mkdir -p dist
	@docker compose up -d
restart: ## restart docker and recreate
	@docker compose up -d --force-recreate
clean: ## delete docker volumes, generated assets and npm dependencies
	@$(MAKE) -s stop
	@docker volume rm elasticms_demo_${ELK_VERSION}_data01 elasticms_demo_${ELK_VERSION}_data02 elasticms_demo_${ELK_VERSION}_data03 elasticms_demo_postgres elasticms_demo_redis elasticms_demo_s3
	@rm -Rf dist/
	@rm -Rf node_modules/
stop: ## stop docker
	@docker compose down
status: ## status docker
	@docker compose ps
logs: ## logs
	@docker compose logs -f
update: ## update docker images
	@docker compose pull
	@docker compose up -d
dump: ## create db dump
	@$(MAKE) -s _db-dump
clear-cache: ## clear cache
	@$(RUN_ADMIN) cache:clear
	@$(RUN_WEB) cache:clear
init: ## init demo (fresh db)
	@$(MAKE) -s npm-install
	@$(MAKE) -s npm-prod
	@$(MAKE) -s clear-cache
	@$(MAKE) -s _db-setup
	@$(MAKE) -s _init-create-users
	@$(MAKE) -s load
load: ## load
	@$(MAKE) -s login
	@$(MAKE) -s restore-configs
	@$(MAKE) -s admin-activate
	@$(MAKE) -s admin-rebuild
	@$(MAKE) -s _init-create-managed-aliases
	@$(MAKE) -s health-check
	@$(MAKE) -s _init-switch_default-env
	@$(MAKE) -s emsch-folder-upload
	@$(MAKE) -s emsch-assets
	@$(MAKE) -s emsch-push
	@$(MAKE) -s health-check
	@$(MAKE) -s restore-documents
	@$(MAKE) -s admin-align

## —— Admin ————————————————————————————————————————————————————————————————————————————————————————————————————————————
admin/%: ## run admin command
	@$(RUN_ADMIN) $*
admin-rebuild: ## rebuild all environments
	@$(RUN_ADMIN) ems:environment:rebuild --all --no-debug
admin-activate: ## activate content types
	@$(RUN_ADMIN) ems:contenttype:activate --all --force --no-debug
admin-align: ## align preview with live
	@$(RUN_ADMIN) ems:environment:align preview live --force --no-debug

## —— Web ——————————————————————————————————————————————————————————————————————————————————————————————————————————————
web/%: ## run web command
	@$(RUN_WEB) $*
login: ## login
	@$(RUN_WEB) emsch:local:login demo demo
	@$(RUN_WEB) ems:admin:login --username=demo --password=demo
emsch-status: ## local status
	@$(RUN_WEB) emsch:local:status
emsch-push: ## local push
	@$(RUN_WEB) emsch:local:push --force
emsch-pull: ## local pull
	@$(RUN_WEB) emsch:local:pull
emsch-assets: ## local upload (bundle.zip)
	@$(RUN_WEB) emsch:local:upload --filename=/opt/src/local/skeleton/template/asset_hash.twig
emsch-folder-upload: ## upload folder assets
	@$(RUN_WEB) emsch:local:folder-upload /opt/src/admin/assets
backup-configs: ## backup configs
	@$(RUN_WEB) ems:admin:backup --configs --export
backup-documents: ## backup documents
	@$(RUN_WEB) ems:admin:backup --documents --export
restore-configs: ## restore configs
	@$(RUN_WEB) ems:admin:restore --configs --force
restore-documents: ## restore documents
	@$(RUN_WEB) ems:admin:restore --documents --force
health-check: ## health check
	@$(RUN_WEB) emsch:health-check -g --no-debug

## —— Npm ——————————————————————————————————————————————————————————————————————————————————————————————————————————————
npm/%:
	@$(RUN_NPM) $*
npm-install: ## npm install
	@$(MAKE) npm/install
npm-prod: ## npm run prod
	@$(MAKE) npm/"run build"
npm-watch: ## npm run watch
	@$(MAKE) npm/"run watch"
npm-dev: ## npm run dev
	@$(MAKE) npm/"run dev"

_db-setup:
	@$(MAKE) -s _db-drop
	@$(MAKE) -s _db-create
	@$(MAKE) -s _db-migrate
_db-dump:
	@$(RUN_POSTGRES) pg_dump ${DB_NAME} -w --clean -Fp -O --schema=${DB_SCHEMA} | sed "/^\(DROP\|ALTER\|CREATE\) SCHEMA.*\$$/d" > dump_demo_$$(date +%Y%m%d%H%M%S).sql
_db-migrate:
	@$(RUN_ADMIN) doctrine:migrations:migrate --no-interaction
_db-drop:
	@$(RUN_POSTGRES) psql -c "DROP DATABASE IF EXISTS ${DB_NAME};"
	@$(RUN_POSTGRES) psql -c "DROP USER IF EXISTS ${DB_USER};"
_db-create:
	@$(RUN_POSTGRES) psql -c "CREATE USER ${DB_USER} WITH ENCRYPTED PASSWORD '${DB_PASSWORD}';"
	@$(RUN_POSTGRES) psql -c "CREATE DATABASE ${DB_NAME} WITH OWNER ${DB_USER};"
	@$(RUN_POSTGRES) psql -c "GRANT ALL PRIVILEGES ON DATABASE ${DB_NAME} TO ${DB_USER};"
	@$(RUN_POSTGRES) psql -d ${DB_NAME} -c "ALTER SCHEMA public OWNER TO ${DB_USER};"
	@$(RUN_POSTGRES) psql -d ${DB_NAME} -c "ALTER SCHEMA public RENAME TO ${DB_SCHEMA}"
	@$(RUN_POSTGRES) psql -d ${DB_NAME} -c "ALTER USER ${DB_USER} SET search_path TO ${DB_SCHEMA};"
	@$(RUN_POSTGRES) psql -d ${DB_NAME} -c "GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA ${DB_SCHEMA} TO ${DB_USER};"

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