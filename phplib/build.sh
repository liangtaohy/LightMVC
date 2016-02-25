#!/bin/bash

PRO="phplib"
OUTPUT="output"
TARGET_DIR="/Volumes/work/users/liangtao01/app/"
mkdir -p $OUTPUT/$PRO

cp -r phplib_headers.php config testfw utils redis memcached log framework $OUTPUT/$PRO

cd $OUTPUT
tar -zcvf $PRO".tgz" $PRO
/bin/rm -rf $PRO
cd -

cp output/$PRO".tgz" $TARGET_DIR
tar -zxvf $TARGET_DIR$PRO".tgz" -C $TARGET_DIR