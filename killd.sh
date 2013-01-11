#!/bin/bash
#run this kill/stop the daemon running the Vidyo to Kaltura syncer

PID=`ps -eaf | grep rund.sh | grep -v grep | awk '{ print $2 }'`
if [[ $PID ]]; then 
	echo 'killing the daemon: '$PID
	kill $PID 
else 
	echo 'daemon is not running'
fi


PID=`ps -eaf | grep syncv2k.php | grep -v grep | awk '{ print $2 }'`
if [[ $PID ]]; then
        echo 'killing the syncv2k.php script: '$PID
        kill $PID
else
        echo 'syncv2k.php script is not running'
fi

if [ ! -d /tmp/syncv2klockdir ]; then
	echo 'lock folder was already deleted'
else
	echo 'deleting lock folder'
	rm -Rf /tmp/syncv2klockdir
fi
