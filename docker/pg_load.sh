#!/bin/bash

#sh db_load.sh my_database c:\dev\dump.sql

docker-compose exec -u ${DOCKER_USER:-1001} -e PGUSER=postgres -e PGPASSWORD=adminpg -T postgres psql -U $1 < $2
