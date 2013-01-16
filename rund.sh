#!/bin/bash
#run this runner to start the Vidyo to Kaltura syncer
#place in /etc/rc.local the following line to make syncer run at startup:
#bash pathto/VidyoReplayToKaltura/rund.sh &

#provide a status function:
if [ "$1" = "status" ]; then
        PID=`ps -eaf | grep syncv2k.php | grep -v grep | awk '{ print $2 }'`
        if [[ $PID ]]; then
                echo 'Syncer daemon is running: '$PID
        else
                echo 'daemon is not running'
        fi
	exit 0
fi

#first, kill any instance previously running:
bash killd.sh reset
#run the sync script in an infinite loop to keep it running forever
while [ true ]; do
	if mkdir /tmp/syncv2klockdir 2>/dev/null 
	then
		#locking a new instance of the syncer script
		php syncv2k.php >> runner.log
		sleep 5
		rmdir /tmp/syncv2klockdir
	fi
done
