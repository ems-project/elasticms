#!/usr/bin/env bash
# Creates the demo role, database and schema.
#
# The postgres image runs it once, on its first start with an empty volume, so
# the database exists before the admin container boots: the admin runs its
# Doctrine migrations at startup and stops when it cannot reach the database.
# `make init` runs it again after dropping the database, for a fresh one.
set -euo pipefail

psql() {
	command psql -v ON_ERROR_STOP=1 --username "${POSTGRES_USER:-postgres}" \
		-v db_user="$DB_USER" -v db_password="$DB_PASSWORD" \
		-v db_name="$DB_NAME" -v db_schema="$DB_SCHEMA" "$@"
}

psql --dbname postgres <<'SQL'
CREATE USER :"db_user" WITH ENCRYPTED PASSWORD :'db_password';
CREATE DATABASE :"db_name" WITH OWNER :"db_user";
GRANT ALL PRIVILEGES ON DATABASE :"db_name" TO :"db_user";
SQL

psql --dbname "$DB_NAME" <<'SQL'
ALTER SCHEMA public OWNER TO :"db_user";
ALTER SCHEMA public RENAME TO :"db_schema";
ALTER USER :"db_user" SET search_path TO :"db_schema";
GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA :"db_schema" TO :"db_user";
SQL
