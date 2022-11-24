#!/bin/bash

source $1

if [ -z ${SET_ENV_IF_HOST+x} ]; then 
	cat $1 | sed '/^\s*$/d' | grep  -v '^#' | sed "s/\([a-zA-Z0-9_]*\)\=\(.*\)/SetEnv \1 \2/g"
else 
	cat $1 | sed '/^\s*$/d' | grep  -v '^#' | sed "s/\(.*\)/SetEnv \"$SET_ENV_IF_HOST\" \1/g"
fi

#cat $1 | sed '/^\s*$/d' | grep  -v '^#' | sed "s/\(.*\)/SetEnvIf \"$2\" \1/g"

#cat $1 | sed '/^\s*$/d' | grep  -v '^#' | sed "s/\(.*\)/\1/g"

#echo $EMS_NAME

