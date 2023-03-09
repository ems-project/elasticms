#!/bin/bash

#sh db_load.sh my_database c:\dev\dump.sql

docker-compose exec -e PGUSER=postgres -e PGPASSWORD=adminpg -T postgres psql -U $1 < $2
