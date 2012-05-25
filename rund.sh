#!/bin/sh
#run this runner to start the Vidyo to Kaltura syncer
#place in /etc/rc.local the following line to make syncer run at startup:
#bash pathto/VidyoReplayToKaltura/rund.sh &

rmdir /tmp/syncv2klockdir
while true
do
 if mkdir /tmp/syncv2klockdir 2>/dev/null
 then
  php syncv2k.php >> runner.log
  sleep 15 
  rmdir /tmp/syncv2klockdir
 fi
done
