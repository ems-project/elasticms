#!/bin/bash

#sh pg_dump.sh my_database schema_my_database_adm

docker-compose exec -e PGUSER=postgres -e PGPASSWORD=adminpg -T postgres pg_dump $1 -w --clean -Fp -O --schema=$2 | sed "/^\(DROP\|ALTER\|CREATE\) SCHEMA.*\$/d"
