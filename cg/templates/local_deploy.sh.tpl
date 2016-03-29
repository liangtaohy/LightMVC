#!/bin/bash

deploy_path={{LOCAL_DEPLOY_PATH}}
pro_file="{{APP_NAME}}.tar.gz"

. build.sh

tar -zxvf output/$pro_file -C $deploy_path