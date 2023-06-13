#/bin/bash

docker run -u ${DOCKER_USER:-1001} --rm -it -v $PWD:/opt/src --workdir /opt/src elasticms/base-php:8.1-cli-dev npx $@
