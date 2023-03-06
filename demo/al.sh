#/bin/bash

docker compose exec -u ${DOCKER_USER:-1001}:0 admin-local ems-demo $@
