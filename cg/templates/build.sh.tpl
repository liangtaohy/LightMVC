#!/bin/bash

PRO_DIR="phpsrc/{{APP_NAME}}"
CONF_DIR="phpsrc/conf/{{APP_NAME}}"
PRO_FILE="{{APP_NAME}}.tar.gz"
TPL_DIR="templates/templates/{{APP_NAME}}/site/template"

mkdir -p output

rm -rf output/*

mkdir -p output/$PRO_DIR
mkdir -p output/$CONF_DIR
mkdir -p output/$TPL_DIR

cp -rf actions library models controller test common index.php output/$PRO_DIR/
cp -rf conf/* output/$CONF_DIR/

cd output

find ./ -type d -name .git|xargs -i rm -rf {}
find ./ -type d -name .svn|xargs -i rm -rf {}

tar cvzf $PRO_FILE *

rm -rf phpsrc templates

cd ..