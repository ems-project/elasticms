#!/bin/bash

#sh pg_drop.sh

docker-compose exec -u ${DOCKER_USER:-1001} -e PGUSER=postgres -e PGPASSWORD=adminpg -T postgres psql -c "DROP DATABASE IF EXISTS $1;"
docker-compose exec -u ${DOCKER_USER:-1001} -e PGUSER=postgres -e PGPASSWORD=adminpg -T postgres psql -c "DROP USER IF EXISTS $1;"
