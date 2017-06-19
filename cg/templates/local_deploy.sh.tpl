#!/bin/bash

deploy_path={{LOCAL_DEPLOY_PATH}}
pro_file="{{APP_NAME}}.tar.gz"

. build.sh

tar -zxvf output/$pro_file -C $deploy_path
cd $deploy_path/phpsrc/
if [ ! -d "phplib" ]; then
	git clone git@code.aliyun.com:laubersder/phplib.git
fi

cd -
