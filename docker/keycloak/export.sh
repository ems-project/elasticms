#!/usr/bin/env bash

docker compose exec keycloak sh /opt/keycloak/bin/kc.sh export --dir /backup --users same_file --realm elasticms