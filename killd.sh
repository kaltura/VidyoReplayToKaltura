#!/bin/sh
#run this kill/stop the daemon running the Vidyo to Kaltura syncer

#kill the daemon:
kill $(ps -eaf | grep rund.sh | grep -v grep | awk '{ print $2 }')
#kill the syncer script:
kill $(ps -eaf | grep syncv2k.php | grep -v grep | awk '{ print $2 }')
#cleanup the lock dir
rmdir /tmp/syncv2klockdir
