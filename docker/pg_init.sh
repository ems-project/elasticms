#/bin/bash

#sh pg_init.sh my_database schema_my_database_adm

docker compose exec postgres psql -c "CREATE USER $1 WITH ENCRYPTED PASSWORD '$1';"
docker compose exec postgres psql -c "CREATE DATABASE $1 WITH OWNER $1;"
docker compose exec postgres psql -c "GRANT ALL PRIVILEGES ON DATABASE $1 TO $1;"
docker compose exec postgres psql -d $1 -c "ALTER SCHEMA public OWNER TO $1;"
docker compose exec postgres psql -d $1 -c "ALTER SCHEMA public RENAME TO $2"
docker compose exec postgres psql -d $1 -c "ALTER USER $1 SET search_path TO $2;"
docker compose exec postgres psql -d $1 -c "GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA $2 TO $1;"
