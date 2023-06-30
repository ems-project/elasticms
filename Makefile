#!/usr/bin/make -f

DOCKER_USER 	?= -1001
DOCKER_COMPOSE	= docker compose -f docker/docker-compose.yml

.DEFAULT_GOAL := help
.PHONY: help docker-up docker-down docker-ps

help: # Show help for each of the Makefile recipes.
	@echo "EMS Monorepo"
	@echo "---------------------------"
	@echo "DOCKER_USER: ${DOCKER_USER}"
	@echo "ADMIN:       http://localhost:8881"
	@echo "WEB:         http://localhost:8882"
	@echo "KIBANA:      http://kibana.localhost"
	@echo "MINIO:       http://minio.localhost"
	@echo "MAIL:        http://mailhog.localhost/"
	@echo "---------------------------"
	@echo ""
	@echo "Usage: make [target]"
	@echo "Targets:"
	@grep -E '(^[a-zA-Z0-9_-]+:.*?##.*$$)|(^##)' Makefile | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

## —— Mono —————————————————————————————————————————————————————————————————————————————————————————————————————————————
mono-init: ## init mono repo (copy .env if missing)
	@cp -u ./docker/.env.dist ./docker/.env
	@cp -u ./elasticms-admin/.env.dist ./elasticms-admin/.env
	@cp -u ./elasticms-admin/.env.local.dist ./elasticms-admin/.env.local
	@cp -u ./elasticms-web/.env.dist ./elasticms-web/.env
	@cp -u ./elasticms-web/.env.local.dist ./elasticms-web/.env.local

## —— Admin ————————————————————————————————————————————————————————————————————————————————————————————————————————————
admin-server-start: ## start symfony server (8881)
	@$(MAKE) -C ./elasticms-admin -s server-start
admin-server-stop: ## stop symfony server
	@$(MAKE) -C ./elasticms-admin -s server-stop
admin-server-log: ## log symfony server
	@$(MAKE) -C ./elasticms-admin -s server-log

## —— web ——————————————————————————————————————————————————————————————————————————————————————————————————————————————
web-server-start: ## start symfony server (8882)
	@$(MAKE) -C ./elasticms-web -s server-start
web-server-stop: ## stop symfony server
	@$(MAKE) -C ./elasticms-web -s server-stop
web-server-log: ## log symfony server
	@$(MAKE) -C ./elasticms-web -s server-log

## —— Docker ———————————————————————————————————————————————————————————————————————————————————————————————————————————
docker-up: ## docker up
	@$(DOCKER_COMPOSE) up -d
docker-down: ## docker down
	@$(DOCKER_COMPOSE) down
docker-ps: ## docker ps
	@$(DOCKER_COMPOSE) ps