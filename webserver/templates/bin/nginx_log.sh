#!/bin/bash
cd ~/opbin/crontab/logrotate_all/bin
./backuplog.sh -D 01 -S 7 -P /home/work/logs/ -F store.log,store.log.wf,store.sdf.log,lightapp_server.sdf.log,lightapp_server.log,thirdbilling.log,developer3.log,developer3.log.wf,developer3.sdf.log,thirdapi.sdf.log,yunid.sdf.log,charge.sdf.log,internalapi.log.wf,internalapi.log,internalapi.sdf.log,callback.sdf.log,openapi.log.wf,openapi.log,openapi.sdf.log,oauth.log.wf,oauth.log,developer.log.wf,developer.sdf.log,developer.log,lightapp_lightapp.sdf.log -T h -X 1

./backuplog.sh -D 01 -S 90 -P /home/work/logs/ -F social.sdf.log

./backuplog.sh -D 01 -S 7 -P /home/work/valve/log/ -F valve.log,valve.log.wf -T h -X 1

#nginx
/home/work/nginx/bin/nginx_logrotate.sh
