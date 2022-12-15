#/bin/bash

docker run -u ${UID-1000} --rm -it -v $PWD:/opt/src --workdir /opt/src elasticms/base-php-dev npm $@
