#/bin/bash

docker run -u ${UID-1000}:0 --rm -it -v $PWD:/opt/src --workdir /opt/src elasticms/base-php-dev npx $@
