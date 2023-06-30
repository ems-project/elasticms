#!/usr/bin/make -f

include .env
export $(grep -v '^#' .env | xargs)
include .env.local
export $(grep -v '^#' .env.local | xargs)

SYMFONY		= symfony

.DEFAULT_GOAL := help
.PHONY: help symfony-start symfony-stop symfony-log

help: # Show help for each of the Makefile recipes.
	@echo "EMS ADMIN"
	@echo "---------------------------"
	@echo "ENV:         ${APP_ENV}"
	@echo "DB:          ${DB_URL}"
	@echo "DOCKER_USER: ${DOCKER_USER}"
	@echo "URL:         http://localhost:8881"
	@echo "---------------------------"
	@echo "Usage: make [target]"
	@echo "Targets:"
	@grep -E '(^[a-zA-Z0-9_-]+:.*?##.*$$)|(^##)' Makefile | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

## —— Symfony Server ———————————————————————————————————————————————————————————————————————————————————————————————————
server-start: ## start symfony server (8881)
	@$(SYMFONY) server:start -d --port=8881
	@echo "Started http://localhost:8881"
server-stop: ## stop symfony server
	@$(SYMFONY) server:stop
server-log: ## logs symfony server
	@$(SYMFONY) server:log