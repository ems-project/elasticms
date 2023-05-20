#!/bin/bash

#sh pg_rename_schema.sh my_database schema_from schema_to

docker compose -u ${DOCKER_USER:-1001} exec -e PGUSER=postgres -e PGPASSWORD=adminpg -T postgres psql -d $1 -c "ALTER SCHEMA $2 RENAME TO $3"
docker compose -u ${DOCKER_USER:-1001} exec -e PGUSER=postgres -e PGPASSWORD=adminpg -T postgres psql -d $1 -c "ALTER USER $1 SET search_path TO $3;"
